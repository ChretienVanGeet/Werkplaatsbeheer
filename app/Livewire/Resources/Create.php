<?php

declare(strict_types=1);

namespace App\Livewire\Resources;
class Create extends Edit
{


    public function getHeading(): string
    {
        return __('Create :model', ['model' => __('Resource')]);
    }
}
