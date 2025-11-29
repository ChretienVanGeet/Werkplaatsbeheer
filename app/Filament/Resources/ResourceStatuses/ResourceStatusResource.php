<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResourceStatuses;

use App\Filament\Resources\ResourceStatuses\Pages\CreateResourceStatus;
use App\Filament\Resources\ResourceStatuses\Pages\EditResourceStatus;
use App\Filament\Resources\ResourceStatuses\Pages\ListResourceStatuses;
use App\Filament\Resources\ResourceStatuses\Schemas\ResourceStatusForm;
use App\Filament\Resources\ResourceStatuses\Tables\ResourceStatusesTable;
use App\Models\ResourceStatus;
use BackedEnum;
use Filament\Resources\Resource as FilamentResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResourceStatusResource extends FilamentResource
{
    protected static ?string $model = ResourceStatus::class;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::withoutGlobalScopes()->count();
    }

    public static function getNavigationLabel(): string
    {
        return __('Resource Statuses');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    public static function form(Schema $schema): Schema
    {
        return ResourceStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResourceStatusesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResourceStatuses::route('/'),
            'create' => CreateResourceStatus::route('/create'),
            'edit' => EditResourceStatus::route('/{record}/edit'),
        ];
    }
}
