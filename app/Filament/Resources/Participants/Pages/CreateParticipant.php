<?php

declare(strict_types=1);

namespace App\Filament\Resources\Participants\Pages;

use App\Filament\Resources\Participants\ParticipantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateParticipant extends CreateRecord
{
    protected static string $resource = ParticipantResource::class;
}
