<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notes\RelationManagers;

use App\Filament\Resources\NoteAttachments\Schemas\NoteAttachmentForm;
use App\Filament\Resources\NoteAttachments\Tables\NoteAttachmentsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    public function form(Schema $schema): Schema
    {
        return NoteAttachmentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return NoteAttachmentsTable::configure($table);
    }
}
