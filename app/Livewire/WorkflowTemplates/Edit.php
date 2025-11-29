<?php

declare(strict_types=1);

namespace App\Livewire\WorkflowTemplates;

use App\Livewire\AbstractFormModelComponentInterface;
use App\Models\WorkflowTemplate;
use Flux\Flux;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Edit extends AbstractFormModelComponentInterface
{
    public string $name = '';
    public ?string $description = null;
    public ?int $deleteWorkflowTemplateStepId = null;

    public array $groups;

    /**
     * @var string[]
     */
    protected $listeners = ['updateSelectedItems'];

    public WorkflowTemplate $workflowTemplate;
    public array $workflowTemplateSteps = [];

    protected function rules(): array
    {
        return [
            'name'                                => ['required', 'string', 'max:255', 'min:3'],
            'description'                         => ['nullable', 'string'],
            'workflowTemplateSteps'               => ['required', 'array', 'min:1'],
            'workflowTemplateSteps.*.id'          => ['nullable', 'integer', 'exists:workflow_template_steps,id'],
            'workflowTemplateSteps.*.name'        => ['nullable', 'string', 'max:255'],
            'workflowTemplateSteps.*.description' => ['nullable', 'string'],
            'workflowTemplateSteps.*.sort_order'  => ['required', 'integer'],
            'groups'                              => ['array', 'required','min:1'],
        ];
    }

    public function mount(?WorkflowTemplate $workflowTemplate): void
    {
        $this->workflowTemplate = $workflowTemplate ?? new WorkflowTemplate();
        $this->name = $this->workflowTemplate->name ?? '';
        $this->description = $this->workflowTemplate->description;
        $this->workflowTemplateSteps = array_values($this->workflowTemplate->workflowTemplateSteps->sortBy('sort_order')->toArray());
        $this->groups = $this->workflowTemplate->groups->pluck('id')->toArray();
    }

    public function createWorkflowTemplateStep(): void
    {
        $this->workflowTemplateSteps[] = [
            'id'          => null,
            'name'        => '',
            'description' => '',
            'sort_order'  => count($this->workflowTemplateSteps) + 1,
        ];

        $index = array_key_last($this->workflowTemplateSteps);
        $this->dispatch('scroll-to-new-step', id: "workflow-step-index-{$index}");
    }

    public function confirmDeleteWorkflowTemplateStep(int $index): void
    {
        $this->deleteWorkflowTemplateStepId = $index;
        Flux::modal('confirm-delete')->show();
    }

    public function deleteWorkflowStep(): void
    {
        // There always needs to be 1 item
        if (count($this->workflowTemplateSteps) <= 1) {
            return;
        }

        unset($this->workflowTemplateSteps[$this->deleteWorkflowTemplateStepId]);
        $this->workflowTemplateSteps = array_values($this->workflowTemplateSteps);
        foreach ($this->workflowTemplateSteps as $i => &$step) {
            $step['sort_order'] = $i + 1;
        }

        $this->deleteWorkflowTemplateStepId = null;
        Flux::modal('confirm-delete')->close();
    }

    public function moveWorkflowStepUp(int $index): void
    {
        if ($index > 0) {
            $temp = $this->workflowTemplateSteps[$index];
            $this->workflowTemplateSteps[$index] = $this->workflowTemplateSteps[$index - 1];
            $this->workflowTemplateSteps[$index - 1] = $temp;

            $this->updateSortOrders();
        }
    }

    public function moveWorkflowStepDown(int $index): void
    {
        if ($index < count($this->workflowTemplateSteps) - 1) {
            $temp = $this->workflowTemplateSteps[$index];
            $this->workflowTemplateSteps[$index] = $this->workflowTemplateSteps[$index + 1];
            $this->workflowTemplateSteps[$index + 1] = $temp;

            $this->updateSortOrders();
        }
    }

    private function updateSortOrders(): void
    {
        foreach ($this->workflowTemplateSteps as $i => &$step) {
            $step['sort_order'] = $i + 1;
        }
        $this->workflowTemplateSteps = array_values($this->workflowTemplateSteps); // reindex
    }

    public function save(): void
    {
        try {
            $validated = $this->validate();
        } catch (ValidationException $e) {
            $errors = collect($e->errors())->flatten();

            $text = $errors->count() === 1
                ? $errors->first()
                : __('There are :count validation errors. Please check the form.', ['count' => $errors->count()]);

            Flux::toast(text: $text, variant: 'danger');

            throw $e;
        }

        $this->workflowTemplate->exists
            ? $this->workflowTemplate->update($validated)
            : $this->workflowTemplate = $this->workflowTemplate->create($validated);

        $this->syncWorkflowTemplateSteps($this->workflowTemplate, $this->workflowTemplateSteps);

        $this->workflowTemplate->groups()->sync($this->groups);

        Flux::toast(variant: 'success', text: __('Your changes have been saved.'));
        $this->redirect(url: route('workflow-templates.edit', $this->workflowTemplate->id), navigate: true);
    }

    protected function syncWorkflowTemplateSteps(WorkflowTemplate $workflowTemplate, array $workflowTemplateSteps, bool $deleteMissing = true): void
    {
        $incomingStepIds = collect($workflowTemplateSteps)
            ->pluck('id')
            ->filter() // removes nulls for new steps
            ->all();

        if ($deleteMissing) {
            $workflowTemplate->workflowTemplateSteps()
                ->whereNotIn('id', $incomingStepIds)
                ->delete();
        }

        foreach ($workflowTemplateSteps as $stepData) {
            $workflowTemplate->workflowTemplateSteps()->updateOrCreate(
                ['id' => $stepData['id'] ?? null],
                [
                    'name'        => $stepData['name'],
                    'description' => $stepData['description'] ?? '',
                    'sort_order'  => $stepData['sort_order'],
                ]
            );
        }
    }

    public function getFormView(): View
    {
        return view('livewire.workflowTemplates.form');
    }

    public function getHeading(): string
    {
        return __('Edit :model', ['model' => __('Workflow template')]);
    }
}
