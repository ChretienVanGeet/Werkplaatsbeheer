<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Group;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ShowGroups extends Component
{
    /** @var Collection<int, Group> */
    public Collection $groups;

    /** @param Collection<int, Group> $groups */
    public function mount(Collection $groups): void
    {
        $user = Auth::user();

        if (! $user) {
            $this->groups = collect();

            return;
        }

        $userGroupIds = $user->groups()->pluck('groups.id');

        // Only show groups that the user has access to
        $this->groups = $groups->filter(function (Group $group) use ($userGroupIds) {
            return $userGroupIds->contains($group->id);
        });
    }

    public function render(): View
    {
        return view('livewire.components.show-groups');
    }
}
