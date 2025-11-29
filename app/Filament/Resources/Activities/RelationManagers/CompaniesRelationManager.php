<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\RelationManagers;

use App\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Filament\Resources\Companies\Tables\CompaniesTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CompaniesRelationManager extends RelationManager
{
    protected static string $relationship = 'companies';

    public function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
    }
}
