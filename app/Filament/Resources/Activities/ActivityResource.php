<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\CreateActivity;
use App\Filament\Resources\Activities\Pages\EditActivity;
use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Schemas\ActivityForm;
use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use App\Models\Activity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

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
        return __('Activities');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    public static function form(Schema $schema): Schema
    {
        return ActivityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GroupsRelationManager::class,
            RelationManagers\ParticipantsRelationManager::class,
            RelationManagers\CompaniesRelationManager::class,
            RelationManagers\ResourcesRelationManager::class,
            RelationManagers\NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'create' => CreateActivity::route('/create'),
            'edit' => EditActivity::route('/{record}/edit'),
        ];
    }
}
