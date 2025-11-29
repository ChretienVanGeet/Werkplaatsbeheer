<?php

declare(strict_types=1);

namespace App\Livewire\Instructors;

use App\Livewire\AbstractShowModelComponentInterface;
use App\Models\Instructor;
use App\Models\InstructorAssignment;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class Show extends AbstractShowModelComponentInterface
{
    private const SLOT_LENGTH_HOURS = 2;
    private const START_HOUR = 8;
    private const END_HOUR = 22;

    public Instructor $instructor;

    /**
     * @var array<int, array{id: int, resource: string|null, activity: string|null, start: string, end: string}>
     */
    public array $upcomingAssignments = [];

    /**
     * @var array<int, array{date: string, label: string, slots: array<int, array{start: string, end: string, assignments: array<int, array{resource: string|null, activity: string|null}>}>}>
     */
    public array $weeklySlots = [];

    public string $weekStart;

    public function mount(Instructor $instructor): void
    {
        $this->instructor = $instructor->load(['groups', 'assignments.resource', 'assignments.activity', 'supportedResources']);

        $this->upcomingAssignments = $this->instructor->assignments()
            ->where('starts_at', '>=', Carbon::now()->startOfDay())
            ->orderBy('starts_at')
            ->limit(10)
            ->get()
            ->map(fn ($assignment) => [
                'id' => $assignment->id,
                'resource' => $assignment->resource?->name,
                'activity' => $assignment->activity?->name,
                'start' => $assignment->starts_at->format('Y-m-d H:i'),
                'end' => $assignment->ends_at->format('Y-m-d H:i'),
            ])
            ->toArray();

        $startOfWeek = Carbon::now()->startOfWeek()->setTime(8, 0);
        $this->weekStart = $startOfWeek->toDateString();
        $this->loadWeeklySlots($startOfWeek, (clone $startOfWeek)->addDays(6)->setTime(22, 0));
    }

    public function getView(): View
    {
        return view('livewire.instructors.show');
    }

    public function getHeading(): string
    {
        return $this->instructor->name ?? __('Instructor');
    }

    public function nextWeek(): void
    {
        $start = Carbon::parse($this->weekStart)->addWeek()->startOfWeek()->setTime(8, 0);
        $end = (clone $start)->addDays(6)->setTime(22, 0);
        $this->weekStart = $start->toDateString();
        $this->loadWeeklySlots($start, $end);
    }

    public function previousWeek(): void
    {
        $start = Carbon::parse($this->weekStart)->subWeek()->startOfWeek()->setTime(8, 0);
        $end = (clone $start)->addDays(6)->setTime(22, 0);
        $this->weekStart = $start->toDateString();
        $this->loadWeeklySlots($start, $end);
    }

    private function loadWeeklySlots(Carbon $start, Carbon $end): void
    {
        $assignments = InstructorAssignment::query()
            ->where('instructor_id', $this->instructor->id)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('starts_at', [$start, $end])
                    ->orWhere(function ($subQuery) use ($start, $end) {
                        $subQuery->where('starts_at', '<', $end)
                            ->where('ends_at', '>', $start);
                    });
            })
            ->with(['resource', 'activity'])
            ->get()
            ->groupBy(fn (InstructorAssignment $assignment) => $assignment->starts_at->toDateTimeString());

        $slotStarts = $this->buildSlotStarts($start, $end);

        $slotsPerDay = collect($slotStarts)
            ->map(function (Carbon $slotStart) use ($assignments) {
                $key = $slotStart->toDateTimeString();
                $slotAssignments = $assignments->get($key, collect())
                    ->map(function (InstructorAssignment $assignment) {
                        $load = (int) ($assignment->load_percentage ?? $assignment->resource?->instructor_capacity ?? 100);

                        return [
                            'resource' => $assignment->resource?->name,
                            'activity' => $assignment->activity?->name,
                            'load_percentage' => $load,
                        ];
                    })
                    ->values()
                    ->all();
                $totalLoad = collect($slotAssignments)->sum('load_percentage');

                return [
                    'start' => $slotStart->format('H:i'),
                    'end' => $slotStart->copy()->addHours(self::SLOT_LENGTH_HOURS)->format('H:i'),
                    'date' => $slotStart->toDateString(),
                    'assignments' => $slotAssignments,
                    'total_load' => $totalLoad,
                    'available' => max(0, 100 - $totalLoad),
                ];
            })
            ->groupBy('date')
            ->map(function ($daySlots, $date) {
                return [
                    'date' => $date,
                    'label' => Carbon::parse($date)->isoFormat('dddd D MMM'),
                    'slots' => $daySlots->map(fn (array $slot) => [
                        'start' => $slot['start'],
                        'end' => $slot['end'],
                        'assignments' => $slot['assignments'],
                        'total_load' => $slot['total_load'],
                        'available' => $slot['available'],
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        $this->weeklySlots = $slotsPerDay;
    }

    /**
     * @return array<int, Carbon>
     */
    private function buildSlotStarts(Carbon $start, Carbon $end): array
    {
        $slotStarts = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            if ($this->isAllowedDay($cursor) && $this->isAllowedHour($cursor)) {
                $slotStarts[] = $cursor->copy();
                $cursor->addHours(self::SLOT_LENGTH_HOURS);
                continue;
            }

            $cursor->addHours(self::SLOT_LENGTH_HOURS);

            if ($cursor->hour >= self::END_HOUR) {
                $cursor = $cursor->addDay()->setTime(self::START_HOUR, 0);
            }
        }

        return $slotStarts;
    }

    private function isAllowedDay(Carbon $date): bool
    {
        return $date->dayOfWeekIso >= 1 && $date->dayOfWeekIso <= 6;
    }

    private function isAllowedHour(Carbon $date): bool
    {
        return $date->hour >= self::START_HOUR
            && $date->hour <= self::END_HOUR - self::SLOT_LENGTH_HOURS
            && $date->minute === 0
            && $date->second === 0;
    }
}
