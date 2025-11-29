<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplates;

use App\Filament\Resources\WorkflowTemplates\Pages\CreateWorkflowTemplate;
use App\Filament\Resources\WorkflowTemplates\Pages\EditWorkflowTemplate;
use App\Filament\Resources\WorkflowTemplates\Pages\ListWorkflowTemplates;
use App\Filament\Resources\WorkflowTemplates\Schemas\WorkflowTemplateForm;
use App\Filament\Resources\WorkflowTemplates\Tables\WorkflowTemplatesTable;
use App\Models\WorkflowTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkflowTemplateResource extends Resource
{
    protected static ?string $model = WorkflowTemplate::class;

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
        return __('Workflow Templates');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::NumberedList;

    public static function form(Schema $schema): Schema
    {
        return WorkflowTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkflowTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GroupsRelationManager::class,
            RelationManagers\WorkflowTemplateStepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWorkflowTemplates::route('/'),
            'create' => CreateWorkflowTemplate::route('/create'),
            'edit'   => EditWorkflowTemplate::route('/{record}/edit'),
        ];
    }
}
