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

    public ?int $notesActivityId = null;
    /**
     * @var array<int, array{id:int,subject:string,content:string,updated_at:string,creator:?string,updater:?string}>
     */
    public array $notesModal = [];

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

    public function openNotes(int $activityId): void
    {
        $activity = Activity::query()
            ->with(['notes' => fn ($q) => $q->latest('updated_at'), 'notes.creator', 'notes.updater'])
            ->findOrFail($activityId);

        $this->notesActivityId = $activityId;
        $this->notesModal = $activity->notes->map(function ($note) {
            return [
                'id' => $note->id,
                'subject' => $note->subject,
                'content' => $note->content,
                'updated_at' => $note->updated_at?->format('d-m-Y H:i'),
                'creator' => $note->creator?->name,
                'updater' => $note->updater?->name,
            ];
        })->all();

        Flux::modal('activity-notes')->show();
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
