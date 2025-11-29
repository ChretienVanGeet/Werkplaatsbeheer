<?php

declare(strict_types=1);

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserGroupScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();

        if (! $user) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $userGroupIds = $user->groups()->pluck('groups.id');
        $selectedGroupId = session('selected_group_id');

        // If a specific group is selected, filter by that group only (if user has access)
        if ($selectedGroupId && $userGroupIds->contains($selectedGroupId)) {
            $builder->whereHas('groups', function (Builder $query) use ($selectedGroupId) {
                $query->where('groups.id', $selectedGroupId);
            });

            return;
        }

        // Otherwise, apply the standard user group filtering
        $builder->where(function (Builder $query) use ($userGroupIds) {
            // Items with no groups attached (visible to everyone)
            $query->whereDoesntHave('groups');

            // OR items with groups that the user has access to (if user has groups)
            if ($userGroupIds->isNotEmpty()) {
                $query->orWhereHas('groups', function (Builder $subQuery) use ($userGroupIds) {
                    $subQuery->whereIn('groups.id', $userGroupIds);
                });
            }
        });
    }
}
