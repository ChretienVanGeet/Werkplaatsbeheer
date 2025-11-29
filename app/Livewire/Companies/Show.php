<?php

declare(strict_types=1);

namespace App\Livewire\Companies;

use App\Livewire\AbstractShowModelComponentInterface;
use App\Models\Company;
use Illuminate\View\View;

class Show extends AbstractShowModelComponentInterface
{
    public Company $company;

    public function mount(Company $company): void
    {
        $this->company = $company;
        $this->company->load(['companyContacts']);

        //            ->load([
        //                'workflows' => function ($query) {
        //                    $query->with(['workflowSteps']);
        //                    $query->withCount([
        //                        'workflowSteps',
        //                        'workflowSteps as completed_steps_count' => fn ($q) => $q->where('status', WorkflowStepStatus::FINISHED->value),
        //                    ]);
        //                },
        //            ]);

    }

    public function getHeading(): string
    {
        return __('Show :model', ['model' => __('Company')]);
    }

    public function getView(): View
    {
        return view('livewire.companies.show', [
            'editRoute' => route('companies.edit', $this->company->id),
        ]);
    }
}
