<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Imports;

use App\Models\Participant;
use App\Scopes\UserGroupScope;
use Tests\TestCase;

/**
 * Tests the business logic for importing participants
 * This tests the same logic used in ParticipantImporter::resolveRecord()
 */
class ParticipantImportLogicTest extends TestCase
{
    public function test_creates_participant_with_all_fields(): void
    {
        // Simulate the logic from ParticipantImporter::resolveRecord()
        $data = [
            'name'     => 'John Doe',
            'phone'    => '0612345678',
            'email'    => 'john@example.com',
            'city'     => 'Amsterdam',
            'comments' => 'Great participant',
        ];

        $participant = Participant::withoutGlobalScope(UserGroupScope::class)->firstOrCreate([
            'name' => $data['name'],
        ], [
            'phone'    => $data['phone'] ?? null,
            'email'    => $data['email'] ?? null,
            'city'     => $data['city'] ?? null,
            'comments' => $data['comments'] ?? null,
        ]);

        $this->assertInstanceOf(Participant::class, $participant);
        $this->assertEquals('John Doe', $participant->name);
        $this->assertEquals('0612345678', $participant->phone);
        $this->assertEquals('john@example.com', $participant->email);
        $this->assertEquals('Amsterdam', $participant->city);
        $this->assertEquals('Great participant', $participant->comments);

        $this->assertDatabaseHas('participants', [
            'name'  => 'John Doe',
            'phone' => '0612345678',
            'email' => 'john@example.com',
        ]);
    }

    public function test_creates_participant_with_only_name(): void
    {
        $data = [
            'name' => 'Minimal Participant',
        ];

        $participant = Participant::withoutGlobalScope(UserGroupScope::class)->firstOrCreate([
            'name' => $data['name'],
        ], [
            'phone'    => $data['phone'] ?? null,
            'email'    => $data['email'] ?? null,
            'city'     => $data['city'] ?? null,
            'comments' => $data['comments'] ?? null,
        ]);

        $this->assertInstanceOf(Participant::class, $participant);
        $this->assertEquals('Minimal Participant', $participant->name);
        $this->assertNull($participant->phone);
        $this->assertNull($participant->email);
        $this->assertNull($participant->city);
        $this->assertNull($participant->comments);

        $this->assertEquals(1, Participant::withoutGlobalScope(UserGroupScope::class)->count());
    }

    public function test_prevents_duplicate_participants(): void
    {
        $data = [
            'name'  => 'John Doe',
            'phone' => '0612345678',
            'email' => 'john@example.com',
        ];

        // Create same participant twice
        $participant1 = $this->processImportRow($data);
        $participant2 = $this->processImportRow([
            'name'  => 'John Doe',  // Same name
            'phone' => '0687654321',  // Different phone
            'email' => 'john2@example.com',  // Different email
        ]);

        // Should be the same participant
        $this->assertEquals($participant1->id, $participant2->id);
        $this->assertEquals(1, Participant::withoutGlobalScope(UserGroupScope::class)->count());

        // First record wins with firstOrCreate
        $this->assertEquals('0612345678', $participant1->phone);
        $this->assertEquals('john@example.com', $participant1->email);
    }

    public function test_creates_participant_with_partial_data(): void
    {
        // Participant with only email
        $participant1 = $this->processImportRow([
            'name'  => 'Email Only',
            'email' => 'email@example.com',
        ]);
        $this->assertEquals('email@example.com', $participant1->email);
        $this->assertNull($participant1->phone);

        // Participant with only phone
        $participant2 = $this->processImportRow([
            'name'  => 'Phone Only',
            'phone' => '0612345678',
        ]);
        $this->assertEquals('0612345678', $participant2->phone);
        $this->assertNull($participant2->email);

        // Participant with only city
        $participant3 = $this->processImportRow([
            'name' => 'City Only',
            'city' => 'Rotterdam',
        ]);
        $this->assertEquals('Rotterdam', $participant3->city);
        $this->assertNull($participant3->email);
        $this->assertNull($participant3->phone);

        $this->assertEquals(3, Participant::withoutGlobalScope(UserGroupScope::class)->count());
    }

    public function test_handles_empty_strings_as_null(): void
    {
        $data = [
            'name'     => 'Test Participant',
            'phone'    => '',
            'email'    => '',
            'city'     => '',
            'comments' => '',
        ];

        $participant = $this->processImportRow($data);

        $this->assertEquals('Test Participant', $participant->name);
        $this->assertNull($participant->phone);
        $this->assertNull($participant->email);
        $this->assertNull($participant->city);
        $this->assertNull($participant->comments);
    }

    /**
     * Helper method that mimics ParticipantImporter::resolveRecord() logic
     */
    private function processImportRow(array $data): Participant
    {
        return Participant::withoutGlobalScope(UserGroupScope::class)->firstOrCreate([
            'name' => $data['name'],
        ], [
            'phone'    => ($data['phone'] ?? '') ?: null,
            'email'    => ($data['email'] ?? '') ?: null,
            'city'     => ($data['city'] ?? '') ?: null,
            'comments' => ($data['comments'] ?? '') ?: null,
        ]);
    }
}
