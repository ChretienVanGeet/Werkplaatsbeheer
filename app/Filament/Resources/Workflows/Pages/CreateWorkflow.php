<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workflows\Pages;

use App\Filament\Resources\Workflows\WorkflowResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkflow extends CreateRecord
{
    protected static string $resource = WorkflowResource::class;
}
