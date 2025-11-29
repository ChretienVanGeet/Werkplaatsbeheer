<?php

declare(strict_types=1);

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\CompanyContacts\Schemas\CompanyContactForm;
use App\Filament\Resources\CompanyContacts\Tables\CompanyContactsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CompanyContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'companyContacts';

    protected static ?string $title = 'Contacts';

    public function form(Schema $schema): Schema
    {
        return CompanyContactForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return CompanyContactsTable::configure($table);
    }
}
