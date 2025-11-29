<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ResourceStatus;
use App\Models\Activity;
use App\Models\Instructor;
use App\Models\InstructorAssignment;
use App\Models\Resource;
use App\Services\ResourceScheduler;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class InstructorScheduler
{
    private const SLOT_LENGTH_HOURS = 2;

    private const START_HOUR = 8;

    private const END_HOUR = 22;

    /**
     * @return Collection<int, array{start: CarbonImmutable, end: CarbonImmutable, total_load: int, assignments: array<int, array{id: int, instructor_id: int, name: string|null, activity_id: int|null, activity_name: string|null, load_percentage: int}>}>
     */
    public function generateScheduleForResource(Resource $resource, CarbonInterface $start, CarbonInterface $end): Collection
    {
        [$rangeStart, $rangeEnd] = $this->normalizeRange($start, $end);
        $this->assertRange($rangeStart, $rangeEnd);

        $assignmentMap = InstructorAssignment::with(['instructor', 'activity'])
            ->where('resource_id', $resource->id)
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($subQuery) use ($rangeStart, $rangeEnd) {
                        $subQuery->where('starts_at', '<', $rangeEnd)
                            ->where('ends_at', '>', $rangeStart);
                    });
            })
            ->get()
            ->groupBy(fn (InstructorAssignment $assignment) => $assignment->starts_at->toDateTimeString());

        $slots = collect();
        $slotStarts = $this->buildSlotStarts($rangeStart, $rangeEnd);

        foreach ($slotStarts as $slotStart) {
            $slotAssignments = $assignmentMap->get($slotStart->toDateTimeString(), collect());
            $slotLoad = $slotAssignments->sum(fn (InstructorAssignment $assignment) => $this->assignmentLoad($assignment));

            $slots->push([
                'start' => $slotStart,
                'end' => $slotStart->addHours(self::SLOT_LENGTH_HOURS),
                'total_load' => $slotLoad,
                'assignments' => $slotAssignments
                    ->map(fn (InstructorAssignment $assignment) => [
                        'id' => $assignment->id,
                        'instructor_id' => $assignment->instructor_id,
                        'name' => $assignment->instructor?->name,
                        'activity_id' => $assignment->activity_id,
                        'activity_name' => $assignment->activity?->name,
                        'load_percentage' => $this->assignmentLoad($assignment),
                    ])
                    ->values()
                    ->all(),
            ]);
        }

        return $slots;
    }

    public function check(
        Resource $resource,
        Instructor $instructor,
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $activityId = null,
        bool $forceOverride = false
    ): void {
        $this->validateSchedule(
            resource: $resource,
            instructor: $instructor,
            start: $start,
            end: $end,
            forceOverride: $forceOverride
        );
    }

    /**
     * @return Collection<int, array{start: CarbonImmutable, end: CarbonImmutable, existing_load: int, new_load: int, total_load: int, available: bool}>
     */
    public function availability(
        Resource $resource,
        Instructor $instructor,
        CarbonInterface $start,
        CarbonInterface $end,
        bool $forceOverride = false
    ): Collection {
        [$rangeStart, $rangeEnd] = $this->normalizeRange($start, $end);
        $this->assertRange($rangeStart, $rangeEnd);

        $slotStarts = $this->occupiedSlotStarts($resource, $rangeStart, $rangeEnd);

        if (empty($slotStarts)) {
            throw ValidationException::withMessages([
                'range' => __('Resource must be occupied in the selected range to schedule instructors.'),
            ]);
        }

        if (empty($slotStarts)) {
            throw ValidationException::withMessages([
                'range' => __('Resource must be occupied in the selected range to schedule instructors.'),
            ]);
        }

        $instructor->loadMissing('supportedResources');

        if (! $instructor->supportedResources->contains('id', $resource->id)) {
            throw ValidationException::withMessages([
                'instructor' => __('Instructor cannot supervise this resource.'),
            ]);
        }

        $instructorAssignments = $forceOverride
            ? collect()
            : InstructorAssignment::where('instructor_id', $instructor->id)
                ->with(['resource', 'activity'])
                ->where(function ($query) use ($rangeStart, $rangeEnd) {
                    $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                        ->orWhere(function ($subQuery) use ($rangeStart, $rangeEnd) {
                            $subQuery->where('starts_at', '<', $rangeEnd)
                                ->where('ends_at', '>', $rangeStart);
                        });
                })
                ->get()
                ->groupBy(fn (InstructorAssignment $assignment) => $assignment->starts_at->toDateTimeString());

        $newLoad = (int) $resource->instructor_capacity;

        return collect($slotStarts)->map(function (CarbonImmutable $slotStart) use ($instructorAssignments, $newLoad) {
            $startKey = $slotStart->toDateTimeString();
            $existingAssignments = $instructorAssignments->get($startKey, collect());
            $existingLoad = $existingAssignments
                ->sum(fn (InstructorAssignment $assignment) => $this->assignmentLoad($assignment));

            $total = $existingLoad + $newLoad;

            return [
                'start' => $slotStart,
                'end' => $slotStart->addHours(self::SLOT_LENGTH_HOURS),
                'existing_load' => $existingLoad,
                'new_load' => $newLoad,
                'total_load' => $total,
                'available' => $total <= 100,
                'assignments' => $existingAssignments->map(fn (InstructorAssignment $assignment) => [
                    'id' => $assignment->id,
                    'resource' => $assignment->resource?->name,
                    'activity' => $assignment->activity?->name,
                    'load_percentage' => $this->assignmentLoad($assignment),
                ])->values()->all(),
            ];
        });
    }

    public function schedule(
        Resource $resource,
        Instructor $instructor,
        CarbonInterface $start,
        CarbonInterface $end,
        ?int $activityId = null,
        bool $forceOverride = false
    ): void {
        [$slotStarts, $rangeStart, $rangeEnd] = $this->validateSchedule(
            resource: $resource,
            instructor: $instructor,
            start: $start,
            end: $end,
            forceOverride: $forceOverride
        );

        if ($forceOverride) {
            InstructorAssignment::where('instructor_id', $instructor->id)
                ->where(function ($query) use ($rangeStart, $rangeEnd) {
                    $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                        ->orWhere(function ($subQuery) use ($rangeStart, $rangeEnd) {
                            $subQuery->where('starts_at', '<', $rangeEnd)
                                ->where('ends_at', '>', $rangeStart);
                        });
                })
                ->delete();
        }

        $activity = $activityId ? Activity::find($activityId) : null;

        foreach ($slotStarts as $slotStart) {
            InstructorAssignment::create([
                'resource_id' => $resource->id,
                'instructor_id' => $instructor->id,
                'activity_id' => $activity?->id,
                'load_percentage' => $resource->instructor_capacity,
                'starts_at' => $slotStart,
                'ends_at' => $slotStart->addHours(self::SLOT_LENGTH_HOURS),
            ]);
        }
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function normalizeRange(CarbonInterface $start, CarbonInterface $end): array
    {
        $rangeStart = CarbonImmutable::parse($start)->minute(0)->second(0)->millisecond(0);
        $rangeEnd = CarbonImmutable::parse($end)->minute(0)->second(0)->millisecond(0);

        return [$rangeStart, $rangeEnd];
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

    private function isAllowedSlotStart(CarbonImmutable $date): bool
    {
        return $this->isAllowedDay($date)
            && $date->hour >= self::START_HOUR
            && $date->hour <= self::END_HOUR - self::SLOT_LENGTH_HOURS
            && $date->minute === 0
            && $date->second === 0;
    }

    private function isAllowedDay(CarbonImmutable $date): bool
    {
        // ISO day: 1 = Monday, 7 = Sunday
        return $date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 6;
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

    private function assignmentLoad(InstructorAssignment $assignment): int
    {
        return (int) ($assignment->load_percentage
            ?? $assignment->resource?->instructor_capacity
            ?? 100);
    }

    /**
     * @return array<int, CarbonImmutable>
     */
    private function occupiedSlotStarts(Resource $resource, CarbonImmutable $rangeStart, CarbonImmutable $rangeEnd): array
    {
        /** @var ResourceScheduler $resourceScheduler */
        $resourceScheduler = app(ResourceScheduler::class);

        return $resourceScheduler->generateSlots($resource, $rangeStart, $rangeEnd)
            ->filter(fn (array $slot) => $slot['status'] === ResourceStatus::OCCUPIED)
            ->map(fn (array $slot) => $slot['start'])
            ->values()
            ->all();
    }

    /**
     * @return array{0: array<int, CarbonImmutable>, 1: CarbonImmutable, 2: CarbonImmutable}
     */
    private function validateSchedule(
        Resource $resource,
        Instructor $instructor,
        CarbonInterface $start,
        CarbonInterface $end,
        bool $forceOverride
    ): array {
        [$rangeStart, $rangeEnd] = $this->normalizeRange($start, $end);
        $this->assertRange($rangeStart, $rangeEnd);

        $slotStarts = $this->occupiedSlotStarts($resource, $rangeStart, $rangeEnd);

        if (empty($slotStarts)) {
            throw ValidationException::withMessages([
                'range' => __('Resource must be occupied in the selected range to schedule instructors.'),
            ]);
        }

        $instructor->loadMissing('supportedResources');

        if (! $instructor->supportedResources->contains('id', $resource->id)) {
            throw ValidationException::withMessages([
                'instructor' => __('Instructor cannot supervise this resource.'),
            ]);
        }

        $instructorAssignments = $forceOverride
            ? collect()
            : InstructorAssignment::where('instructor_id', $instructor->id)
                ->where(function ($query) use ($rangeStart, $rangeEnd) {
                    $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                        ->orWhere(function ($subQuery) use ($rangeStart, $rangeEnd) {
                            $subQuery->where('starts_at', '<', $rangeEnd)
                                ->where('ends_at', '>', $rangeStart);
                        });
                })
                ->get()
                ->groupBy(fn (InstructorAssignment $assignment) => $assignment->starts_at->toDateTimeString());

        foreach ($slotStarts as $slotStart) {
            $startKey = $slotStart->toDateTimeString();

            $existingLoad = $instructorAssignments->get($startKey, collect())
                ->sum(fn (InstructorAssignment $assignment) => $this->assignmentLoad($assignment));

            $newLoad = (int) $resource->instructor_capacity;

            if ($existingLoad + $newLoad > 100) {
                throw ValidationException::withMessages([
                    'instructor' => __('Instructor would exceed 100% load at :time', ['time' => $slotStart->format('Y-m-d H:i')]),
                ]);
            }
        }

        return [$slotStarts, $rangeStart, $rangeEnd];
    }
}
