<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplateSteps\Pages;

use App\Filament\Resources\WorkflowTemplateSteps\WorkflowTemplateStepResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkflowTemplateSteps extends ListRecords
{
    protected static string $resource = WorkflowTemplateStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
