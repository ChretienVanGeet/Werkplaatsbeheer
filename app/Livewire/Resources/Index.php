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

    public function render(): View
    {
        return view('livewire.resources.index');
    }

    protected function query(): Builder
    {
        return Resource::query();
    }

    protected function sortableFields(): array
    {
        return ['id', 'name', 'machine_type', 'instructor_capacity'];
    }
}
