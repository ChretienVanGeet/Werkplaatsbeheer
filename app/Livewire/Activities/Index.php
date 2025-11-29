<?php

declare(strict_types=1);

namespace App\Livewire\Activities;

use App\Enums\ActivityStatus;
use App\Livewire\Traits\HasFluxTable;
use App\Models\Activity;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;
    use HasFluxTable;

    public string $statusFilter = "";
    public ?string $periodStart = null;
    public ?string $periodEnd = null;
    public array $activityStatuses;

    public function mount(): void
    {
        $this->activityStatuses = ActivityStatus::list();
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

    public ?int $deleteSelection = null;
    public function confirmDelete(int $id): void
    {
        $this->deleteSelection = $id;
        Flux::modal('confirm-delete')->show();
    }

    public function delete(): void
    {
        Activity::findOrFail($this->deleteSelection)->delete();
        $this->deleteSelection = null;

        Flux::modal('confirm-delete')->close();
    }

    #[On('group-filter-updated')]
    public function refreshForGroupFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.activities.index');
    }

    protected function sortableFields(): array
    {
        return ['id', 'name', 'start_date', 'end_date'];
    }

    protected function query(): Builder
    {
        return Activity::query()->withCount('notes');
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
