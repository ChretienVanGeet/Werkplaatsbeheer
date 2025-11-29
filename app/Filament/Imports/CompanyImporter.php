<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Company;
use App\Models\CompanyContact;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class CompanyImporter extends Importer
{
    protected static ?string $model = Company::class;

    /**
     * Safely cast a mixed value to string or return null if empty.
     */
    private function castToStringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }

    public static function getColumns(): array
    {
        return [
            // Company fields
            ImportColumn::make('name')
                ->label('Company Name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('industry')
                ->label('Company Industry')
                ->rules(['max:255']),
            ImportColumn::make('comments')
                ->label('Company Comments'),
            ImportColumn::make('locations')
                ->label('Company Locations'),

            // CompanyContact fields
            ImportColumn::make('contact_name')
                ->label('Contact Name')
                ->rules(['max:255']),
            ImportColumn::make('contact_email')
                ->label('Contact Email')
                ->rules(['email', 'max:255']),
            ImportColumn::make('contact_phone')
                ->label('Contact Phone')
                ->rules(['max:255']),
            ImportColumn::make('contact_location')
                ->label('Contact Location')
                ->rules(['max:255']),
            ImportColumn::make('contact_role')
                ->label('Contact Role')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?Company
    {
        // First, create or find the company
        $company = Company::firstOrCreate([
            'name' => $this->data['name'],
        ], [
            'industry'  => $this->data['industry'] ?? null,
            'comments'  => $this->data['comments'] ?? null,
            'locations' => $this->data['locations'] ?? null,
        ]);

        if (! empty($this->data['contact_name'])) {
            /**
             * @var CompanyContact|null $contact
             */
            // Check if this exact contact already exists for this company
            $contact = $company->companyContacts()
                ->where('name', $this->data['contact_name'])
                ->first();

            if (! $contact) {
                $contact = new CompanyContact();
                $contact->company_id = $company->id;
                $contact->name = $this->castToStringOrNull($this->data['contact_name']);
            }

            $contact->email = $this->castToStringOrNull($this->data['contact_email'] ?? null);
            $contact->phone = $this->castToStringOrNull($this->data['contact_phone'] ?? null);
            $contact->location = $this->castToStringOrNull($this->data['contact_location'] ?? null);
            $contact->role = $this->castToStringOrNull($this->data['contact_role'] ?? null);
            $contact->save();
        }

        return $company;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your company and contacts import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
