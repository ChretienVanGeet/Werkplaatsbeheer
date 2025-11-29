<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\View\View;

interface FormModelComponentInterface
{
    public function getFormView(): View;

    public function getHeading(): string;

    public function getSubHeading(): string;

}
