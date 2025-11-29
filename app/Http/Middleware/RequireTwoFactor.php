<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;

class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user) {
            // If user doesn't have 2FA set up yet, redirect to setup
            if (empty($user->app_authentication_secret)) {
                if (! $request->routeIs(['two-factor.setup', 'logout'])) {
                    return redirect()->guest(route('two-factor.setup'));
                }
            }
            // If user has 2FA set up, check if they've passed 2FA challenge
            else {
                if (! $request->routeIs(['two-factor.challenge', 'two-factor.setup', 'logout'])) {
                    $passed = $request->session()->get('two_factor_passed', false);

                    if (! $passed && $this->isRememberedDevice($request, $user)) {
                        $request->session()->put('two_factor_passed', true);
                        $passed = true;
                    }

                    if (! $passed) {
                        return redirect()->route('two-factor.challenge');
                    }
                }
            }
        }

        return $next($request);
    }

    private function isRememberedDevice(Request $request, User $user): bool
    {
        $cookie = $request->cookie('two_factor_remember');

        if (! $cookie) {
            return false;
        }

        try {
            // Decrypt function expects string but cookie might be array in some contexts
            if (is_array($cookie)) {
                return false;
            }

            $decrypted = decrypt($cookie);

            if (!is_string($decrypted)) {
                return false;
            }

            $parts = explode('|', $decrypted, 2);

            if (count($parts) !== 2) {
                return false;
            }

            [$userId, $userAgent] = $parts;
            $requestUserAgent = $request->userAgent();

            return (int) $userId === $user->getKey()
                && $userAgent === sha1($requestUserAgent ?? '');
        } catch (\Exception) {
            return false;
        }
    }
}
