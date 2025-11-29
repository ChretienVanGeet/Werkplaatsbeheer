<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class SelectGroups extends Component
{
    #[Modelable]
    public array $groups = [];

    public bool $showAll = false;

    public array $selectableGroups;
    public array $hiddenGroups = []; // Groups user can't see but need to preserve

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->selectableGroups = [];

            return;
        }

        if ($this->showAll) {
            // Show all groups when showAll is true
            $this->selectableGroups = Group::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
        } else {
            $userGroupIds = $user->groups()->pluck('groups.id');

            // Only show groups the user has access to for selection
            $this->selectableGroups = Group::query()
                ->whereIn('id', $userGroupIds)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();

            // Separate already selected groups into visible and hidden
            $this->separateGroups();
        }
    }

    public function updatedGroups(): void
    {
        // When groups are updated, merge with hidden groups for the final result
        // Only merge hidden groups when not showing all groups
        if (! $this->showAll) {
            $this->groups = array_merge($this->groups, $this->hiddenGroups);
        }
    }

    private function separateGroups(): void
    {
        $user = Auth::user();

        if (! $user) {
            return;
        }

        $userGroupIds = $user->groups()->pluck('groups.id')->toArray();
        $visibleGroupIds = [];
        $hiddenGroupIds = [];

        // Separate current groups into visible and hidden
        foreach ($this->groups as $groupId) {
            if (in_array($groupId, $userGroupIds)) {
                $visibleGroupIds[] = $groupId;
            } else {
                $hiddenGroupIds[] = $groupId;
            }
        }

        $this->groups = $visibleGroupIds;
        $this->hiddenGroups = $hiddenGroupIds;
    }

    public function render(): View
    {
        return view('livewire.components.select-groups');
    }
}
