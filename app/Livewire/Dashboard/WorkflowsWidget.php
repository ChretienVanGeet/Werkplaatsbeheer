<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\WorkflowStepStatus;
use App\Livewire\Traits\HasFluxTable;
use App\Models\Workflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class WorkflowsWidget extends Component
{
    use AuthorizesRequests;
    use HasFluxTable;

    public string $stepStatusFilter = '';
    public string $objectTypeFilter = '';

    public function render(): View
    {
        return view('livewire.dashboard.workflows-widget');
    }

    protected function sortableFields(): array
    {
        return ['id', 'name'];
    }

    protected function getPageName(): string
    {
        return 'wp';
    }

    #[On('group-filter-updated')]
    public function refreshForGroupFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStepStatusFilter(): void
    {
        $this->resetPage($this->getPageName());
    }

    public function updatedObjectTypeFilter(): void
    {
        $this->resetPage($this->getPageName());
    }

    protected function query(): Builder
    {
        return Workflow::query()
            ->whereHas('workflowTemplate')
            ->with([
                'workflowTemplate',
                'subject',
                'workflowSteps' => fn ($q) => $q
                    ->when($this->stepStatusFilter, fn ($f) => $f->where('status', $this->stepStatusFilter))
                    ->with('workflowTemplateStep'),
            ])
            ->withCount([
                'workflowSteps',
                'workflowSteps as completed_steps_count' => fn ($q) => $q->where('status', WorkflowStepStatus::FINISHED->value),
            ])
            ->when($this->objectTypeFilter, fn ($q) => $q->where('subject_type', $this->objectTypeFilter))
            ->when($this->stepStatusFilter, fn ($q) => $q->whereHas('workflowSteps', function ($w) {
                $w->where('status', $this->stepStatusFilter);
            }));
    }
}
