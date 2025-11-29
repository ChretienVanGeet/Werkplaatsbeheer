<?php

declare(strict_types=1);

namespace App\Filament\Resources\NoteAttachments\Pages;

use App\Filament\Resources\NoteAttachments\NoteAttachmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNoteAttachment extends EditRecord
{
    protected static string $resource = NoteAttachmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
