<?php

declare(strict_types=1);

namespace App\Filament\Resources\Workflows\Schemas;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Participant;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WorkflowForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('workflow_template_id')
                    ->relationship('workflowTemplate', 'name')
                    ->required(),
                MorphToSelect::make('subject')
                    ->types([
                        MorphToSelect\Type::make(Participant::class)->titleAttribute('name'),
                        MorphToSelect\Type::make(Company::class)->titleAttribute('name'),
                        MorphToSelect\Type::make(Activity::class)->titleAttribute('name'),
                    ]),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
