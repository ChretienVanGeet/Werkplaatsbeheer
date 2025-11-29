<?php

declare(strict_types=1);

namespace App\Filament\Resources\Groups\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label(__('Description'))
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('created_by')
                    ->default(auth()->id())
                    ->numeric()
                    ->hiddenOn('create'),

                TextInput::make('updated_by')
                    ->default(auth()->id())
                    ->numeric()
                    ->hiddenOn('create'),
            ]);
    }
}
