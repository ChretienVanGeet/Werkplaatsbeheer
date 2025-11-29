<?php

declare(strict_types=1);

namespace App\Filament\Resources\Instructors\Pages;

use App\Filament\Resources\Instructors\InstructorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstructors extends ListRecords
{
    protected static string $resource = InstructorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
