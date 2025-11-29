<?php

declare(strict_types=1);

namespace App\Livewire\Activities;

use App\Livewire\AbstractShowModelComponentInterface;
use App\Models\Activity;
use Illuminate\View\View;

class Show extends AbstractShowModelComponentInterface
{
    public Activity $activity;

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

    public function mount(Activity $activity): void
    {
        $this->activity = $activity;
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

    public function getHeading(): string
    {
        return __('Show :model', ['model' => __('Activity')]);
    }

    public function getView(): View
    {
        return view('livewire.activities.show', [
            'editRoute' => route('activities.edit', $this->activity->id),
        ]);
    }
}
