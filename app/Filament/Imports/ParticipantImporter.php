<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Participant;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

/**
 * ParticipantImporter
 *
 * Imports participants from CSV files.
 *
 * CSV Format:
 * - Each row represents one participant
 * - All participant fields are optional except name
 *
 * Example CSV:
 * name,phone,email,city,comments
 * "John Doe","0612345678","john@example.com","Amsterdam","Great participant"
 * "Jane Smith","0687654321","jane@example.com","Rotterdam","Active member"
 */
class ParticipantImporter extends Importer
{
    protected static ?string $model = Participant::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Participant Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('phone')
                ->label('Phone Number')
                ->rules(['max:255']),
            ImportColumn::make('email')
                ->label('Email Address')
                ->rules(['email', 'max:255']),
            ImportColumn::make('city')
                ->label('City')
                ->rules(['max:255']),
            ImportColumn::make('comments')
                ->label('Comments'),
        ];
    }

    public function resolveRecord(): ?Participant
    {
        // Create or find the participant
        $participant = Participant::firstOrCreate([
            'name' => $this->data['name'],
        ], [
            'phone'    => ($this->data['phone'] ?? '') ?: null,
            'email'    => ($this->data['email'] ?? '') ?: null,
            'city'     => ($this->data['city'] ?? '') ?: null,
            'comments' => ($this->data['comments'] ?? '') ?: null,
        ]);

        return $participant;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your participant import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
