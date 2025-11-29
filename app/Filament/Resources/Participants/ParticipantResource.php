<?php

declare(strict_types=1);

namespace App\Filament\Resources\Participants;

use App\Filament\Resources\Participants\Pages\CreateParticipant;
use App\Filament\Resources\Participants\Pages\EditParticipant;
use App\Filament\Resources\Participants\Pages\ListParticipants;
use App\Filament\Resources\Participants\Schemas\ParticipantForm;
use App\Filament\Resources\Participants\Tables\ParticipantsTable;
use App\Models\Participant;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

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
        return __('Participants');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

    protected static ?string $recordTitleAttribute = 'Participant';

    public static function form(Schema $schema): Schema
    {
        return ParticipantForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ParticipantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GroupsRelationManager::class,
            RelationManagers\ActivitiesRelationManager::class,
            RelationManagers\NotesRelationManager::class,
            RelationManagers\WorkflowsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListParticipants::route('/'),
            'create' => CreateParticipant::route('/create'),
            'edit'   => EditParticipant::route('/{record}/edit'),
        ];
    }
}
