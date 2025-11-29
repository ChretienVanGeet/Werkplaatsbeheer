<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\RelationManagers;

use App\Filament\Resources\Resources\Schemas\ResourceForm;
use App\Filament\Resources\Resources\Tables\ResourcesTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ResourcesRelationManager extends RelationManager
{
    protected static string $relationship = 'resources';

    public function form(Schema $schema): Schema
    {
        return ResourceForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ResourcesTable::configure($table);
    }
}
