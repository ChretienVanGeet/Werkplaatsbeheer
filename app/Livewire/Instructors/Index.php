<?php

declare(strict_types=1);

namespace App\Livewire\Instructors;

use App\Livewire\Traits\HasFluxTable;
use App\Models\Instructor;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;
    use HasFluxTable;

    public ?int $notesInstructorId = null;
    /**
     * @var array<int, array{id:int,subject:string,content:string,updated_at:string,creator:?string,updater:?string}>
     */
    public array $notesModal = [];

    public ?int $deleteSelection = null;

    public function confirmDelete(int $id): void
    {
        $this->deleteSelection = $id;
        Flux::modal('confirm-delete')->show();
    }

    public function delete(): void
    {
        Instructor::findOrFail($this->deleteSelection)->delete();
        $this->deleteSelection = null;

        Flux::modal('confirm-delete')->close();
    }

    public function openNotes(int $instructorId): void
    {
        $instructor = Instructor::query()
            ->with(['notes' => fn ($q) => $q->latest('updated_at'), 'notes.creator', 'notes.updater'])
            ->findOrFail($instructorId);

        $this->notesInstructorId = $instructorId;
        $this->notesModal = $instructor->notes->map(function ($note) {
            return [
                'id' => $note->id,
                'subject' => $note->subject,
                'content' => $note->content,
                'updated_at' => $note->updated_at?->format('d-m-Y H:i'),
                'creator' => $note->creator?->name,
                'updater' => $note->updater?->name,
            ];
        })->all();

        Flux::modal('instructor-notes')->show();
    }

    #[On('group-filter-updated')]
    public function refreshForGroupFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.instructors.index');
    }

    protected function sortableFields(): array
    {
        return ['id', 'name', 'supported_resources_count', 'assignments_count'];
    }

    protected function query(): Builder
    {
        return Instructor::query()->withCount(['assignments', 'supportedResources', 'notes']);
    }
}
