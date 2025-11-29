<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

trait FiltersByUserGroups
{
    protected function filterByUserGroups(Builder $query): Builder
    {
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        $userGroupIds = $user->groups()->pluck('groups.id');

        if ($userGroupIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('groups', function (Builder $subQuery) use ($userGroupIds) {
            $subQuery->whereIn('groups.id', $userGroupIds);
        });
    }

    protected function getUserGroupIds(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect();
        }

        return $user->groups()->pluck('groups.id');
    }

    protected function scopeForUserGroups(Builder $query): Builder
    {
        return $this->filterByUserGroups($query);
    }
}
