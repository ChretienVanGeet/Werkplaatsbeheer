<?php

declare(strict_types=1);

namespace App\Livewire\Participants;

use App\Livewire\AbstractShowModelComponentInterface;
use App\Models\Participant;
use Illuminate\View\View;

class Show extends AbstractShowModelComponentInterface
{
    public Participant $participant;

    public function mount(Participant $participant): void
    {
        $this->participant = $participant;
    }

    public function getHeading(): string
    {
        return __('Show :model', ['model' => __('Participant')]);
    }

    public function getView(): View
    {
        return view('livewire.participants.show', [
            'editRoute' => route('participants.edit', $this->participant->id),
        ]);
    }
}
