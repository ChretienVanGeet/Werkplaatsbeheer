<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Livewire\Dashboard\ActivitiesWidget;
use App\Livewire\Dashboard\ResourceActivityWidget;
use App\Livewire\Dashboard\WorkflowsWidget;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public array $widgets = [
//        [
//            'component' => ActivitiesWidget::class,
//            'params'    => [
//                'activityStatus' => ActivityStatus::STARTED,
//                'pageName' => 'as',
//            ],
//        ],
//        [
//            'component' => ActivitiesWidget::class,
//            'params'    => [
//                'activityStatus' => ActivityStatus::PREPARING,
//                'pageName' => 'ap'
//            ],
//        ],
//        [
//            'component' => ActivitiesWidget::class,
//            'params'    => [
//                'activityStatus' => ActivityStatus::FINISHED,
//                'pageName' => 'af'
//            ],
//        ],
        [
            'id'         => 'activities',
            'label'      => 'Activities',
            'component'  => ActivitiesWidget::class,
            'card-style' => 'col-span-2',
            'params'     => [
                'activityStatus' => null,
                'pageName'       => 'a',
            ],
        ],
        [
            'id'         => 'workflows',
            'label'      => 'Workflows',
            'component'  => WorkflowsWidget::class,
            'card-style' => 'col-span-2',
            'params'     => [],
        ],
        [
            'id'         => 'resources',
            'label'      => 'Resources',
            'component'  => ResourceActivityWidget::class,
            'card-style' => 'col-span-2',
            'params'     => [],
        ],
    ];

    public function render(): View
    {
        return view('livewire.dashboard.index');
    }

}
