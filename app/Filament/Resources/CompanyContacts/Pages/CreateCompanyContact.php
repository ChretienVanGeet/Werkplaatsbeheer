<?php

declare(strict_types=1);

namespace App\Filament\Resources\CompanyContacts\Pages;

use App\Filament\Resources\CompanyContacts\CompanyContactResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyContact extends CreateRecord
{
    protected static string $resource = CompanyContactResource::class;
}
