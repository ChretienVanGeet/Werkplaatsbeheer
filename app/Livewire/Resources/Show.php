<?php

declare(strict_types=1);

namespace App\Livewire\Resources;

use App\Enums\ResourceStatus;
use App\Models\Activity;
use App\Models\Instructor;
use App\Models\InstructorAssignment;
use App\Models\Resource;
use App\Services\InstructorScheduler;
use App\Services\ResourceScheduler;
use Carbon\CarbonInterface;
use Flux\Flux;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public Resource $resource;

    public string $weekStart;

    /**
     * @var array<int, array{date: string, label: string, slots: array<int, array{start: string, end: string, status: string, activity_name: string|null, activity_id: int|null, assignments: array<int, array{id: int, instructor_id: int, name: string|null, activity_id: int|null, activity_name: string|null, load_percentage: int}>, total_load: int, uncovered: bool}>}>
     */
    public array $weeklySlots = [];

    /**
     * @var array<int, array{start: string, end: string, status: string, activity_name: string|null}>
     */
    public array $rangeSlots = [];

    public string $rangeStart;

    public string $rangeEnd;

    public string $status;

    public ?int $activityId = null;

    public bool $confirmOverride = false;

    public ?bool $rangeMatchesSelection = null;

    public array $statusOptions = [];

    /**
     * @var array<int, array{id: int, name: string}>
     */
    public array $activities = [];

    /**
     * @var array<int, array{id: int, name: string}>
     */
    public array $instructors = [];

    public ?int $instructorId = null;

    public ?int $instructorActivityId = null;

    public string $instructorRangeStart;

    public string $instructorRangeEnd;

    public bool $forceInstructorOverride = false;

    /**
     * @var array<int, array{start: string, end: string, current_load: int, new_load: int, total_load: int, available: bool, assignments: array<int, array{id: int, resource: string|null, activity: string|null, load_percentage: int}>}>
     */
    public array $instructorRangeSlots = [];

    public bool $confirmUnscheduleAll = false;

    public function mount(Resource $resource, ResourceScheduler $scheduler, InstructorScheduler $instructorScheduler): void
    {
        $this->resource = $resource->load('activities');
        $this->statusOptions = ResourceStatus::list();
        $this->status = '';

        $startOfWeek = Carbon::now()->startOfWeek(CarbonInterface::MONDAY)->toDateString();
        $this->weekStart = $startOfWeek;
        $this->rangeStart = Carbon::parse($startOfWeek)->setTime(8, 0)->format('Y-m-d\TH:i');
        $this->rangeEnd = Carbon::parse($startOfWeek)->setTime(10, 0)->format('Y-m-d\TH:i');
        $this->instructorRangeStart = $this->rangeStart;
        $this->instructorRangeEnd = $this->rangeEnd;

        /** @var array<int, array{id: int, name: string}> $activities */
        $this->activities = $this->resource->activities
            ->map(fn (Activity $activity) => ['id' => $activity->id, 'name' => $activity->name])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->toArray();

        /** @var array<int, array{id: int, name: string}> $instructors */
        $this->instructors = Instructor::query()
            ->whereHas('supportedResources', fn ($query) => $query->whereKey($this->resource->id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Instructor $instructor) => ['id' => $instructor->id, 'name' => $instructor->name])
            ->toArray();

        $this->loadWeeklySlots($scheduler, $instructorScheduler);
        $this->checkAvailability($scheduler);
    }

    public function previousWeek(ResourceScheduler $scheduler, InstructorScheduler $instructorScheduler): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->toDateString();
        $this->loadWeeklySlots($scheduler, $instructorScheduler);
    }

    public function nextWeek(ResourceScheduler $scheduler, InstructorScheduler $instructorScheduler): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->toDateString();
        $this->loadWeeklySlots($scheduler, $instructorScheduler);
    }

    public function saveStatus(ResourceScheduler $scheduler): void
    {
        $this->validate([
            'rangeStart' => 'required|date',
            'rangeEnd' => 'required|date|after:rangeStart',
            'status' => 'required|string|in:'.implode(',', ResourceStatus::values()),
            'activityId' => [
                'nullable',
                'integer',
                Rule::in($this->resource->activities->pluck('id')->all()),
            ],
            'confirmOverride' => 'boolean',
        ]);

        try {
            $scheduler->setStatus(
                resource: $this->resource,
                start: Carbon::parse($this->rangeStart),
                end: Carbon::parse($this->rangeEnd),
                status: ResourceStatus::from($this->status),
                activityId: $this->activityId,
                forceOverride: $this->confirmOverride,
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();
            Flux::toast(text: $message, variant: 'danger');
            throw $exception;
        }

        $this->confirmOverride = false;
        $this->loadWeeklySlots($scheduler, app(InstructorScheduler::class));
        $this->checkAvailability($scheduler);
        Flux::toast(text: __('Status updated'), variant: 'success');
    }

    public function scheduleInstructor(InstructorScheduler $scheduler): void
    {
        $instructor = $this->validateInstructorInput();

        try {
            $scheduler->schedule(
                resource: $this->resource,
                instructor: $instructor,
                start: Carbon::parse($this->instructorRangeStart),
                end: Carbon::parse($this->instructorRangeEnd),
                activityId: $this->instructorActivityId,
                forceOverride: $this->forceInstructorOverride,
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();
            Flux::toast(text: $message, variant: 'danger');
            throw $exception;
        }

        $this->forceInstructorOverride = false;
        $this->loadWeeklySlots(app(ResourceScheduler::class), $scheduler);
        Flux::toast(text: __('Instructor scheduled'), variant: 'success');
    }

    public function checkInstructorStatus(InstructorScheduler $scheduler): void
    {
        $instructor = $this->validateInstructorInput();

        try {
            $availability = $scheduler->availability(
                resource: $this->resource,
                instructor: $instructor,
                start: Carbon::parse($this->instructorRangeStart),
                end: Carbon::parse($this->instructorRangeEnd),
                forceOverride: $this->forceInstructorOverride,
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();
            Flux::toast(text: $message, variant: 'danger');
            throw $exception;
        }

        $this->instructorRangeSlots = $availability
            ->map(fn (array $slot) => [
                'start' => $slot['start']->format('Y-m-d H:i'),
                'end' => $slot['end']->format('Y-m-d H:i'),
                'current_load' => $slot['existing_load'],
                'new_load' => $slot['new_load'],
                'total_load' => $slot['total_load'],
                'available' => $slot['available'],
                'assignments' => $slot['assignments'],
            ])
            ->all();
    }

    public function unscheduleInstructor(InstructorScheduler $scheduler, ResourceScheduler $resourceScheduler): void
    {
        $instructor = $this->validateInstructorInput(requireUnscheduleConfirmation: true);

        $rangeStart = Carbon::parse($this->instructorRangeStart);
        $rangeEnd = Carbon::parse($this->instructorRangeEnd);

        $deleted = InstructorAssignment::where('resource_id', $this->resource->id)
            ->where('instructor_id', $instructor->id)
            ->when($this->instructorActivityId !== null && $this->instructorActivityId !== 0, function ($query) {
                $query->where('activity_id', $this->instructorActivityId);
            })
            ->where(function ($query) use ($rangeStart, $rangeEnd) {
                $query->whereBetween('starts_at', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($subQuery) use ($rangeStart, $rangeEnd) {
                        $subQuery->where('starts_at', '<', $rangeEnd)
                            ->where('ends_at', '>', $rangeStart);
                    });
            })
            ->delete();

        $this->loadWeeklySlots($resourceScheduler, $scheduler);

        $message = $deleted > 0
            ? __('Instructor unscheduled from :count slots', ['count' => $deleted])
            : __('No assignments found for the selected range');

        Flux::toast(text: $message, variant: $deleted > 0 ? 'success' : 'info');
        $this->confirmUnscheduleAll = false;
    }

    public function removeInstructorAssignment(int $assignmentId, InstructorScheduler $scheduler): void
    {
        $assignment = InstructorAssignment::find($assignmentId);

        if (! $assignment || $assignment->resource_id !== $this->resource->id) {
            return;
        }

        $assignment->delete();
        $this->loadWeeklySlots(app(ResourceScheduler::class), $scheduler);
        Flux::toast(text: __('Instructor removed from slot'), variant: 'success');
    }

    public function checkAvailability(ResourceScheduler $scheduler): void
    {
        $this->validate([
            'rangeStart' => 'required|date',
            'rangeEnd' => 'required|date|after:rangeStart',
        ]);

        try {
            $slots = $scheduler->generateSlots(
                resource: $this->resource,
                start: Carbon::parse($this->rangeStart),
                end: Carbon::parse($this->rangeEnd),
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();
            Flux::toast(text: $message, variant: 'danger');
            throw $exception;
        }

        $filteredSlots = $slots->filter(function (array $slot): bool {
            $statusMatches = $this->status === '' || $slot['status']->value === $this->status;
            $activityMatches = $this->activityId === null || $this->activityId === 0 || $slot['activity_id'] === $this->activityId;

            return $statusMatches && $activityMatches;
        });

        $this->rangeSlots = $filteredSlots
            ->map(fn (array $slot) => [
                'start' => $slot['start']->format('Y-m-d H:i'),
                'end' => $slot['end']->format('Y-m-d H:i'),
                'status' => $slot['status']->value,
                'activity_name' => $slot['activity_name'],
                'activity_id' => $slot['activity_id'],
            ])
            ->all();

        $allMatchStatus = $this->status === ''
            || $slots->every(fn (array $slot) => $slot['status']->value === $this->status);

        $allMatchActivity = $this->activityId === null
            || $this->activityId === 0
            || $slots->every(fn (array $slot) => $slot['activity_id'] === $this->activityId);

        $this->rangeMatchesSelection = $allMatchStatus && $allMatchActivity ? true : null;
    }

    public function toggleSlotStatus(string $slotStart, ResourceScheduler $scheduler): void
    {
        $start = Carbon::parse($slotStart);
        $end = (clone $start)->addHours(2);

        $slots = $scheduler->generateSlots(
            resource: $this->resource,
            start: $start,
            end: $end
        );

        $currentStatus = $slots->first()['status'] ?? ResourceStatus::AVAILABLE;
        $statuses = ResourceStatus::cases();
        $currentIndex = array_search($currentStatus, $statuses, true);
        $nextStatus = $statuses[($currentIndex + 1) % count($statuses)];

        try {
            $scheduler->setStatus(
                resource: $this->resource,
                start: $start,
                end: $end,
                status: $nextStatus,
                activityId: null,
                forceOverride: true
            );
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();
            Flux::toast(text: $message, variant: 'danger');
            throw $exception;
        }

        if ($nextStatus !== ResourceStatus::OCCUPIED) {
            InstructorAssignment::where('resource_id', $this->resource->id)
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('starts_at', [$start, $end])
                        ->orWhere(function ($subQuery) use ($start, $end) {
                            $subQuery->where('starts_at', '<', $end)
                                ->where('ends_at', '>', $start);
                        });
                })
                ->delete();
        }

        $this->loadWeeklySlots($scheduler, app(InstructorScheduler::class));
    }

    public function render(): View
    {
        return view('livewire.resources.show');
    }

    private function loadWeeklySlots(ResourceScheduler $scheduler, InstructorScheduler $instructorScheduler): void
    {
        $weekStart = Carbon::parse($this->weekStart)->startOfWeek(CarbonInterface::MONDAY)->setTime(8, 0);
        $weekEnd = (clone $weekStart)->addDays(5)->setTime(22, 0);

        try {
            $slots = $scheduler->generateSlots($this->resource, $weekStart, $weekEnd);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();
            Flux::toast(text: $message, variant: 'danger');
            throw $exception;
        }

        try {
            $instructorSlots = $instructorScheduler->generateScheduleForResource($this->resource, $weekStart, $weekEnd);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())->flatten()->first();
            Flux::toast(text: $message, variant: 'danger');
            throw $exception;
        }

        $instructorByStart = $instructorSlots->keyBy(fn (array $slot) => $slot['start']->toDateTimeString());

        $this->weeklySlots = $slots
            ->groupBy(fn (array $slot) => $slot['start']->toDateString())
            ->map(function ($daySlots, $date) use ($instructorByStart) {
                return [
                    'date' => $date,
                    'label' => Carbon::parse($date)->isoFormat('dddd D MMM'),
                    'slots' => $daySlots->map(function (array $slot) use ($instructorByStart) {
                        $key = $slot['start']->toDateTimeString();
                        $instructorSlot = $instructorByStart->get($key);
                        $assignments = $instructorSlot['assignments'] ?? [];
                        $totalLoad = $instructorSlot['total_load'] ?? 0;
                        $statusEnum = $slot['status'];
                        $needsCoverage = in_array($statusEnum->value, [ResourceStatus::RESERVED->value, ResourceStatus::OCCUPIED->value], true);

                        return [
                            'start' => $slot['start']->format('H:i'),
                            'end' => $slot['end']->format('H:i'),
                            'start_raw' => $slot['start']->toDateTimeString(),
                            'status' => $statusEnum->value,
                            'activity_name' => $slot['activity_name'],
                            'activity_id' => $slot['activity_id'],
                            'assignments' => $assignments,
                            'total_load' => $totalLoad,
                            'uncovered' => $needsCoverage && $totalLoad === 0,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    private function validateInstructorInput(bool $requireUnscheduleConfirmation = false): Instructor
    {
        $rules = [
            'instructorId' => [
                'required',
                'integer',
                Rule::in(collect($this->instructors)->pluck('id')->all()),
            ],
            'instructorRangeStart' => 'required|date',
            'instructorRangeEnd' => 'required|date|after:instructorRangeStart',
            'instructorActivityId' => [
                'nullable',
                'integer',
                Rule::in($this->resource->activities->pluck('id')->all()),
            ],
            'forceInstructorOverride' => 'boolean',
        ];

        if ($requireUnscheduleConfirmation) {
            $rules['confirmUnscheduleAll'] = 'boolean';
        }

        $this->validate($rules);

        /** @var Instructor|null $instructor */
        $instructor = Instructor::find($this->instructorId);

        if (! $instructor) {
            Flux::toast(text: __('Instructor not found'), variant: 'danger');

            throw ValidationException::withMessages([
                'instructorId' => __('Instructor not found'),
            ]);
        }

        if (
            $requireUnscheduleConfirmation
            && ($this->instructorActivityId === null || $this->instructorActivityId === 0)
            && ! $this->confirmUnscheduleAll
        ) {
            Flux::toast(text: __('Please confirm unscheduling all activities for this instructor in the selected range.'), variant: 'warning');

            throw ValidationException::withMessages([
                'confirmUnscheduleAll' => __('Confirmation required to unschedule all activities'),
            ]);
        }

        return $instructor;
    }
}
