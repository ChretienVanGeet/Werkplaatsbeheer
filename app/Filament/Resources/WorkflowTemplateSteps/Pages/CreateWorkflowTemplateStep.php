<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplateSteps\Pages;

use App\Filament\Resources\WorkflowTemplateSteps\WorkflowTemplateStepResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkflowTemplateStep extends CreateRecord
{
    protected static string $resource = WorkflowTemplateStepResource::class;
}
