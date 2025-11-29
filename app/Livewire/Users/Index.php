<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Livewire\Traits\HasFluxTable;
use App\Models\User;
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
        User::findOrFail($this->deleteSelection)->delete();
        $this->deleteSelection = null;

        Flux::modal('confirm-delete')->close();
    }

    public function render(): View
    {

        $this->idsOnPage = $this->rows()->pluck('id')->toArray();

        return view('livewire.users.index');
    }

    protected function sortableFields(): array
    {
        return ['id', 'name', 'mobile', 'organisation', 'role', 'email', 'email_verified_at', 'created_at', 'updated_at'];
    }

    protected function query(): Builder
    {
        return User::query()->with(['creator', 'updater', 'groups']);
    }
}
