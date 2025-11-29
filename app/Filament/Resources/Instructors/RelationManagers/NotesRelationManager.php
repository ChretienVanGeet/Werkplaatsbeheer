<?php

declare(strict_types=1);

namespace App\Filament\Resources\Instructors\RelationManagers;

use App\Filament\Resources\Notes\Schemas\NoteForm;
use App\Filament\Resources\Notes\Tables\NotesTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    public function form(Schema $schema): Schema
    {
        return NoteForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return NotesTable::configure($table);
    }
}
