<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResourceStatuses\Pages;

use App\Filament\Resources\ResourceStatuses\ResourceStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResourceStatus extends EditRecord
{
    protected static string $resource = ResourceStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
