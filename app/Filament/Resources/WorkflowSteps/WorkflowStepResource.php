<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowSteps;

use App\Filament\Resources\WorkflowSteps\Pages\CreateWorkflowStep;
use App\Filament\Resources\WorkflowSteps\Pages\EditWorkflowStep;
use App\Filament\Resources\WorkflowSteps\Pages\ListWorkflowSteps;
use App\Filament\Resources\WorkflowSteps\Schemas\WorkflowStepForm;
use App\Filament\Resources\WorkflowSteps\Tables\WorkflowStepsTable;
use App\Models\WorkflowStep;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkflowStepResource extends Resource
{
    protected static ?string $model = WorkflowStep::class;

    public static function getNavigationParentItem(): ?string
    {
        return __('Workflows');
    }

    public static function getNavigationLabel(): string
    {
        return __('Workflow Steps');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WorkflowStepForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkflowStepsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWorkflowSteps::route('/'),
            'create' => CreateWorkflowStep::route('/create'),
            'edit'   => EditWorkflowStep::route('/{record}/edit'),
        ];
    }
}
