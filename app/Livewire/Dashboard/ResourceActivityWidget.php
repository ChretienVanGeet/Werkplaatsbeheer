<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\ResourceStatus;
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

    /**
     * @var array<int, \App\Enums\ResourceStatus>
     */
    public array $resourceStatuses = [];

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

        if (! $periodStart && ! $periodEnd) {
            $periodStart = Carbon::now();
            $periodEnd = Carbon::now();
        } elseif ($periodStart && ! $periodEnd) {
            $periodEnd = (clone $periodStart)->endOfDay();
        } elseif ($periodEnd && ! $periodStart) {
            $periodStart = (clone $periodEnd)->startOfDay();
        }

        $query = Resource::query()
            ->with([
                'statuses' => fn ($statusQuery) => $statusQuery
                    ->where('starts_at', '<=', $periodEnd)
                    ->where('ends_at', '>=', $periodStart)
                    ->orderByDesc('starts_at'),
                'instructorAssignments' => fn ($assignmentQuery) => $assignmentQuery
                    ->where('starts_at', '<=', $periodEnd)
                    ->where('ends_at', '>=', $periodStart)
                    ->with('instructor'),
            ])
            ->orderBy('id');

        if (! empty($this->statusFilter)) {
            $selectedStatus = ResourceStatus::from($this->statusFilter);

            $query->where(function ($resourceQuery) use ($selectedStatus, $periodStart, $periodEnd) {
                if ($selectedStatus === ResourceStatus::AVAILABLE) {
                    $resourceQuery->whereDoesntHave('statuses', function ($statusQuery) use ($periodStart, $periodEnd) {
                        $statusQuery
                            ->where('starts_at', '<=', $periodEnd)
                            ->where('ends_at', '>=', $periodStart);
                    });
                } else {
                    $resourceQuery->whereHas('statuses', function ($statusQuery) use ($selectedStatus, $periodStart, $periodEnd) {
                        $statusQuery
                            ->where('status', $selectedStatus->value)
                            ->where('starts_at', '<=', $periodEnd)
                            ->where('ends_at', '>=', $periodStart);
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

        if ($this->periodStart || $this->periodEnd) {
            $query->where(function ($resourceQuery) use ($periodStart, $periodEnd) {
                $resourceQuery->whereDoesntHave('statuses');

                $resourceQuery->orWhereHas('statuses', function ($statusQuery) use ($periodStart, $periodEnd) {
                    $statusQuery->where(function ($overlap) use ($periodStart, $periodEnd) {
                        if ($periodStart && $periodEnd) {
                            $overlap->where('starts_at', '<=', $periodEnd)
                                ->where('ends_at', '>=', $periodStart);
                        } elseif ($periodStart) {
                            $overlap->where('ends_at', '>=', $periodStart);
                        } elseif ($periodEnd) {
                            $overlap->where('starts_at', '<=', $periodEnd);
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

                return [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'statuses' => $statuses,
                    'instructors' => $instructors,
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
