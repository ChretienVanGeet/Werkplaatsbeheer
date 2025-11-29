<?php

declare(strict_types=1);

namespace App\Filament\Resources\NoteAttachments\Pages;

use App\Filament\Resources\NoteAttachments\NoteAttachmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNoteAttachment extends CreateRecord
{
    protected static string $resource = NoteAttachmentResource::class;
}
