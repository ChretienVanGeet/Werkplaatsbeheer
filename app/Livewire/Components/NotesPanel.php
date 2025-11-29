<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Contracts\HasNotesContract;
use App\Models\Note;
use App\Models\NoteAttachment;
use Exception;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class NotesPanel extends Component
{
    use WithFileUploads;

    public bool $readOnly = false;

    public array $files = [];

    public array $fileDisplayNames = [];

    public array $newFiles = [];

    public HasNotesContract $model;

    public string $subject = '';

    public ?string $content = null;

    public ?Note $editingNote = null;

    public array $editingNotesAttachmentNames = [];

    public ?int $deletingNoteId = null;

    public ?int $deletingNoteAttachmentId = null;

    protected function rules(): array
    {
        return [
            'subject'                       => 'string|required',
            'content'                       => 'nullable|string|max:65535',
            'files.*'                       => 'file|max:5120|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,pjpeg,png,gif,webp,zip,rar',
            'editingNotesAttachmentNames.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function messages(): array
    {
        return [
            'files.*.max'      => __('Each file must not exceed 5MB in size.'),
            'files.*.mimes'    => __('Only PDF, Word, Excel, PowerPoint, text, image, and archive files are allowed.'),
            'files.*.file'     => __('The uploaded item must be a valid file.'),
            'subject.required' => __('The subject field is required.'),
            'content.max'      => __('The note content is too long (maximum :max characters).', ['max' => 65535]),
        ];
    }

    public function mount(HasNotesContract $model, bool $readOnly = false): void
    {
        $this->model = $model;
        $this->readOnly = $readOnly;
    }

    public function confirmDeleteNote(int $noteId): void
    {
        $this->deletingNoteId = $noteId;
        Flux::modal('confirm-note-delete')->show();
    }

    public function confirmDeleteNoteAttachment(int $noteAttachmentId): void
    {
        $this->deletingNoteAttachmentId = $noteAttachmentId;
        Flux::modal('confirm-note-attachment-delete')->show();
    }

    public function render(): View
    {
        $notes = $this->model->notes()->orderByDesc('updated_at')->get();

        return view('livewire.components.notes-panel', [
            'notes' => $notes,
        ]);
    }

    public function edit(Note $note): void
    {
        $this->reset(['editingNote', 'files', 'fileDisplayNames', 'subject', 'content']);

        // Force clear files array to prevent duplicate inputs
        $this->files = [];
        $this->fileDisplayNames = [];

        $this->editingNote = $note;

        $attachments = $note->attachments;
        $this->editingNotesAttachmentNames = $attachments
            ->mapWithKeys(
                /** @return array<int, string> */
                fn (NoteAttachment $attachment): array => [
                    $attachment->id => (string) ($attachment->display_name ?? ''),
                ]
            )
            ->toArray();
        $this->subject = $note->subject;
        $this->content = $note->content;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingNote', 'files', 'subject', 'content']);
    }

    public function removeFile(int $index): void
    {
        // Remove the file at the specified index
        unset($this->files[$index]);
        unset($this->fileDisplayNames[$index]);

        // Re-index the arrays to prevent gaps
        $this->files = array_values($this->files);
        $this->fileDisplayNames = array_values($this->fileDisplayNames);
    }

    public function updatedNewFiles(): void
    {
        // Append newly selected files to existing files array
        foreach ($this->newFiles as $file) {
            $this->files[] = $file;
        }

        // Reset newFiles to prepare for next selection
        $this->newFiles = [];
    }

    public function save(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            throw $e;
        }

        try {
            if ($this->editingNote) {
                $this->editingNote->update([
                    'subject' => $this->subject,
                    'content' => $this->content,
                ]);
                $note = $this->editingNote;
            } else {
                /**
                 * @var Note $note
                 */
                $note = $this->model->notes()->create([
                    'subject' => $this->subject,
                    'content' => $this->content,
                    // 'user_id' => auth()->id() // optional
                ]);
            }

            $failedFiles = [];
            foreach ($this->files as $index => $file) {
                try {
                    $storedPath = $file->store('notes', 'public');

                    $note->attachments()->create([
                        'file_path'     => $storedPath,
                        'original_name' => $file->getClientOriginalName(),
                        'display_name'  => $this->fileDisplayNames[$index] ?? null,
                    ]);
                } catch (Exception $e) {
                    $failedFiles[] = $file->getClientOriginalName();
                    Log::error('File upload failed for note attachment', [
                        'note_id'   => $note->id,
                        'file_name' => $file->getClientOriginalName(),
                        'error'     => $e->getMessage(),
                    ]);
                }
            }

            foreach ($this->editingNotesAttachmentNames as $id => $name) {
                /** @var NoteAttachment|null $attachment */
                $attachment = $note->attachments->firstWhere('id', (int) $id);
                if ($attachment && $attachment->display_name !== $name) {
                    $attachment->display_name = $name ?: null;
                    $attachment->save();
                }
            }

            if (empty($failedFiles)) {
                Flux::toast(__('Note saved successfully!'), variant: 'success');
            } else {
                $failedFilesList = implode(', ', $failedFiles);
                Flux::toast(
                    __('Note saved, but some files failed to upload: :files', ['files' => $failedFilesList]),
                    variant: 'warning'
                );
            }

            $this->reset(['editingNote', 'subject', 'content', 'files', 'fileDisplayNames', 'editingNotesAttachmentNames']);

        } catch (Exception $e) {
            Log::error('Failed to save note', [
                'model_type' => get_class($this->model),
                'model_id'   => $this->model->getKey(),
                'error'      => $e->getMessage(),
            ]);

            Flux::toast(__('Failed to save note. Please try again or contact support.'), variant: 'danger');
        }
    }

    public function deleteNote(): void
    {
        $note = Note::query()->findOrFail($this->deletingNoteId);
        $note->delete();
        $this->deletingNoteId = null;
        Flux::modal('confirm-note-delete')->close();
    }

    public function deleteNoteAttachment(): void
    {
        $noteAttachment = NoteAttachment::query()->findOrFail($this->deletingNoteAttachmentId);
        Storage::disk('public')->delete($noteAttachment->file_path);
        $noteAttachment->delete();
        $this->deletingNoteAttachmentId = null;
        Flux::modal('confirm-note-attachment-delete')->close();

        $this->dispatch('attachmentDeleted');
    }
}
