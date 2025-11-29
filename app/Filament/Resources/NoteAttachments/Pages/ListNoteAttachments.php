<?php

declare(strict_types=1);

namespace App\Filament\Resources\NoteAttachments\Pages;

use App\Filament\Resources\NoteAttachments\NoteAttachmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNoteAttachments extends ListRecords
{
    protected static string $resource = NoteAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
