<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplateSteps;

use App\Filament\Resources\WorkflowTemplateSteps\Pages\CreateWorkflowTemplateStep;
use App\Filament\Resources\WorkflowTemplateSteps\Pages\EditWorkflowTemplateStep;
use App\Filament\Resources\WorkflowTemplateSteps\Pages\ListWorkflowTemplateSteps;
use App\Filament\Resources\WorkflowTemplateSteps\Schemas\WorkflowTemplateStepForm;
use App\Filament\Resources\WorkflowTemplateSteps\Tables\WorkflowTemplateStepsTable;
use App\Models\WorkflowTemplateStep;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkflowTemplateStepResource extends Resource
{
    protected static ?string $model = WorkflowTemplateStep::class;

    public static function getNavigationParentItem(): ?string
    {
        return __('Workflow Templates');
    }

    public static function getNavigationLabel(): string
    {
        return __('Workflow Template Steps');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return WorkflowTemplateStepForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkflowTemplateStepsTable::configure($table);
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
            'index'  => ListWorkflowTemplateSteps::route('/'),
            'create' => CreateWorkflowTemplateStep::route('/create'),
            'edit'   => EditWorkflowTemplateStep::route('/{record}/edit'),
        ];
    }
}
