<?php

declare(strict_types=1);

namespace Tests\Unit\Livewire\Components;

use App\Livewire\Components\NotesPanel;
use App\Models\Company;
use App\Models\Note;
use App\Models\NoteAttachment;
use Database\Seeders\DevelopmentSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\DisablesUserGroupScope;
use Tests\TestCase;

class NotesPanelTest extends TestCase
{
    use DisablesUserGroupScope;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DevelopmentSeeder::class);

        // Set up authenticated user with access to all groups for component tests
        $this->authenticateUserWithFullAccess();
    }

    public function test_renders_successfully(): void
    {
        $company = Company::first();

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->assertStatus(200);
    }

    public function test_confirm_delete_note_sets_deleting_note_id(): void
    {
        $company = Company::first();

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->call('confirmDeleteNote', 123);

        $component->assertSet('deletingNoteId', 123);
    }

    public function test_confirm_delete_note_attachment_sets_deleting_note_attachment_id(): void
    {
        $company = Company::first();

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->call('confirmDeleteNoteAttachment', 456);

        $component->assertSet('deletingNoteAttachmentId', 456);
    }

    public function test_edit_sets_editing_note_and_form_fields(): void
    {
        $company = Company::first();
        $note = $company->notes()->first();

        if (! $note) {
            $note = Note::factory()->create([
                'noteable_id'   => $company->id,
                'noteable_type' => Company::class,
                'subject'       => 'Test Subject',
                'content'       => 'Test Content',
            ]);
        }

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->call('edit', $note);

        $component->assertSet('subject', $note->subject)
            ->assertSet('content', $note->content);
    }

    public function test_edit_handles_note_with_attachments(): void
    {
        $company = Company::first();
        $note = Note::factory()->create([
            'noteable_id'   => $company->id,
            'noteable_type' => Company::class,
            'subject'       => 'Test Subject',
            'content'       => 'Test Content',
        ]);

        $attachment = NoteAttachment::create([
            'note_id'       => $note->id,
            'file_path'     => 'test/path.pdf',
            'original_name' => 'original.pdf',
            'display_name'  => 'Test File',
        ]);

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->call('edit', $note);

        $component->assertSet('editingNotesAttachmentNames', [$attachment->id => 'Test File']);
    }

    public function test_cancel_edit_resets_form_fields(): void
    {
        $company = Company::first();
        $note = Note::first();

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->set('subject', 'Test Subject')
            ->set('content', 'Test Content')
            ->set('editingNote', $note);

        $component->call('cancelEdit');

        $component->assertSet('editingNote', null)
            ->assertSet('subject', '')
            ->assertSet('content', null);
    }

    public function test_save_creates_new_note_when_not_editing(): void
    {
        $company = Company::first();

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->set('subject', 'Test Subject')
            ->set('content', 'Test Content')
            ->call('save');

        $component->assertHasNoErrors();

        $this->assertDatabaseHas('notes', [
            'noteable_id'   => $company->id,
            'noteable_type' => Company::class,
            'subject'       => 'Test Subject',
            'content'       => 'Test Content',
        ]);
    }

    public function test_save_updates_existing_note_when_editing(): void
    {
        $company = Company::first();
        $note = Note::factory()->create([
            'noteable_id'   => $company->id,
            'noteable_type' => Company::class,
            'subject'       => 'Original Subject',
            'content'       => 'Original Content',
        ]);

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->set('editingNote', $note)
            ->set('subject', 'Updated Subject')
            ->set('content', 'Updated Content')
            ->call('save');

        $component->assertHasNoErrors();

        $note->refresh();
        $this->assertEquals('Updated Subject', $note->subject);
        $this->assertEquals('Updated Content', $note->content);
    }

    public function test_save_with_file_uploads(): void
    {
        Storage::fake('public');

        $company = Company::first();
        $file = UploadedFile::fake()->create('test.pdf', 100);

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->set('subject', 'Test Subject')
            ->set('content', 'Test Content')
            ->set('files', [$file])
            ->call('save');

        $component->assertHasNoErrors();

        $this->assertDatabaseHas('notes', [
            'noteable_id' => $company->id,
            'subject'     => 'Test Subject',
        ]);

        $this->assertDatabaseHas('note_attachments', [
            'original_name' => 'test.pdf',
        ]);
    }

    public function test_validation_rules_enforce_required_subject(): void
    {
        $company = Company::first();

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->set('subject', '')
            ->call('save');

        $component->assertHasErrors(['subject']);
    }

    public function test_validation_rules_enforce_content_max_length(): void
    {
        $company = Company::first();

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->set('subject', 'Valid Subject')
            ->set('content', str_repeat('a', 65536))
            ->call('save');

        $component->assertHasErrors(['content']);
    }

    public function test_delete_note_removes_note(): void
    {
        $company = Company::first();
        $note = Note::factory()->create([
            'noteable_id'   => $company->id,
            'noteable_type' => Company::class,
        ]);

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->set('deletingNoteId', $note->id)
            ->call('deleteNote');

        $component->assertSet('deletingNoteId', null);
        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }

    public function test_delete_note_attachment_removes_file_and_record(): void
    {
        Storage::fake('public');

        $company = Company::first();
        $note = Note::factory()->create([
            'noteable_id'   => $company->id,
            'noteable_type' => Company::class,
        ]);

        $attachment = NoteAttachment::create([
            'note_id'       => $note->id,
            'file_path'     => 'test/file.pdf',
            'original_name' => 'file.pdf',
            'display_name'  => 'Test File',
        ]);

        // Create the file so it exists when we try to delete it
        Storage::disk('public')->put('test/file.pdf', 'test content');

        $component = Livewire::test(NotesPanel::class, ['model' => $company]);

        $component->set('deletingNoteAttachmentId', $attachment->id)
            ->call('deleteNoteAttachment');

        $component->assertSet('deletingNoteAttachmentId', null)
            ->assertDispatched('attachmentDeleted');

        $this->assertDatabaseMissing('note_attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertMissing('test/file.pdf');
    }
}
