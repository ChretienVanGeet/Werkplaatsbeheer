<?php

declare(strict_types=1);

namespace App\Livewire\Companies;

use App\Livewire\Traits\HasFluxTable;
use App\Models\Company;
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

    public ?int $notesCompanyId = null;
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
        Company::findOrFail($this->deleteSelection)->delete();
        $this->deleteSelection = null;

        Flux::modal('confirm-delete')->close();
    }

    public function openNotes(int $companyId): void
    {
        $company = Company::query()
            ->with(['notes' => fn ($q) => $q->latest('updated_at'), 'notes.creator', 'notes.updater'])
            ->findOrFail($companyId);

        $this->notesCompanyId = $companyId;
        $this->notesModal = $company->notes->map(function ($note) {
            return [
                'id' => $note->id,
                'subject' => $note->subject,
                'content' => $note->content,
                'updated_at' => $note->updated_at?->format('d-m-Y H:i'),
                'creator' => $note->creator?->name,
                'updater' => $note->updater?->name,
            ];
        })->all();

        Flux::modal('company-notes')->show();
    }

    #[On('group-filter-updated')]
    public function refreshForGroupFilter(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.companies.index');
    }

    protected function sortableFields(): array
    {
        return ['id', 'name', 'industry', 'comments', 'locations'];
    }

    protected function query(): Builder
    {
        return Company::query()->withCount('notes');
    }
}
