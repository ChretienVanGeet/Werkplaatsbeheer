<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notes;

use App\Filament\Resources\Notes\Pages\CreateNote;
use App\Filament\Resources\Notes\Pages\EditNote;
use App\Filament\Resources\Notes\Pages\ListNotes;
use App\Filament\Resources\Notes\Schemas\NoteForm;
use App\Filament\Resources\Notes\Tables\NotesTable;
use App\Models\Note;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NoteResource extends Resource
{
    protected static ?string $model = Note::class;

    public static function getNavigationLabel(): string
    {
        return __('Notes');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Document;

    public static function form(Schema $schema): Schema
    {
        return NoteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListNotes::route('/'),
            'create' => CreateNote::route('/create'),
            'edit'   => EditNote::route('/{record}/edit'),
        ];
    }
}
