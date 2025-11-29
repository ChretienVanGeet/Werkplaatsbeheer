<?php

declare(strict_types=1);

namespace App\Livewire\Groups;

use App\Livewire\AbstractFormModelComponentInterface;
use App\Models\Group;
use Flux\Flux;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Edit extends AbstractFormModelComponentInterface
{
    public string $name;
    public ?string $description;

    public Group $group;

    protected function rules(): array
    {
        return [
            'name'        => 'string|required',
            'description' => 'nullable|string',
        ];
    }

    public function mount(?Group $group): void
    {
        $this->group = $group ?? new Group();
        $this->name = $this->group->name ?? '';
        $this->description = $this->group->description;
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

        $this->group->exists
            ? $this->group->update($validated)
            : $this->group = $this->group->create($validated);

        Flux::toast(variant: 'success', text: __('Your changes have been saved.'));
        $this->redirect(url: route('groups.edit', $this->group->id), navigate: true);
    }

    public function getFormView(): View
    {
        return view('livewire.groups.form');
    }

    public function getHeading(): string
    {
        return __('Edit :model', ['model' => __('Group')]);
    }
}
