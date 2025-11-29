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

    protected function query(): Builder
    {
        return Workflow::query()
            ->whereHas('workflowTemplate')
            ->with([
                'workflowTemplate',
                'subject',
                'workflowSteps',
                'workflowSteps.workflowTemplateStep',
            ])
            ->withCount([
                'workflowSteps',
                'workflowSteps as completed_steps_count' => fn ($q) => $q->where('status', WorkflowStepStatus::FINISHED->value),
            ]);
    }
}
