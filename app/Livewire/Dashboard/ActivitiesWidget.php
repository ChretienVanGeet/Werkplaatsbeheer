<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\ActivityStatus;
use App\Livewire\Traits\HasFluxTable;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ActivitiesWidget extends Component
{
    use AuthorizesRequests;
    use HasFluxTable;

    public string $statusFilter = "";
    public ?string $periodStart = null;
    public ?string $periodEnd = null;
    public ?ActivityStatus $activityStatus = null;
    public string $pageName;
    public array $activityStatuses;

    public function mount(?ActivityStatus $activityStatus, string $pageName): void
    {
        if (!is_null($activityStatus)) {
            $this->activityStatus = $activityStatus;
        }

        $this->pageName = $pageName;
        $this->activityStatuses = ActivityStatus::list();
    }

    #[On('group-filter-updated')]
    public function refreshForGroupFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodStart(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodEnd(): void
    {
        $this->resetPage();
    }

    protected function getPageName(): string
    {
        return 'ap-'.$this->pageName;
    }

    public function render(): View
    {
        return view('livewire.dashboard.activities-widget');
    }

    protected function sortableFields(): array
    {
        return ['id', 'name', 'start_date', 'end_date'];
    }

    protected function query(): Builder
    {
        $query = Activity::query();

        if (!is_null($this->activityStatus)) {
            $query->where('status', $this->activityStatus->value);
        }

        return $query;
    }

    protected function applyFilters(Builder $query): Builder
    {
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->periodStart && $this->periodEnd) {
            $query->where(function (Builder $dateQuery): void {
                $dateQuery->whereDate('start_date', '<=', $this->periodEnd)
                    ->where(function (Builder $endDateQuery): void {
                        $endDateQuery->whereDate('end_date', '>=', $this->periodStart)
                            ->orWhereNull('end_date');
                    });
            });
        } elseif ($this->periodStart) {
            $query->where(function (Builder $dateQuery): void {
                $dateQuery->whereDate('end_date', '>=', $this->periodStart)
                    ->orWhere(function (Builder $openEndedQuery): void {
                        $openEndedQuery->whereNull('end_date')
                            ->whereDate('start_date', '>=', $this->periodStart);
                    });
            });
        } elseif ($this->periodEnd) {
            $query->whereDate('start_date', '<=', $this->periodEnd);
        }

        return $query;
    }
}
