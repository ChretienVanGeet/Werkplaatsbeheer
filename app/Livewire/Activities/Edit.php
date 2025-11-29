<?php

declare(strict_types=1);

namespace App\Livewire\Activities;

use App\Enums\ActivityStatus;
use App\Livewire\AbstractFormModelComponentInterface;
use App\Models\Activity;
use Flux\DateRange;
use Flux\Flux;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class Edit extends AbstractFormModelComponentInterface
{
    public string $name = '';

    public string $status;

    public DateRange $range;

    public array $activityStatuses;

    /**
     * @var array<array{id: int, label: string}>
     */
    public array $participants = [];

    /**
     * @var array<array{id: int, label: string}>
     */
    public array $companies = [];

    /**
     * @var array<array{id: int, label: string}>
     */
    public array $resources = [];

    public Activity $activity;

    public array $groups;

    /**
     * @var string[]
     */
    protected $listeners = ['updateSelectedItems'];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'status' => ['required', new Enum(ActivityStatus::class)],
            'groups' => 'array|required|min:1',
        ];
    }

    public function mount(?Activity $activity): void
    {
        $this->activity = ($activity ?? new Activity);
        $this->name = $this->activity->name ?? '';
        $this->status = $this->activity->status->value;

        $this->groups = $this->activity->groups->pluck('id')->toArray();

        $this->activityStatuses = ActivityStatus::list();

        $this->range = new DateRange($this->activity->start_date ?? Carbon::now(), $this->activity->end_date ?? null);

        /** @var array<array{id: int, label: string}> $participants */
        $participants = $this->activity->participants->pluck('name', 'id')
            ->map(fn ($label, $id) => ['id' => $id, 'label' => $label, 'edit-link' => route('participants.edit', $id), 'link' => route('participants.show', $id)])
            ->values()
            ->toArray();
        $this->participants = $participants;

        /** @var array<array{id: int, label: string}> $companies */
        $companies = $this->activity->companies->pluck('name', 'id')
            ->map(fn ($label, $id) => ['id' => $id, 'label' => $label, 'edit-link' => route('participants.edit', $id), 'link' => route('companies.show', $id)])
            ->values()
            ->toArray();
        $this->companies = $companies;

        /** @var array<array{id: int, label: string}> $resources */
        $resources = $this->activity->resources->pluck('name', 'id')
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

        $startDate = $this->range->start() ?? null;
        $endDate = $this->range->end() ?? null;

        $data = [
            'name' => $validated['name'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $this->status,
        ];

        $this->activity->exists
            ? $this->activity->update($data)
            : $this->activity = $this->activity->create($data);

        $participantsWithSortOrder = collect($this->participants)
            ->values() // ensure indexes are 0-based
            ->mapWithKeys(function ($item, $index) {
                return [
                    $item['id'] => ['sort_order' => $index],
                ];
            })
            ->toArray();

        $this->activity->participants()->sync($participantsWithSortOrder);

        $companiesWithSortOrder = collect($this->companies)
            ->values()
            ->mapWithKeys(function ($item, $index) {
                return [
                    $item['id'] => ['sort_order' => $index],
                ];
            })
            ->toArray();

        $this->activity->companies()->sync($companiesWithSortOrder);
        $this->activity->resources()->sync(collect($this->resources)->pluck('id'));

        $this->activity->groups()->sync($this->groups);

        Flux::toast(text: __('Your changes have been saved.'), variant: 'success');
        $this->redirect(url: route('activities.edit', $this->activity->id), navigate: true);
    }

    public function updateSelectedItems(string $modalId, array $items): void
    {
        if ($modalId === 'select-companies') {
            $this->companies = $items;
        } elseif ($modalId === 'select-participants') {
            $this->participants = $items;
        } elseif ($modalId === 'select-resources') {
            $this->resources = $items;
        }
    }

    public function getFormView(): View
    {
        return view('livewire.activities.form');
    }

    public function getHeading(): string
    {
        return __('Edit :model', ['model' => __('Activity')]);
    }
}
