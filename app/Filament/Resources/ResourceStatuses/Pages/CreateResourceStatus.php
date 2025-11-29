<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResourceStatuses\Pages;

use App\Filament\Resources\ResourceStatuses\ResourceStatusResource;
use Filament\Resources\Pages\CreateRecord;

class CreateResourceStatus extends CreateRecord
{
    protected static string $resource = ResourceStatusResource::class;
}
