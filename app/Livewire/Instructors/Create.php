<?php

declare(strict_types=1);

namespace App\Livewire\Instructors;

class Create extends Edit
{
    public function getHeading(): string
    {
        return __('Create :model', ['model' => __('Instructor')]);
    }
}
