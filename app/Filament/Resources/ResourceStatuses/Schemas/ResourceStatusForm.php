<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResourceStatuses\Schemas;

use App\Enums\ResourceStatus as ResourceStatusEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ResourceStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('resource_id')
                ->relationship('resource', 'name')
                ->required(),
            Select::make('activity_id')
                ->relationship('activity', 'name')
                ->searchable()
                ->preload(),
            Select::make('status')
                ->options(ResourceStatusEnum::options())
                ->required(),
            DateTimePicker::make('starts_at')
                ->required(),
            DateTimePicker::make('ends_at')
                ->required(),
        ]);
    }
}
