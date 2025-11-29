<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workflows\RelationManagers;

use App\Filament\Resources\WorkflowSteps\Schemas\WorkflowStepForm;
use App\Filament\Resources\WorkflowSteps\Tables\WorkflowStepsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WorkflowStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'workflowSteps';

    protected static ?string $title = 'Steps';

    public function form(Schema $schema): Schema
    {
        return WorkflowStepForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return WorkflowStepsTable::configure($table);
    }
}
