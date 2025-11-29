<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Schemas;

use App\Enums\ActivityStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                Select::make('status')
                    ->options(ActivityStatus::class)
                    ->default('preparing')
                    ->required(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
