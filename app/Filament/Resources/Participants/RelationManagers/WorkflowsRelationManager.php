<?php

declare(strict_types=1);

namespace App\Filament\Resources\Participants\RelationManagers;

use App\Filament\Resources\Workflows\Schemas\WorkflowForm;
use App\Filament\Resources\Workflows\Tables\WorkflowsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class WorkflowsRelationManager extends RelationManager
{
    protected static string $relationship = 'workflows';

    public function form(Schema $schema): Schema
    {
        return WorkflowForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return WorkflowsTable::configure($table);
    }
}
