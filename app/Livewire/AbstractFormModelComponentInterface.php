<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

abstract class AbstractFormModelComponentInterface extends Component implements FormModelComponentInterface
{
    public function render(): View
    {
        return $this->getFormView()->with([
            'heading'    => $this->getHeading(),
            'subHeading' => $this->getSubHeading(),
        ]);
    }

    public function getSubHeading(): string
    {
        return __('Edit the data below');
    }
}
