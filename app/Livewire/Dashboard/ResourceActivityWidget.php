<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\ActivityStatus;
use App\Enums\ResourceStatus;
use App\Models\Activity;
use App\Models\Resource;
use App\Services\ResourceScheduler;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Component;

class ResourceActivityWidget extends Component
{
    public string $statusFilter = '';

    /**
     * @var array<int, \App\Enums\ActivityStatus>
     */
    public array $activityStatuses = [];

    /**
     * @var array<int, array{id: int, name: string, status: ActivityStatus, resources: array<int, array{id: int, name: string, machine_type: string|null}>}>
     */
    public array $activities = [];

    public string $weekStart;

    public ?int $selectedResourceId = null;

    public string $selectedResourceName = '';

    /**
     * @var array<int, array{date: string, label: string, slots: array<int, array{start: string, end: string, status: string, activity_name: string|null}>}>
     */
    public array $resourceWeek = [];

    public function mount(): void
    {
        $this->weekStart = Carbon::now()->startOfWeek()->toDateString();
        $this->loadActivities();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadActivities();
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

        $slots = $scheduler->generateSlots($resource, $weekStart, $weekEnd);

        $this->resourceWeek = $slots
            ->groupBy(fn (array $slot) => $slot['start']->toDateString())
            ->map(function ($daySlots, $date) {
                return [
                    'date' => $date,
                    'label' => Carbon::parse($date)->isoFormat('dddd D MMM'),
                    'slots' => $daySlots->map(fn (array $slot) => [
                        'start' => $slot['start']->format('H:i'),
                        'end' => $slot['end']->format('H:i'),
                        'status' => $slot['status']->value,
                        'activity_name' => $slot['activity_name'],
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('livewire.dashboard.resource-activity-widget');
    }

    private function loadActivities(): void
    {
        $this->activityStatuses = \App\Enums\ActivityStatus::list();

        $query = Activity::query()
            ->whereHas('resources')
            ->with(['resources'])
            ->orderBy('name');

        if (! empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        $this->activities = $query->get()
            ->map(function (Activity $activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->name,
                    'status' => $activity->status,
                    'resources' => $activity->resources->map(fn (Resource $resource) => [
                        'id' => $resource->id,
                        'name' => $resource->name,
                        'machine_type' => $resource->machine_type,
                    ])->values()->all(),
                ];
            })
            ->toArray();
    }

    private function refreshResourceSchedule(): void
    {
        if ($this->selectedResourceId) {
            $this->showResourceSchedule($this->selectedResourceId);
        }
    }
}
