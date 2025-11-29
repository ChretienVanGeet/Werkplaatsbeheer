<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use Livewire\Attributes\Validate;

class Create extends Edit
{
    #[Validate('required|confirmed|min:8')]
    public string $password = '';

    #[Validate('required')]
    public string $password_confirmation = '';

    public function getHeading(): string
    {
        return __('Create :model', ['model' => __('User')]);
    }
}
