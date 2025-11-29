<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Contracts\HasWorkflowsContract;
use App\Enums\WorkflowStepStatus;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateStep;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class WorkflowsPanel extends Component
{
    public HasWorkflowsContract $model;

    public ?Workflow $editingWorkflow = null;
    public ?int $deletingWorkflowId = null;

    public ?int $selectedWorkflowTemplateId = null;

    public bool $readOnly = false;

    public function mount(HasWorkflowsContract $model, bool $readOnly = false): void
    {
        $this->model = $model;
        $this->readOnly = $readOnly;
    }

    public function getSelectableWorkflowTemplates(): Collection
    {
        $usedTemplateIds = $this->model->workflows()->whereHas('workflowTemplate')->pluck('workflow_template_id');

        return WorkflowTemplate::query()
            ->whereNotIn('id', $usedTemplateIds)
            ->orderBy('name')
            ->get();
    }

    public function render(): View
    {
        $workflows = $this->model
            ->workflows()
            ->whereHas('workflowTemplate')
            ->with('workflowTemplate')
            ->get();
        $sortedWorkflows = $workflows
            ->sortBy('workflowTemplate.name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('livewire.components.workflows-panel', [
            'workflows' => $sortedWorkflows,
        ]);
    }

    public function confirmDeleteWorkflow(int $workflowId): void
    {
        $this->deletingWorkflowId = $workflowId;
        Flux::modal('confirm-workflow-delete')->show();
    }

    public function updateStatus(int $id, string $status): void
    {
        $step = WorkflowStep::findOrFail($id);
        $step->status = WorkflowStepStatus::from($status);
        $step->save();
        Flux::toast(variant: 'success', text: 'The status is saved successfully.');
    }

    public function addSelectedWorkflow(): void
    {
        /** @var Workflow $workflow */
        $workflow = $this->model->workflows()->create([
           'workflow_template_id' => $this->selectedWorkflowTemplateId,
        ]);

        $workflowTemplateSteps = WorkflowTemplateStep::query()->where('workflow_template_id', $this->selectedWorkflowTemplateId)->get();
        $workflowTemplateSteps->each(function (WorkflowTemplateStep $workflowTemplateStep) use ($workflow) {
            $workflow->workflowSteps()->create([
                'workflow_template_step_id' => $workflowTemplateStep->id,
                'status'                    => WorkflowStepStatus::CREATED,
            ]);
        });

        $this->selectedWorkflowTemplateId = null;
    }

    public function deleteWorkflow(): void
    {
        $workflow = Workflow::query()->findOrFail($this->deletingWorkflowId);
        $workflow->delete();
        $this->deletingWorkflowId = null;
        Flux::modal('confirm-workflow-delete')->close();
    }
}
