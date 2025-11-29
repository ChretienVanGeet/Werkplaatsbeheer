<?php

declare(strict_types=1);

namespace App\Filament\Resources\Resources;

use App\Filament\Resources\Resources\Pages\CreateResource;
use App\Filament\Resources\Resources\Pages\EditResource;
use App\Filament\Resources\Resources\Pages\ListResources;
use App\Filament\Resources\Resources\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\Resources\RelationManagers\GroupsRelationManager;
use App\Filament\Resources\Resources\RelationManagers\NotesRelationManager;
use App\Filament\Resources\Resources\Schemas\ResourceForm;
use App\Filament\Resources\Resources\Tables\ResourcesTable;
use App\Models\Resource;
use BackedEnum;
use Filament\Resources\Resource as FilamentResource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResourceResource extends FilamentResource
{
    protected static ?string $model = Resource::class;

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
        return __('Resources');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::WrenchScrewdriver;

    public static function form(Schema $schema): Schema
    {
        return ResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResourcesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            GroupsRelationManager::class,
            ActivitiesRelationManager::class,
            NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResources::route('/'),
            'create' => CreateResource::route('/create'),
            'edit' => EditResource::route('/{record}/edit'),
        ];
    }
}
