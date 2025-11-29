<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

abstract class AbstractShowModelComponentInterface extends Component implements ShowModelComponentInterface
{
    public function render(): View
    {
        return $this->getView()->with([
            'heading'    => $this->getHeading(),
            'subHeading' => $this->getSubHeading(),
        ]);
    }

    public function getSubHeading(): string
    {
        return __('See the data below');
    }
}
