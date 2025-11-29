<?php

declare(strict_types=1);

namespace App\Filament\Resources\Resources\RelationManagers;

use App\Filament\Resources\Groups\Schemas\GroupForm;
use App\Filament\Resources\Groups\Tables\GroupsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public function form(Schema $schema): Schema
    {
        return GroupForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return GroupsTable::configure($table);
    }
}
