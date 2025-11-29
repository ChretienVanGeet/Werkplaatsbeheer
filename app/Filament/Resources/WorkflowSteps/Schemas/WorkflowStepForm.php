<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowSteps\Schemas;

use App\Enums\WorkflowStepStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkflowStepForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('workflow_id')
                    ->relationship('workflow', 'id')
                    ->required(),
                Select::make('workflow_template_step_id')
                    ->relationship('workflowTemplateStep', 'name')
                    ->required(),
                Select::make('status')
                    ->options(WorkflowStepStatus::class)
                    ->default('created')
                    ->required(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
