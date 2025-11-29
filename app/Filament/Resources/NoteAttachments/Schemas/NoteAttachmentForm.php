<?php

declare(strict_types=1);

namespace App\Filament\Resources\NoteAttachments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NoteAttachmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('note_id')
                    ->relationship('note', 'id')
                    ->required(),
                FileUpload::make('file_path')
                    ->required()
                    ->downloadable()
                    ->openable(),
                TextInput::make('original_name')
                    ->required(),
                TextInput::make('display_name'),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
