<?php

declare(strict_types=1);

namespace App\Livewire\Resources;

use App\Livewire\AbstractFormModelComponentInterface;
use App\Models\Resource;
use Flux\Flux;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Edit extends AbstractFormModelComponentInterface
{
    public string $name = '';
    public string $machineType = '';
    public ?string $description = null;
    public array $groups;
    public int $instructorCapacity = 100;
    public array $activities;

    public Resource $resource;

    /**
     * @var string[]
     */
    protected $listeners = ['updateSelectedItems'];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'machineType' => 'required|string|min:2',
            'description' => 'nullable|string',
            'groups' => 'array|required|min:1',
            'activities' => 'array',
            'instructorCapacity' => 'required|integer|min:1|max:100',
        ];
    }

    public function mount(?Resource $resource): void
    {
        $this->resource = ($resource ?? new Resource());
        $this->name = $this->resource->name ?? '';
        $this->machineType = $this->resource->machine_type ?? '';
        $this->description = $this->resource->description;
        $this->instructorCapacity = $this->resource->instructor_capacity ?? 100;

        if ($this->resource->exists) {
            $this->groups = $this->resource->groups->pluck('id')->toArray();
            /** @var array<array{id: int, label: string}> $activities */
            $activities = $this->resource->activities->pluck('name', 'id')
                ->map(fn ($label, $id) => ['id' => $id, 'label' => $label, 'edit-link' => route('activities.edit', $id), 'link' => route('activities.show', $id)])
                ->values()
                ->toArray();
            $this->activities = $activities;
        } else {
            $this->groups = [];
            $this->activities = [];
        }
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
            'machine_type' => $validated['machineType'],
            'description' => $validated['description'] ?? null,
            'instructor_capacity' => $validated['instructorCapacity'],
        ];

        $this->resource->exists
            ? $this->resource->update($data)
            : $this->resource = Resource::create($data);

        $this->resource->groups()->sync($this->groups);
        $this->resource->activities()->sync(collect($this->activities)->pluck('id'));

        Flux::toast(variant: 'success', text: __('Your changes have been saved.'));
        $this->redirect(url: route('resources.show', $this->resource->id), navigate: true);
    }

    public function updateSelectedItems(string $modalId, array $items): void
    {
        if ($modalId === 'select-activities') {
            $this->activities = $items;
        }
    }

    public function getFormView(): View
    {
        return view('livewire.resources.form');
    }

    public function getHeading(): string
    {
        return __('Edit :model', ['model' => __('Resource')]);
    }
}
