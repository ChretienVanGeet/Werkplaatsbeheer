<?php

declare(strict_types=1);

namespace App\Livewire\Companies;

class Create extends Edit
{
    public function getHeading(): string
    {
        return __('Create :model', ['model' => __('Company')]);
    }
}
