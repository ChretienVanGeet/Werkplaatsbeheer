<?php

declare(strict_types=1);

namespace App\Filament\Resources\Instructors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InstructorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required(),
            Select::make('supportedResources')
                ->label(__('Resources'))
                ->relationship('supportedResources', 'name')
                ->multiple()
                ->preload(),
            Textarea::make('description')
                ->columnSpanFull(),
        ]);
    }
}
