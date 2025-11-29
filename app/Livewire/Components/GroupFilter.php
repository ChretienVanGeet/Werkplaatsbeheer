<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class GroupFilter extends Component
{
    public ?int $selectedGroupId = null;

    public function mount(): void
    {
        $sessionValue = session('selected_group_id');
        $this->selectedGroupId = is_int($sessionValue) ? $sessionValue : null;
    }

    public function updatedSelectedGroupId(): void
    {
        session(['selected_group_id' => $this->selectedGroupId]);
        $this->dispatch('group-filter-updated', groupId: $this->selectedGroupId);
    }

    #[On('reset-group-filter')]
    public function resetFilter(): void
    {
        $this->selectedGroupId = null;
        session()->forget('selected_group_id');
        $this->dispatch('group-filter-updated', groupId: null);
    }

    public function getUserGroups(): Collection
    {
        $user = Auth::user();
        if (!$user) {
            return new Collection();
        }

        return $user->groups()->orderBy('name')->get();
    }

    public function render(): View
    {
        return view('livewire.components.group-filter', [
            'userGroups' => $this->getUserGroups(),
        ]);
    }
}
