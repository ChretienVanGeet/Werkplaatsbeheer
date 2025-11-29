<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkflowTemplateSteps\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkflowTemplateStepForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('workflow_template_id')
                    ->relationship('workflowTemplate', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
