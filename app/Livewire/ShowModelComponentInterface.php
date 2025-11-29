<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\View\View;

interface ShowModelComponentInterface
{
    public function getView(): View;
    public function getHeading(): string;

    public function getSubHeading(): string;

}
