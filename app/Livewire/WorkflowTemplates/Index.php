<?php

declare(strict_types=1);

namespace App\Livewire\WorkflowTemplates;

use App\Livewire\Traits\HasFluxTable;
use App\Models\WorkflowTemplate;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;
    use HasFluxTable;

    public ?int $deleteSelection = null;
    public function confirmDelete(int $id): void
    {
        $this->deleteSelection = $id;
        Flux::modal('confirm-delete')->show();
    }

    public function delete(): void
    {
        WorkflowTemplate::query()->findOrFail($this->deleteSelection)->delete();
        $this->deleteSelection = null;

        Flux::modal('confirm-delete')->close();
    }

    public function render(): View
    {
        return view('livewire.workflowTemplates.index');
    }

    protected function sortableFields(): array
    {
        return ['id', 'name', 'description', 'workflow_template_steps_count'];
    }

    protected function query(): Builder
    {
        return WorkflowTemplate::query()->withCount(['workflowTemplateSteps']);
    }
}
