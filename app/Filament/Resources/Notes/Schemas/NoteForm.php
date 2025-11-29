<?php

declare(strict_types=1);

namespace App\Filament\Resources\Notes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class NoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('noteable_type')
                    ->required(),
                TextInput::make('noteable_id')
                    ->required()
                    ->numeric(),
                TextInput::make('subject')
                    ->required(),
                Textarea::make('content')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
