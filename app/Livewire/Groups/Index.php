<?php

declare(strict_types=1);

namespace App\Livewire\Groups;

use App\Livewire\Traits\HasFluxTable;
use App\Models\Group;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;
    use HasFluxTable;

    #[Locked]
    public array $idsOnPage = [];
    public ?int $deleteSelection = null;
    public function confirmDelete(int $id): void
    {
        $this->deleteSelection = $id;
        Flux::modal('confirm-delete')->show();
    }

    public function delete(): void
    {
        Group::findOrFail($this->deleteSelection)->delete();
        $this->deleteSelection = null;

        Flux::modal('confirm-delete')->close();
    }

    public function render(): View
    {

        $this->idsOnPage = $this->rows()->pluck('id')->toArray();

        return view('livewire.groups.index');
    }

    protected function sortableFields(): array
    {
        return ['id', 'name', 'description', 'created_at', 'updated_at'];
    }

    protected function query(): Builder
    {
        return Group::query()->with(['creator', 'updater']);
    }
}
