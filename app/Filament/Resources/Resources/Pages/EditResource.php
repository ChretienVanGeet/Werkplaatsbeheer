<?php

declare(strict_types=1);

namespace App\Filament\Resources\Resources\Pages;

use App\Filament\Resources\Resources\ResourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResource extends EditRecord
{
    protected static string $resource = ResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
