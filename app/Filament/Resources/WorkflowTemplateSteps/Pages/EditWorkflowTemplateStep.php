<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplateSteps\Pages;

use App\Filament\Resources\WorkflowTemplateSteps\WorkflowTemplateStepResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkflowTemplateStep extends EditRecord
{
    protected static string $resource = WorkflowTemplateStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
