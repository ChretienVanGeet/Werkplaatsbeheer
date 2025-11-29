<?php

declare(strict_types=1);

// app/Livewire/Auth/TwoFactorChallenge.php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use OTPHP\TOTP;

#[Layout('components.layouts.auth')]
class TwoFactorChallenge extends Component
{
    public string $code = '';

    public string $recovery_code = '';

    public bool $remember = false;

    public bool $showRecoveryCode = false;

    public function mount(Request $request): RedirectResponse|Redirector|null
    {
        // Als user geen 2FA aan heeft, direct door
        $user = $request->user();
        if (!$user || empty($user->app_authentication_secret)) {
            session()->put('two_factor_passed', true);

            return redirect()->intended(route('dashboard')); // pas aan naar jouw intended bestemming
        }

        return null;
    }

    public function submit(Request $request): void
    {
        $this->validate([
            'code'          => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        if (!$user || empty($user->app_authentication_secret)) {
            session()->put('two_factor_passed', true);
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        $userAgent = $request->userAgent() ?? '';
        $ip = $request->ip() ?? '';
        $userKey = (string) $user->getKey();
        $rateKey = '2fa:'.sha1($ip.'|'.$userKey);
        if (RateLimiter::tooManyAttempts($rateKey, 5)) {
            throw ValidationException::withMessages([
                'code' => __('Too many attempts. Please try again later.'),
            ]);
        }

        $verified = false;

        // 1) TOTP code
        if ($this->code !== '') {
            $totp = TOTP::createFromSecret($user->app_authentication_secret);
            // kleine clock-skew: window 1 (±30s)
            $verified = $totp->verify($this->code, null, 1);
        }

        // 2) Recovery code
        if (! $verified && $this->recovery_code !== '') {
            $verified = $this->consumeRecoveryCode($user, $this->recovery_code);
        }

        if (! $verified) {
            RateLimiter::hit($rateKey, 60); // 5 pogingen per 60s

            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid.'),
            ]);
        }

        RateLimiter::clear($rateKey);
        session()->put('two_factor_passed', true);

        if ($this->remember) {
            // “Remember this device” cookie (30 dagen)
            $cookieSecure = config('session.secure');
            $cookieSameSite = config('session.same_site');
            cookie()->queue(cookie()->make(
                name: 'two_factor_remember',
                value: encrypt($userKey.'|'.sha1($userAgent)),
                minutes: 60 * 24 * 30,
                httpOnly: true,
                secure: is_bool($cookieSecure) ? $cookieSecure : true,
                sameSite: is_string($cookieSameSite) ? $cookieSameSite : 'lax'
            ));
        }

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }

    protected function consumeRecoveryCode(User $user, string $input): bool
    {
        $codes = $user->app_authentication_recovery_codes;

        if (!$codes) {
            return false;
        }

        foreach ($codes as $i => $code) {
            if (hash_equals($code, $input) || Hash::check($input, $code)) {
                unset($codes[$i]);
                $user->app_authentication_recovery_codes = array_values($codes);
                $user->save();

                return true;
            }
        }

        return false;
    }

    public function toggleRecoveryCode(): void
    {
        $this->showRecoveryCode = ! $this->showRecoveryCode;

        if (! $this->showRecoveryCode) {
            $this->recovery_code = '';
        }
    }

    public function render(): View
    {
        return view('livewire.auth.two-factor-challenge');
    }
}
