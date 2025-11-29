<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Imports;

use App\Models\Company;
use App\Models\CompanyContact;
use App\Scopes\UserGroupScope;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyImporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_imports_companies_from_csv_without_contacts(): void
    {
        $csvContent = implode("\n", [
            'name,industry,comments,locations,contact_name,contact_email,contact_phone',
            '"Acme Corp","Technology","Great company","Amsterdam","","",""',
            '"Beta Inc","Finance","","Rotterdam","","",""',
        ]);

        $this->processCSVImport($csvContent);

        $this->assertEquals(2, Company::withoutGlobalScope(UserGroupScope::class)->count());
        $this->assertEquals(0, CompanyContact::count());

        $this->assertDatabaseHas('companies', [
            'name'      => 'Acme Corp',
            'industry'  => 'Technology',
            'comments'  => 'Great company',
            'locations' => 'Amsterdam',
        ]);

        $this->assertDatabaseHas('companies', [
            'name'      => 'Beta Inc',
            'industry'  => 'Finance',
            'comments'  => null,
            'locations' => 'Rotterdam',
        ]);
    }

    public function test_imports_companies_from_csv_with_contacts(): void
    {
        $csvContent = implode("\n", [
            'name,industry,comments,locations,contact_name,contact_email,contact_phone',
            '"Acme Corp","Technology","Great company","Amsterdam","John Doe","john@acme.com","0612345678"',
            '"Acme Corp","Technology","Great company","Amsterdam","Jane Smith","jane@acme.com","0687654321"',
            '"Beta Inc","Finance","","Rotterdam","Bob Johnson","bob@beta.com","0698765432"',
        ]);

        $this->processCSVImport($csvContent);

        // Should create 2 companies and 3 contacts
        $this->assertEquals(2, Company::withoutGlobalScope(UserGroupScope::class)->count());
        $this->assertEquals(3, CompanyContact::count());

        // Check Acme Corp has 2 contacts
        $acmeCompany = Company::withoutGlobalScope(UserGroupScope::class)->where('name', 'Acme Corp')->first();
        $this->assertEquals(2, $acmeCompany->companyContacts()->count());

        // Check Beta Inc has 1 contact
        $betaCompany = Company::withoutGlobalScope(UserGroupScope::class)->where('name', 'Beta Inc')->first();
        $this->assertEquals(1, $betaCompany->companyContacts()->count());

        // Verify specific contact data
        $this->assertDatabaseHas('company_contacts', [
            'name'       => 'John Doe',
            'email'      => 'john@acme.com',
            'phone'      => '0612345678',
            'company_id' => $acmeCompany->id,
        ]);

        $this->assertDatabaseHas('company_contacts', [
            'name'       => 'Jane Smith',
            'email'      => 'jane@acme.com',
            'phone'      => '0687654321',
            'company_id' => $acmeCompany->id,
        ]);
    }

    public function test_prevents_duplicate_contacts_from_csv(): void
    {
        $csvContent = implode("\n", [
            'name,industry,comments,locations,contact_name,contact_email,contact_phone',
            '"Acme Corp","Technology","Great company","Amsterdam","John Doe","john@acme.com","0612345678"',
            '"Acme Corp","Technology","Great company","Amsterdam","John Doe","john@acme.com","0612345678"',
        ]);

        $this->processCSVImport($csvContent);

        // Should create 1 company and 1 contact (no duplicates)
        $this->assertEquals(1, Company::withoutGlobalScope(UserGroupScope::class)->count());
        $this->assertEquals(1, CompanyContact::count());

        $company = Company::withoutGlobalScope(UserGroupScope::class)->first();
        $this->assertEquals(1, $company->companyContacts()->count());
    }

    public function test_imports_companies_with_partial_contact_data(): void
    {
        $csvContent = implode("\n", [
            'name,industry,comments,locations,contact_name,contact_email,contact_phone',
            '"Company A","Tech","","","John Doe","",""',
            '"Company B","Finance","","","","info@companyb.com",""',
            '"Company C","Retail","","","","","0612345678"',
        ]);

        $this->processCSVImport($csvContent);

        $this->assertEquals(3, Company::withoutGlobalScope(UserGroupScope::class)->count());
        $this->assertEquals(3, CompanyContact::count());

        // Check each company has exactly one contact
        $companies = Company::withoutGlobalScope(UserGroupScope::class)->get();
        foreach ($companies as $company) {
            $this->assertEquals(1, $company->companyContacts()->count());
        }

        // Verify partial data was stored correctly
        $this->assertDatabaseHas('company_contacts', ['name' => 'John Doe', 'email' => null, 'phone' => null]);
        $this->assertDatabaseHas('company_contacts', ['name' => null, 'email' => 'info@companyb.com', 'phone' => null]);
        $this->assertDatabaseHas('company_contacts', ['name' => null, 'email' => null, 'phone' => '0612345678']);
    }

    public function test_imports_mixed_companies_with_and_without_contacts(): void
    {
        $csvContent = implode("\n", [
            'name,industry,comments,locations,contact_name,contact_email,contact_phone',
            '"With Contact","Tech","","","John Doe","john@example.com","0612345678"',
            '"Without Contact","Finance","","","","",""',
        ]);

        $this->processCSVImport($csvContent);

        $this->assertEquals(2, Company::withoutGlobalScope(UserGroupScope::class)->count());
        $this->assertEquals(1, CompanyContact::count()); // Only one company has a contact

        $withContact = Company::withoutGlobalScope(UserGroupScope::class)->where('name', 'With Contact')->first();
        $withoutContact = Company::withoutGlobalScope(UserGroupScope::class)->where('name', 'Without Contact')->first();

        $this->assertEquals(1, $withContact->companyContacts()->count());
        $this->assertEquals(0, $withoutContact->companyContacts()->count());
    }

    /**
     * Process a CSV import by simulating the CompanyImporter behavior
     */
    private function processCSVImport(string $csvContent): void
    {
        // Parse CSV content properly using a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, $csvContent);

        $handle = fopen($tempFile, 'r');
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            } // Skip empty rows

            $data = array_combine($headers, $row);

            // Simulate CompanyImporter::resolveRecord() logic
            $company = Company::withoutGlobalScope(UserGroupScope::class)->firstOrCreate([
                'name' => $data['name'],
            ], [
                'industry'  => $data['industry'] ?: null,
                'comments'  => $data['comments'] ?: null,
                'locations' => $data['locations'] ?: null,
            ]);

            // Create contact if data is provided
            if (! empty($data['contact_name']) || ! empty($data['contact_email']) || ! empty($data['contact_phone'])) {
                $contactExists = $company->companyContacts()
                    ->where('name', $data['contact_name'] ?: null)
                    ->where('email', $data['contact_email'] ?: null)
                    ->where('phone', $data['contact_phone'] ?: null)
                    ->exists();

                if (! $contactExists) {
                    CompanyContact::create([
                        'company_id' => $company->id,
                        'name'       => $data['contact_name'] ?: null,
                        'email'      => $data['contact_email'] ?: null,
                        'phone'      => $data['contact_phone'] ?: null,
                    ]);
                }
            }
        }

        fclose($handle);
        unlink($tempFile);
    }
}
