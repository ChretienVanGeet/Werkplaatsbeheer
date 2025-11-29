<?php

declare(strict_types=1);

namespace App\Livewire\Resources;

use App\Livewire\Traits\HasFluxTable;
use App\Models\Resource;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    use HasFluxTable;

    public ?int $notesResourceId = null;
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
        Resource::findOrFail($this->deleteSelection)->delete();
        $this->deleteSelection = null;
        Flux::modal('confirm-delete')->close();
    }

    public function openNotes(int $resourceId): void
    {
        $resource = Resource::query()
            ->with(['notes' => fn ($q) => $q->latest('updated_at'), 'notes.creator', 'notes.updater'])
            ->findOrFail($resourceId);

        $this->notesResourceId = $resourceId;
        $this->notesModal = $resource->notes->map(function ($note) {
            return [
                'id' => $note->id,
                'subject' => $note->subject,
                'content' => $note->content,
                'updated_at' => $note->updated_at?->format('d-m-Y H:i'),
                'creator' => $note->creator?->name,
                'updater' => $note->updater?->name,
            ];
        })->all();

        Flux::modal('resource-notes')->show();
    }

    public function render(): View
    {
        return view('livewire.resources.index');
    }

    protected function query(): Builder
    {
        return Resource::query()->withCount('notes');
    }

    protected function sortableFields(): array
    {
        return ['id', 'name', 'machine_type', 'instructor_capacity'];
    }
}
