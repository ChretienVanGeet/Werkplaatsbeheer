<?php

declare(strict_types=1);

namespace App\Livewire\Groups;

class Create extends Edit
{
    public function getHeading(): string
    {
        return __('Create :model', ['model' => __('Group')]);
    }
}
