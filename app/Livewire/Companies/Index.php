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
