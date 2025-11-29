<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workflows;

use App\Filament\Resources\Workflows\Pages\CreateWorkflow;
use App\Filament\Resources\Workflows\Pages\EditWorkflow;
use App\Filament\Resources\Workflows\Pages\ListWorkflows;
use App\Filament\Resources\Workflows\Schemas\WorkflowForm;
use App\Filament\Resources\Workflows\Tables\WorkflowsTable;
use App\Models\Workflow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkflowResource extends Resource
{
    protected static ?string $model = Workflow::class;

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
        return __('Workflows');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Workflow';

    public static function form(Schema $schema): Schema
    {
        return WorkflowForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkflowsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\WorkflowStepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWorkflows::route('/'),
            'create' => CreateWorkflow::route('/create'),
            'edit'   => EditWorkflow::route('/{record}/edit'),
        ];
    }
}
