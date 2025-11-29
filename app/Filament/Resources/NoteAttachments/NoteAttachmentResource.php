<?php

declare(strict_types=1);

namespace App\Filament\Resources\NoteAttachments;

use App\Filament\Resources\NoteAttachments\Pages\CreateNoteAttachment;
use App\Filament\Resources\NoteAttachments\Pages\EditNoteAttachment;
use App\Filament\Resources\NoteAttachments\Pages\ListNoteAttachments;
use App\Filament\Resources\NoteAttachments\Schemas\NoteAttachmentForm;
use App\Filament\Resources\NoteAttachments\Tables\NoteAttachmentsTable;
use App\Models\NoteAttachment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NoteAttachmentResource extends Resource
{
    protected static ?string $model = NoteAttachment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationParentItem(): ?string
    {
        return __('Notes');
    }

    public static function getNavigationLabel(): string
    {
        return __('Note Attachments');
    }

    public static function form(Schema $schema): Schema
    {
        return NoteAttachmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NoteAttachmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListNoteAttachments::route('/'),
            'create' => CreateNoteAttachment::route('/create'),
            'edit'   => EditNoteAttachment::route('/{record}/edit'),
        ];
    }
}
