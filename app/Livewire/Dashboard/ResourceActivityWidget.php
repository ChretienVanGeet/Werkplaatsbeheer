<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\ResourceStatus;
use App\Enums\ActivityStatus;
use App\Models\Activity;
use App\Models\Resource;
use App\Services\ResourceScheduler;
use App\Services\InstructorScheduler;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ResourceActivityWidget extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $search = '';
    public ?string $periodStart = null;
    public ?string $periodEnd = null;
    public ?int $activityFilter = null;

    /**
     * @var array<int, \App\Enums\ResourceStatus>
     */
    public array $resourceStatuses = [];
    /**
     * @var array<int, array{id:int,name:string}>
     */
    public array $activityOptions = [];

    public string $weekStart;

    public ?int $selectedResourceId = null;

    public string $selectedResourceName = '';

    /**
     * @var array<int, array{date: string, label: string, slots: array<int, array{start: string, end: string, status: string, activity_name: string|null}>}>
     */
    public array $resourceWeek = [];

    protected string $pageName = 'resources-page';

    public function mount(): void
    {
        $this->weekStart = Carbon::now()->startOfWeek()->toDateString();
        $this->resourceStatuses = ResourceStatus::list();
        $this->activityOptions = Activity::query()
            ->whereIn('status', [ActivityStatus::PREPARING, ActivityStatus::STARTED])
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Activity $activity) => ['id' => $activity->id, 'name' => $activity->name])
            ->all();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage($this->pageName);
    }

    public function updatedSearch(): void
    {
        $this->resetPage($this->pageName);
    }

    public function updatedPeriodStart(): void
    {
        $this->resetPage($this->pageName);
    }

    public function updatedPeriodEnd(): void
    {
        $this->resetPage($this->pageName);
    }

    public function updatedActivityFilter(): void
    {
        $this->resetPage($this->pageName);
    }

    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->addWeek()->toDateString();
        $this->refreshResourceSchedule();
    }

    public function previousWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->toDateString();
        $this->refreshResourceSchedule();
    }

    public function showResourceSchedule(int $resourceId): void
    {
        $resource = Resource::find($resourceId);

        if (! $resource) {
            $this->resourceWeek = [];
            $this->selectedResourceId = null;
            $this->selectedResourceName = '';
            return;
        }

        $this->selectedResourceId = $resource->id;
        $this->selectedResourceName = $resource->name;

        $weekStart = Carbon::parse($this->weekStart)->startOfWeek()->setTime(8, 0);
        $weekEnd = (clone $weekStart)->addDays(6)->setTime(22, 0);

        /** @var ResourceScheduler $scheduler */
        $scheduler = app(ResourceScheduler::class);
        /** @var InstructorScheduler $instructorScheduler */
        $instructorScheduler = app(InstructorScheduler::class);

        $slots = $scheduler->generateSlots($resource, $weekStart, $weekEnd);
        $instructorSlots = $instructorScheduler->generateScheduleForResource($resource, $weekStart, $weekEnd);
        $instructorByStart = $instructorSlots->keyBy(fn (array $slot) => $slot['start']->toDateTimeString());

        $this->resourceWeek = $slots
            ->groupBy(fn (array $slot) => $slot['start']->toDateString())
            ->map(function ($daySlots, $date) use ($instructorByStart) {
                return [
                    'date' => $date,
                    'label' => Carbon::parse($date)->isoFormat('dddd D MMM'),
                    'slots' => $daySlots->map(function (array $slot) use ($instructorByStart) {
                        $instructorSlot = $instructorByStart->get($slot['start']->toDateTimeString());

                        return [
                            'start' => $slot['start']->format('H:i'),
                            'end' => $slot['end']->format('H:i'),
                            'status' => $slot['status']->value,
                            'activity_name' => $slot['activity_name'],
                            'assignments' => $instructorSlot['assignments'] ?? [],
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.dashboard.resource-activity-widget', [
            'resources' => $this->resources(),
        ]);
    }

    #[Computed]
    public function resources(): LengthAwarePaginator
    {
        $periodStart = $this->periodStart ? Carbon::parse($this->periodStart)->startOfDay() : null;
        $periodEnd = $this->periodEnd ? Carbon::parse($this->periodEnd)->endOfDay() : null;

        $activityWindowStart = null;
        $activityWindowEnd = null;
        if (! empty($this->activityFilter)) {
            $activity = Activity::find($this->activityFilter);
            if ($activity) {
                $activityWindowStart = $activity->start_date?->copy()->setTime(8, 0);
                $activityWindowEnd = $activity->end_date?->copy()->setTime(22, 0);
            }
        }

        // Combine activity window with optional period filters (period further restricts activity window)
        $effectiveStart = $activityWindowStart ?? $periodStart;
        $effectiveEnd = $activityWindowEnd ?? $periodEnd;

        if ($activityWindowStart && $periodStart) {
            $effectiveStart = $periodStart->greaterThan($activityWindowStart) ? $periodStart : $activityWindowStart;
        }

        if ($activityWindowEnd && $periodEnd) {
            $effectiveEnd = $periodEnd->lessThan($activityWindowEnd) ? $periodEnd : $activityWindowEnd;
        }

        if (! $effectiveStart && ! $effectiveEnd) {
            $effectiveStart = Carbon::now();
            $effectiveEnd = Carbon::now();
        } elseif ($effectiveStart && ! $effectiveEnd) {
            $effectiveEnd = (clone $effectiveStart)->endOfDay();
        } elseif ($effectiveEnd && ! $effectiveStart) {
            $effectiveStart = (clone $effectiveEnd)->startOfDay();
        }

        $query = Resource::query()
            ->with([
                'statuses' => fn ($statusQuery) => $statusQuery
                    ->when($this->activityFilter, fn ($q) => $q->where('activity_id', $this->activityFilter))
                    ->where('starts_at', '<=', $effectiveEnd)
                    ->where('ends_at', '>=', $effectiveStart)
                    ->orderByDesc('starts_at'),
                'instructorAssignments' => fn ($assignmentQuery) => $assignmentQuery
                    ->where('starts_at', '<=', $effectiveEnd)
                    ->where('ends_at', '>=', $effectiveStart)
                    ->with('instructor'),
            ])
            ->orderBy('id');

        if (! empty($this->activityFilter)) {
            $query->whereHas('statuses', function ($statusQuery) use ($effectiveStart, $effectiveEnd) {
                $statusQuery
                    ->where('activity_id', $this->activityFilter)
                    ->when($effectiveEnd, fn ($q) => $q->where('starts_at', '<=', $effectiveEnd))
                    ->when($effectiveStart, fn ($q) => $q->where('ends_at', '>=', $effectiveStart));
            });

            $query->with(['activities' => fn ($activityQuery) => $activityQuery
                ->where('activities.id', $this->activityFilter)
                ->select('activities.id', 'activities.name', 'activities.start_date', 'activities.end_date')]);
        }

        if (! empty($this->statusFilter)) {
            $selectedStatus = ResourceStatus::from($this->statusFilter);

            $query->where(function ($resourceQuery) use ($selectedStatus, $effectiveStart, $effectiveEnd) {
                if ($selectedStatus === ResourceStatus::AVAILABLE) {
                    $resourceQuery->whereDoesntHave('statuses', function ($statusQuery) use ($effectiveStart, $effectiveEnd) {
                        $statusQuery
                            ->where('starts_at', '<=', $effectiveEnd)
                            ->where('ends_at', '>=', $effectiveStart);
                    });
                } else {
                    $resourceQuery->whereHas('statuses', function ($statusQuery) use ($selectedStatus, $effectiveStart, $effectiveEnd) {
                        $statusQuery
                            ->where('status', $selectedStatus->value)
                            ->where('starts_at', '<=', $effectiveEnd)
                            ->where('ends_at', '>=', $effectiveStart);
                    });
                }
            });
        }

        if (! empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($searchQuery) use ($searchTerm) {
                $searchQuery->where('name', 'like', $searchTerm)
                    ->orWhere('machine_type', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        if ($this->periodStart || $this->periodEnd || $activityWindowStart || $activityWindowEnd) {
            $query->where(function ($resourceQuery) use ($effectiveStart, $effectiveEnd) {
                $resourceQuery->whereDoesntHave('statuses');

                $resourceQuery->orWhereHas('statuses', function ($statusQuery) use ($effectiveStart, $effectiveEnd) {
                    $statusQuery->where(function ($overlap) use ($effectiveStart, $effectiveEnd) {
                        if ($effectiveStart && $effectiveEnd) {
                            $overlap->where('starts_at', '<=', $effectiveEnd)
                                ->where('ends_at', '>=', $effectiveStart);
                        } elseif ($effectiveStart) {
                            $overlap->where('ends_at', '>=', $effectiveStart);
                        } elseif ($effectiveEnd) {
                            $overlap->where('starts_at', '<=', $effectiveEnd);
                        }
                    });
                });
            });
        }

        return $query
            ->paginate($this->getPageSize(), pageName: $this->pageName)
            ->through(function (Resource $resource) {
                $statuses = $resource->statuses
                    ->map(fn ($status) => $status->status)
                    ->unique()
                    ->values()
                    ->all();

                if (empty($statuses)) {
                    $statuses = [ResourceStatus::AVAILABLE];
                }

                $instructors = $resource->instructorAssignments
                    ->map(function ($assignment) {
                        $instructor = $assignment->instructor;

                        return $instructor ? ['id' => $instructor->id, 'name' => $instructor->name] : null;
                    })
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->all();

                $activityData = null;
                if (! empty($this->activityFilter) && $resource->relationLoaded('activities')) {
                    /** @var Activity|null $activity */
                    $activity = $resource->activities->first();
                    if ($activity) {
                        $activityData = [
                            'id' => $activity->id,
                            'name' => $activity->name,
                            'start' => $activity->start_date?->format('d-m-Y'),
                            'end' => $activity->end_date?->format('d-m-Y'),
                        ];
                    }
                }

                return [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'machine_type' => $resource->machine_type,
                    'statuses' => $statuses,
                    'instructors' => $instructors,
                    'activity' => $activityData,
                ];
            })
            ->withQueryString();
    }

    private function refreshResourceSchedule(): void
    {
        if ($this->selectedResourceId) {
            $this->showResourceSchedule($this->selectedResourceId);
        }
    }

    private function getPageSize(): int
    {
        return 10;
    }
}
