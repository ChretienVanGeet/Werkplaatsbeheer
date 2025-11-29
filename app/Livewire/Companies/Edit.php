<?php

declare(strict_types=1);

namespace App\Livewire\Companies;

use App\Livewire\AbstractFormModelComponentInterface;
use App\Models\Company;
use Flux\Flux;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Edit extends AbstractFormModelComponentInterface
{
    public string $name = '';
    public ?string $industry = null;
    public ?string $comments = null;
    public ?string $locations = null;
    public ?int $deleteCompanyContactId = null;
    public array $companyContacts = [];

    public Company $company;

    public array $groups;

    protected function rules(): array
    {
        return [
            'name'      => 'required|string|min:3',
            'industry'  => 'nullable|string',
            'comments'  => 'nullable|string',
            'locations' => 'nullable|string',
            'groups'    => 'array|required|min:1',
        ];
    }

    public function mount(?Company $company): void
    {
        $this->company = ($company ?? new Company());
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
        $this->name = $this->company->name ?? '';
        $this->industry = $this->company->industry;
        $this->comments = $this->company->comments;
        $this->locations = $this->company->locations;
        $this->groups = $this->company->groups->pluck('id')->toArray();
        $this->companyContacts = array_values($this->company->companyContacts->sortBy('sort_order')->toArray());
    }

    public function createCompanyContact(): void
    {
        $this->companyContacts[] = [
            'id'         => null,
            'name'       => '',
            'role'       => '',
            'phone'      => '',
            'email'      => '',
            'location'   => '',
            'sort_order' => count($this->companyContacts) + 1,
        ];

        $index = array_key_last($this->companyContacts);
        $this->dispatch('scroll-to-new-step', id: "company-contact-index-{$index}");
    }

    public function confirmDeleteCompanyContact(int $index): void
    {
        $this->deleteCompanyContactId = $index;
        Flux::modal('confirm-delete')->show();
    }

    public function deleteWorkflowStep(): void
    {
        // There always needs to be 1 item
        if (count($this->companyContacts) <= 1) {
            return;
        }

        unset($this->companyContacts[$this->deleteCompanyContactId]);
        $this->companyContacts = array_values($this->companyContacts);
        foreach ($this->companyContacts as $i => &$step) {
            $step['sort_order'] = $i + 1;
        }

        $this->deleteCompanyContactId = null;
        Flux::modal('confirm-delete')->close();
    }

    public function moveCompanyContactUp(int $index): void
    {
        if ($index > 0) {
            $temp = $this->companyContacts[$index];
            $this->companyContacts[$index] = $this->companyContacts[$index - 1];
            $this->companyContacts[$index - 1] = $temp;

            $this->updateSortOrders();
        }
    }

    public function moveCompanyContactDown(int $index): void
    {
        if ($index < count($this->companyContacts) - 1) {
            $temp = $this->companyContacts[$index];
            $this->companyContacts[$index] = $this->companyContacts[$index + 1];
            $this->companyContacts[$index + 1] = $temp;

            $this->updateSortOrders();
        }
    }

    private function updateSortOrders(): void
    {
        foreach ($this->companyContacts as $i => &$step) {
            $step['sort_order'] = $i + 1;
        }
        $this->companyContacts = array_values($this->companyContacts); // reindex
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

        $this->company->exists
            ? $this->company->update($validated)
            : $this->company = $this->company->create($validated);

        $this->syncCompanyContacts($this->company, $this->companyContacts);
        $this->company->groups()->sync($this->groups);

        Flux::toast(variant: 'success', text: __('Your changes have been saved.'));
        $this->redirect(url: route('companies.show', $this->company->id), navigate: true);
    }

    protected function syncCompanyContacts(Company $company, array $companyContacts, bool $deleteMissing = true): void
    {
        $incomingCompanyContactIds = collect($companyContacts)
            ->pluck('id')
            ->filter() // removes nulls for new steps
            ->all();

        if ($deleteMissing) {
            $company->companyContacts()
                ->whereNotIn('id', $incomingCompanyContactIds)
                ->delete();
        }

        foreach ($companyContacts as $companyContact) {
            $company->companyContacts()->updateOrCreate(
                ['id' => $companyContact['id'] ?? null],
                [
                    'name'       => $companyContact['name'],
                    'role'       => $companyContact['role'],
                    'phone'      => $companyContact['phone'],
                    'email'      => $companyContact['email'],
                    'location'   => $companyContact['location'],
                    'sort_order' => $companyContact['sort_order'],
                ]
            );
        }
    }

    public function getFormView(): View
    {
        return view('livewire.companies.form');
    }

    public function getHeading(): string
    {
        return __('Edit :model', ['model' => __('Company')]);
    }
}
