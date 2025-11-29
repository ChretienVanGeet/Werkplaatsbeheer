<?php

declare(strict_types=1);

namespace App\Livewire\Instructors;

use App\Livewire\AbstractFormModelComponentInterface;
use App\Models\Instructor;
use Flux\Flux;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Edit extends AbstractFormModelComponentInterface
{
    public string $name = '';
    public array $resources ;
    public ?string $description = null;
    
    public array $groups ;

    public Instructor $instructor;
    protected $listeners = ['updateSelectedItems'];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'description' => 'nullable|string',
            'groups' => 'array|required|min:1',
            'resources' => 'array',
        ];
    }

    public function mount(?Instructor $instructor): void
    {
        $this->instructor = ($instructor ?? new Instructor());
        $this->name = $this->instructor->name ?? '';
        $this->description = $this->instructor->description;
        $this->groups = $this->instructor->groups->pluck('id')->toArray();
        /** @var array<array{id: int, label: string}> $resources */
        $resources = $this->instructor->supportedResources->pluck('name', 'id')
            ->map(fn ($label, $id) => ['id' => $id, 'label' => $label, 'edit-link' => route('resources.edit', $id), 'link' => route('resources.show', $id)])
            ->values()
            ->toArray();
        $this->resources = $resources;
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

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        $this->instructor->exists
            ? $this->instructor->update($data)
            : $this->instructor = $this->instructor->create($data);

        $this->instructor->groups()->sync($this->groups);
        $this->instructor->supportedResources()->sync(collect($this->resources)->pluck('id'));

        Flux::toast(variant: 'success', text: __('Your changes have been saved.'));
        $this->redirect(url: route('instructors.show', $this->instructor->id), navigate: true);
    }

    public function updateSelectedItems(string $modalId, array $items): void
    {
        if ($modalId === 'select-resources') {
            $this->resources = $items;
        }
    }

    public function getFormView(): View
    {
        return view('livewire.instructors.form');
    }

    public function getHeading(): string
    {
        return __('Edit :model', ['model' => __('Instructor')]);
    }
}
