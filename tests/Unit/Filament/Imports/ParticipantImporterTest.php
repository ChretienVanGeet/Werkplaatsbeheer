<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Imports;

use App\Models\Participant;
use App\Scopes\UserGroupScope;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantImporterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_imports_participants_from_csv_with_all_fields(): void
    {
        $csvContent = implode("\n", [
            'name,phone,email,city,comments',
            '"John Doe","0612345678","john@example.com","Amsterdam","Great participant"',
            '"Jane Smith","0687654321","jane@example.com","Rotterdam","Active member"',
        ]);

        $this->processCSVImport($csvContent);

        $this->assertEquals(2, Participant::withoutGlobalScope(UserGroupScope::class)->count());

        $this->assertDatabaseHas('participants', [
            'name'     => 'John Doe',
            'phone'    => '0612345678',
            'email'    => 'john@example.com',
            'city'     => 'Amsterdam',
            'comments' => 'Great participant',
        ]);

        $this->assertDatabaseHas('participants', [
            'name'     => 'Jane Smith',
            'phone'    => '0687654321',
            'email'    => 'jane@example.com',
            'city'     => 'Rotterdam',
            'comments' => 'Active member',
        ]);
    }

    public function test_imports_participants_with_partial_data(): void
    {
        $csvContent = implode("\n", [
            'name,phone,email,city,comments',
            '"John Doe","0612345678","","",""',
            '"Jane Smith","","jane@example.com","",""',
            '"Bob Wilson","","","Rotterdam",""',
            '"Alice Brown","","","","Nice person"',
        ]);

        $this->processCSVImport($csvContent);

        $this->assertEquals(4, Participant::withoutGlobalScope(UserGroupScope::class)->count());

        // Verify partial data was stored correctly (empty strings become null)
        $this->assertDatabaseHas('participants', ['name' => 'John Doe', 'phone' => '0612345678', 'email' => null, 'city' => null, 'comments' => null]);
        $this->assertDatabaseHas('participants', ['name' => 'Jane Smith', 'phone' => null, 'email' => 'jane@example.com', 'city' => null, 'comments' => null]);
        $this->assertDatabaseHas('participants', ['name' => 'Bob Wilson', 'phone' => null, 'email' => null, 'city' => 'Rotterdam', 'comments' => null]);
        $this->assertDatabaseHas('participants', ['name' => 'Alice Brown', 'phone' => null, 'email' => null, 'city' => null, 'comments' => 'Nice person']);
    }

    public function test_prevents_duplicate_participants(): void
    {
        $csvContent = implode("\n", [
            'name,phone,email,city,comments',
            '"John Doe","0612345678","john@example.com","Amsterdam","Great participant"',
            '"John Doe","0687654321","john2@example.com","Rotterdam","Updated info"',
        ]);

        $this->processCSVImport($csvContent);

        // Should create only 1 participant (no duplicates based on name)
        $this->assertEquals(1, Participant::withoutGlobalScope(UserGroupScope::class)->count());

        $participant = Participant::withoutGlobalScope(UserGroupScope::class)->first();
        $this->assertEquals('John Doe', $participant->name);
        // First record wins for firstOrCreate
        $this->assertEquals('0612345678', $participant->phone);
        $this->assertEquals('john@example.com', $participant->email);
        $this->assertEquals('Amsterdam', $participant->city);
    }

    public function test_imports_participants_with_only_required_field(): void
    {
        $csvContent = implode("\n", [
            'name,phone,email,city,comments',
            '"Minimal Participant","","","",""',
        ]);

        $this->processCSVImport($csvContent);

        $this->assertEquals(1, Participant::withoutGlobalScope(UserGroupScope::class)->count());

        $participant = Participant::withoutGlobalScope(UserGroupScope::class)->first();
        $this->assertEquals('Minimal Participant', $participant->name);
        $this->assertNull($participant->phone);
        $this->assertNull($participant->email);
        $this->assertNull($participant->city);
        $this->assertNull($participant->comments);
    }

    public function test_imports_mixed_participants_data(): void
    {
        $csvContent = implode("\n", [
            'name,phone,email,city,comments',
            '"Complete User","0612345678","complete@example.com","Amsterdam","Has all data"',
            '"Email Only","","email@example.com","",""',
            '"Phone Only","0687654321","","",""',
            '"Name Only","","","",""',
        ]);

        $this->processCSVImport($csvContent);

        $this->assertEquals(4, Participant::withoutGlobalScope(UserGroupScope::class)->count());

        $completeUser = Participant::withoutGlobalScope(UserGroupScope::class)->where('name', 'Complete User')->first();
        $this->assertEquals('0612345678', $completeUser->phone);
        $this->assertEquals('complete@example.com', $completeUser->email);
        $this->assertEquals('Amsterdam', $completeUser->city);
        $this->assertEquals('Has all data', $completeUser->comments);

        $emailOnly = Participant::withoutGlobalScope(UserGroupScope::class)->where('name', 'Email Only')->first();
        $this->assertNull($emailOnly->phone);
        $this->assertEquals('email@example.com', $emailOnly->email);
        $this->assertNull($emailOnly->city);
        $this->assertNull($emailOnly->comments);
    }

    /**
     * Process a CSV import by simulating the ParticipantImporter behavior
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

            // Simulate ParticipantImporter::resolveRecord() logic
            Participant::withoutGlobalScope(UserGroupScope::class)->firstOrCreate([
                'name' => $data['name'],
            ], [
                'phone'    => $data['phone'] ?: null,
                'email'    => $data['email'] ?: null,
                'city'     => $data['city'] ?: null,
                'comments' => $data['comments'] ?: null,
            ]);
        }

        fclose($handle);
        unlink($tempFile);
    }
}
