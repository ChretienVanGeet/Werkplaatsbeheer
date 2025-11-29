<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ResourceStatus;
use App\Models\Activity;
use App\Models\Resource;
use App\Models\ResourceStatus as ResourceStatusModel;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ResourceScheduler
{
    private const SLOT_LENGTH_HOURS = 2;

    private const START_HOUR = 8;

    private const END_HOUR = 22;

    /**
     * @return Collection<int, array{start: CarbonImmutable, end: CarbonImmutable, status: ResourceStatus, activity_id: int|null, activity_name: string|null}>
     */
    public function generateSlots(Resource $resource, CarbonInterface $start, CarbonInterface $end): Collection
    {
        $rangeStart = CarbonImmutable::parse($start)->minute(0)->second(0)->millisecond(0);
        $rangeEnd = CarbonImmutable::parse($end)->minute(0)->second(0)->millisecond(0);

        if ($rangeEnd->lte($rangeStart)) {
            throw ValidationException::withMessages([
                'range' => __('The end of the range must be after the start.'),
            ]);
        }

        $statusMap = ResourceStatusModel::with('activity')
            ->where('resource_id', $resource->id)
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($subQuery) use ($rangeStart) {
                        $subQuery->where('starts_at', '<', $rangeStart)
                            ->where('ends_at', '>', $rangeStart);
                    });
            })
            ->get()
            ->keyBy(fn (ResourceStatusModel $status) => $status->starts_at->toDateTimeString());

        $slots = collect();
        $cursor = $this->moveToNextAllowedSlot($rangeStart);

        while ($cursor->lt($rangeEnd)) {
            if (! $this->isAllowedSlotStart($cursor)) {
                $cursor = $this->moveToNextAllowedSlot($cursor);

                continue;
            }

            $slotEnd = $cursor->addHours(self::SLOT_LENGTH_HOURS);

            if ($slotEnd->gt($rangeEnd)) {
                break;
            }

            $status = $statusMap->get($cursor->toDateTimeString());

            $slots->push([
                'start' => $cursor,
                'end' => $slotEnd,
                'status' => $status?->status ?? ResourceStatus::AVAILABLE,
                'activity_id' => $status?->activity_id,
                'activity_name' => $status?->activity?->name,
            ]);

            $cursor = $slotEnd;

            if ($cursor->hour >= self::END_HOUR) {
                $cursor = $this->moveToNextAllowedSlot($cursor);
            }
        }

        return $slots;
    }

    public function setStatus(
        Resource $resource,
        CarbonInterface $start,
        CarbonInterface $end,
        ResourceStatus $status,
        ?int $activityId,
        bool $forceOverride
    ): void {
        $rangeStart = CarbonImmutable::parse($start)->minute(0)->second(0)->millisecond(0);
        $rangeEnd = CarbonImmutable::parse($end)->minute(0)->second(0)->millisecond(0);

        $this->assertRange($rangeStart, $rangeEnd);

        $slotStarts = $this->buildSlotStarts($rangeStart, $rangeEnd);

        $overlappingStatuses = ResourceStatusModel::where('resource_id', $resource->id)
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($subQuery) use ($rangeStart, $rangeEnd) {
                        $subQuery->where('starts_at', '<', $rangeEnd)
                            ->where('ends_at', '>', $rangeStart);
                    });
            })
            ->get();

        if ($overlappingStatuses->isNotEmpty() && ! $forceOverride) {
            throw ValidationException::withMessages([
                'confirmOverride' => __('Existing slot statuses will be overwritten. Please confirm to proceed.'),
            ]);
        }

        if ($overlappingStatuses->isNotEmpty()) {
            ResourceStatusModel::whereIn('id', $overlappingStatuses->pluck('id'))->delete();
        }

        if ($status === ResourceStatus::AVAILABLE) {
            return;
        }

        $activity = $activityId ? Activity::find($activityId) : null;

        foreach ($slotStarts as $slotStart) {
            ResourceStatusModel::create([
                'resource_id' => $resource->id,
                'activity_id' => $activity?->id,
                'status' => $status,
                'starts_at' => $slotStart,
                'ends_at' => $slotStart->addHours(self::SLOT_LENGTH_HOURS),
            ]);
        }

        if ($activity) {
            $resource->activities()->syncWithoutDetaching([$activity->id]);
        }
    }

    private function assertRange(CarbonImmutable $start, CarbonImmutable $end): void
    {
        if ($start->gte($end)) {
            throw ValidationException::withMessages([
                'range' => __('The end time must be after the start time.'),
            ]);
        }

        if ($start->minute !== 0 || $end->minute !== 0 || $start->second !== 0 || $end->second !== 0) {
            throw ValidationException::withMessages([
                'range' => __('Times must align to 2-hour slots (e.g. 08:00, 10:00, ...).'),
            ]);
        }

        if (! $this->isAllowedSlotBoundary($start) || ! $this->isAllowedSlotBoundary($end, allowEndBoundary: true)) {
            throw ValidationException::withMessages([
                'range' => __('Times must be within 08:00 and 22:00 in 2-hour steps.'),
            ]);
        }
    }

    /**
     * @return array<int, CarbonImmutable>
     */
    private function buildSlotStarts(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $slotStarts = [];
        $cursor = $this->moveToNextAllowedSlot($start);

        while ($cursor->lt($end)) {
            if (! $this->isAllowedSlotStart($cursor)) {
                $cursor = $this->moveToNextAllowedSlot($cursor);

                continue;
            }

            $slotEnd = $cursor->addHours(self::SLOT_LENGTH_HOURS);
            if ($slotEnd->gt($end)) {
                break;
            }

            $slotStarts[] = $cursor;
            $cursor = $slotEnd;

            if ($cursor->hour >= self::END_HOUR) {
                $cursor = $this->moveToNextAllowedSlot($cursor);
            }
        }

        return $slotStarts;
    }

    private function isAllowedDay(CarbonImmutable $date): bool
    {
        // ISO day: 1 = Monday, 7 = Sunday
        return $date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 6;
    }

    private function isAllowedSlotStart(CarbonImmutable $date): bool
    {
        return $this->isAllowedDay($date)
            && $date->hour >= self::START_HOUR
            && $date->hour <= self::END_HOUR - self::SLOT_LENGTH_HOURS
            && $date->minute === 0
            && $date->second === 0;
    }

    private function isAllowedSlotBoundary(CarbonImmutable $date, bool $allowEndBoundary = false): bool
    {
        $withinHours = $allowEndBoundary
            ? $date->hour >= self::START_HOUR && $date->hour <= self::END_HOUR
            : $date->hour >= self::START_HOUR && $date->hour <= self::END_HOUR - self::SLOT_LENGTH_HOURS;

        return $withinHours
            && $date->minute === 0
            && $date->second === 0
            && $date->hour % self::SLOT_LENGTH_HOURS === 0;
    }

    private function isAllowedSlotEnd(CarbonImmutable $date): bool
    {
        return $this->isAllowedDay($date)
            && $date->hour <= self::END_HOUR
            && $date->minute === 0
            && $date->second === 0;
    }

    private function moveToNextAllowedSlot(CarbonImmutable $date): CarbonImmutable
    {
        if ($date->dayOfWeekIso === 7) {
            $date = $date->next(CarbonInterface::MONDAY)->startOfDay();
        }

        if ($date->hour < self::START_HOUR) {
            return $date->setTime(self::START_HOUR, 0);
        }

        if ($date->hour >= self::END_HOUR) {
            return $date->addDay()->setTime(self::START_HOUR, 0);
        }

        $hour = $date->hour;
        $hour = $hour - ($hour % self::SLOT_LENGTH_HOURS);

        return $date->setTime($hour, 0);
    }
}
