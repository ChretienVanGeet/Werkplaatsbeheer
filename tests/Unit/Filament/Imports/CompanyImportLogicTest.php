<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Imports;

use App\Models\Company;
use App\Models\CompanyContact;
use App\Scopes\UserGroupScope;
use Tests\TestCase;

/**
 * Tests the business logic for importing companies and contacts
 * This tests the same logic used in CompanyImporter::resolveRecord()
 */
class CompanyImportLogicTest extends TestCase
{
    public function test_creates_company_without_contacts(): void
    {
        // Simulate the logic from CompanyImporter::resolveRecord()
        $data = [
            'name'      => 'Acme Corp',
            'industry'  => 'Technology',
            'comments'  => 'Great company',
            'locations' => 'Amsterdam',
        ];

        $company = Company::withoutGlobalScope(UserGroupScope::class)->firstOrCreate([
            'name' => $data['name'],
        ], [
            'industry'  => $data['industry'] ?? null,
            'comments'  => $data['comments'] ?? null,
            'locations' => $data['locations'] ?? null,
        ]);

        $this->assertInstanceOf(Company::class, $company);
        $this->assertEquals('Acme Corp', $company->name);
        $this->assertEquals('Technology', $company->industry);
        $this->assertEquals('Great company', $company->comments);
        $this->assertEquals('Amsterdam', $company->locations);
        $this->assertEquals(0, $company->companyContacts()->count());

        $this->assertDatabaseHas('companies', [
            'name'     => 'Acme Corp',
            'industry' => 'Technology',
        ]);
    }

    public function test_creates_company_with_single_contact(): void
    {
        $data = [
            'name'          => 'Acme Corp',
            'industry'      => 'Technology',
            'comments'      => 'Great company',
            'locations'     => 'Amsterdam',
            'contact_name'  => 'John Doe',
            'contact_email' => 'john@acme.com',
            'contact_phone' => '0612345678',
        ];

        // Create company
        $company = Company::withoutGlobalScope(UserGroupScope::class)->firstOrCreate([
            'name' => $data['name'],
        ], [
            'industry'  => $data['industry'] ?? null,
            'comments'  => $data['comments'] ?? null,
            'locations' => $data['locations'] ?? null,
        ]);

        // Create contact if data is provided
        if (! empty($data['contact_name']) || ! empty($data['contact_email']) || ! empty($data['contact_phone'])) {
            $contactExists = $company->companyContacts()
                ->where('name', $data['contact_name'] ?? null)
                ->where('email', $data['contact_email'] ?? null)
                ->where('phone', $data['contact_phone'] ?? null)
                ->exists();

            if (! $contactExists) {
                CompanyContact::create([
                    'company_id' => $company->id,
                    'name'       => $data['contact_name'] ?? null,
                    'email'      => $data['contact_email'] ?? null,
                    'phone'      => $data['contact_phone'] ?? null,
                ]);
            }
        }

        $this->assertInstanceOf(Company::class, $company);
        $this->assertEquals('Acme Corp', $company->name);
        $this->assertEquals(1, $company->companyContacts()->count());

        $contact = $company->companyContacts()->first();
        $this->assertEquals('John Doe', $contact->name);
        $this->assertEquals('john@acme.com', $contact->email);
        $this->assertEquals('0612345678', $contact->phone);

        $this->assertDatabaseHas('companies', ['name' => 'Acme Corp']);
        $this->assertDatabaseHas('company_contacts', [
            'name'  => 'John Doe',
            'email' => 'john@acme.com',
            'phone' => '0612345678',
        ]);
    }

    public function test_creates_multiple_contacts_for_same_company(): void
    {
        // First contact
        $data1 = [
            'name'          => 'Acme Corp',
            'industry'      => 'Technology',
            'contact_name'  => 'John Doe',
            'contact_email' => 'john@acme.com',
            'contact_phone' => '0612345678',
        ];

        $company1 = $this->processImportRow($data1);

        // Second contact for same company
        $data2 = [
            'name'          => 'Acme Corp',
            'industry'      => 'Technology',
            'contact_name'  => 'Jane Smith',
            'contact_email' => 'jane@acme.com',
            'contact_phone' => '0687654321',
        ];

        $company2 = $this->processImportRow($data2);

        // Should be the same company
        $this->assertEquals($company1->id, $company2->id);
        $this->assertEquals(1, Company::withoutGlobalScope(UserGroupScope::class)->count());
        $this->assertEquals(2, CompanyContact::count());

        $company = Company::withoutGlobalScope(UserGroupScope::class)->first();
        $this->assertEquals(2, $company->companyContacts()->count());

        $contacts = $company->companyContacts()->orderBy('name')->get();
        $this->assertEquals('Jane Smith', $contacts[0]->name);
        $this->assertEquals('John Doe', $contacts[1]->name);
    }

    public function test_prevents_duplicate_contacts(): void
    {
        $data = [
            'name'          => 'Acme Corp',
            'industry'      => 'Technology',
            'contact_name'  => 'John Doe',
            'contact_email' => 'john@acme.com',
            'contact_phone' => '0612345678',
        ];

        // Process same data twice
        $company1 = $this->processImportRow($data);
        $company2 = $this->processImportRow($data);

        // Should be same company with only one contact
        $this->assertEquals($company1->id, $company2->id);
        $this->assertEquals(1, Company::withoutGlobalScope(UserGroupScope::class)->count());
        $this->assertEquals(1, CompanyContact::count());
    }

    public function test_creates_contact_with_partial_data(): void
    {
        // Contact with only name
        $company1 = $this->processImportRow([
            'name'         => 'Acme Corp',
            'contact_name' => 'John Doe',
        ]);
        $this->assertEquals(1, $company1->companyContacts()->count());

        // Contact with only email
        $company2 = $this->processImportRow([
            'name'          => 'Beta Inc',
            'contact_email' => 'info@beta.com',
        ]);
        $this->assertEquals(1, $company2->companyContacts()->count());

        // Contact with only phone
        $company3 = $this->processImportRow([
            'name'          => 'Gamma LLC',
            'contact_phone' => '0612345678',
        ]);
        $this->assertEquals(1, $company3->companyContacts()->count());

        $this->assertEquals(3, Company::withoutGlobalScope(UserGroupScope::class)->count());
        $this->assertEquals(3, CompanyContact::count());
    }

    public function test_skips_contact_when_all_contact_fields_empty(): void
    {
        $data = [
            'name'          => 'Acme Corp',
            'industry'      => 'Technology',
            'contact_name'  => null,
            'contact_email' => null,
            'contact_phone' => null,
        ];

        $company = $this->processImportRow($data);

        $this->assertInstanceOf(Company::class, $company);
        $this->assertEquals('Acme Corp', $company->name);
        $this->assertEquals(0, $company->companyContacts()->count());

        $this->assertEquals(1, Company::withoutGlobalScope(UserGroupScope::class)->count());
        $this->assertEquals(0, CompanyContact::count());
    }

    /**
     * Helper method that mimics CompanyImporter::resolveRecord() logic
     */
    private function processImportRow(array $data): Company
    {
        // Create or find the company (bypass UserGroupScope for import logic testing)
        $company = Company::withoutGlobalScope(UserGroupScope::class)->firstOrCreate([
            'name' => $data['name'],
        ], [
            'industry'  => $data['industry'] ?? null,
            'comments'  => $data['comments'] ?? null,
            'locations' => $data['locations'] ?? null,
        ]);

        // Create the company contact if contact data is provided
        if (! empty($data['contact_name']) || ! empty($data['contact_email']) || ! empty($data['contact_phone'])) {
            // Check if this exact contact already exists for this company
            $contactExists = $company->companyContacts()
                ->where('name', $data['contact_name'] ?? null)
                ->where('email', $data['contact_email'] ?? null)
                ->where('phone', $data['contact_phone'] ?? null)
                ->exists();

            if (! $contactExists) {
                CompanyContact::create([
                    'company_id' => $company->id,
                    'name'       => $data['contact_name'] ?? null,
                    'email'      => $data['contact_email'] ?? null,
                    'phone'      => $data['contact_phone'] ?? null,
                ]);
            }
        }

        return $company;
    }
}
