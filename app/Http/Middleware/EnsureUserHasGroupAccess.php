<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Group;
use App\Models\Participant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasGroupAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $modelType = null): Response
    {
        $user = Auth::user();

        if (! $user) {
            abort(401, 'Unauthorized');
        }

        $userGroupIds = $user->groups()->pluck('groups.id');

        if ($userGroupIds->isEmpty()) {
            abort(403, 'You do not belong to any groups');
        }

        $modelId = $this->getModelIdFromRoute($request, $modelType);

        if ($modelId && $modelType) {
            $hasAccess = match ($modelType) {
                'activity' => Activity::whereHas('groups', function ($query) use ($userGroupIds) {
                    $query->whereIn('groups.id', $userGroupIds);
                })->where('id', $modelId)->exists(),
                'company' => Company::whereHas('groups', function ($query) use ($userGroupIds) {
                    $query->whereIn('groups.id', $userGroupIds);
                })->where('id', $modelId)->exists(),
                'participant' => Participant::whereHas('groups', function ($query) use ($userGroupIds) {
                    $query->whereIn('groups.id', $userGroupIds);
                })->where('id', $modelId)->exists(),
                'group' => Group::whereIn('id', $userGroupIds)->where('id', $modelId)->exists(),
                default => true,
            };

            if (! $hasAccess) {
                abort(403, 'You do not have access to this resource');
            }
        }

        $request->merge(['userGroupIds' => $userGroupIds]);

        return $next($request);
    }

    private function getModelIdFromRoute(Request $request, ?string $modelType): ?int
    {
        if (! $modelType) {
            return null;
        }

        $route = $request->route();
        if (! $route) {
            return null;
        }

        $id = $route->parameter($modelType);

        return is_numeric($id) ? (int) $id : null;
    }
}
