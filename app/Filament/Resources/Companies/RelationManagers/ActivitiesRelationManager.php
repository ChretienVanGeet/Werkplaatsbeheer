<?php

declare(strict_types=1);

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Activities\Schemas\ActivityForm;
use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    public function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }
}
