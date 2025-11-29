<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplates\RelationManagers;

use App\Filament\Resources\WorkflowTemplateSteps\Schemas\WorkflowTemplateStepForm;
use App\Filament\Resources\WorkflowTemplateSteps\Tables\WorkflowTemplateStepsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WorkflowTemplateStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'workflowTemplateSteps';

    protected static ?string $title = 'Template Steps';

    public function form(Schema $schema): Schema
    {
        return WorkflowTemplateStepForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return WorkflowTemplateStepsTable::configure($table);
    }
}
