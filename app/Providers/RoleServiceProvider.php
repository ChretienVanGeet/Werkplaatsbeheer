<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class RoleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('read', function (User $user) {
            return in_array($user->role, [
                UserRole::Reader, UserRole::Editor, UserRole::Administrator,
            ], true);
        });

        Gate::define('write', function (User $user) {
            return in_array($user->role, [
                UserRole::Editor, UserRole::Administrator,
            ], true);
        });

        Gate::define('manage', function (User $user) {
            return $user->role === UserRole::Administrator;
        });
    }
}
