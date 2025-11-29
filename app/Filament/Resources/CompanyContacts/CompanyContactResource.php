<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyContacts;

use App\Filament\Resources\CompanyContacts\Pages\CreateCompanyContact;
use App\Filament\Resources\CompanyContacts\Pages\EditCompanyContact;
use App\Filament\Resources\CompanyContacts\Pages\ListCompanyContacts;
use App\Filament\Resources\CompanyContacts\Schemas\CompanyContactForm;
use App\Filament\Resources\CompanyContacts\Tables\CompanyContactsTable;
use App\Models\CompanyContact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyContactResource extends Resource
{
    protected static ?string $model = CompanyContact::class;

    public static function getNavigationParentItem(): string
    {
        return __('Companies');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    public static function getNavigationLabel(): string
    {
        return __('Company Contacts');
    }

    public static function form(Schema $schema): Schema
    {
        return CompanyContactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyContactsTable::configure($table);
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
            'index'  => ListCompanyContacts::route('/'),
            'create' => CreateCompanyContact::route('/create'),
            'edit'   => EditCompanyContact::route('/{record}/edit'),
        ];
    }
}
