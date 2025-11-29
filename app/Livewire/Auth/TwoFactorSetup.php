<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use OTPHP\TOTP;

#[Layout('components.layouts.auth')]
class TwoFactorSetup extends Component
{
    public string $qrCode = '';

    public string $secret = '';

    public string $confirmationCode = '';

    public bool $setupComplete = false;

    public bool $remember = false;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        // If 2FA is already set up, redirect to dashboard
        if ($user && ! empty($user->app_authentication_secret)) {
            $this->redirect(route('dashboard'));
        }

        $this->generateSecret();
    }

    public function generateSecret(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $totp = TOTP::generate();
        $appName = config('app.name');
        $email = $user->email !== '' ? $user->email : 'user@example.com';
        $appNameStr = is_string($appName) && $appName !== '' ? $appName : 'App';

        $totp->setLabel($email);
        $totp->setIssuer($appNameStr);

        $this->secret = $totp->getSecret();
        $this->qrCode = $totp->getQrCodeUri(
            'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=[DATA]',
            '[DATA]'
        );
    }

    public function confirmSetup(): void
    {
        $this->validate([
            'confirmationCode' => 'required|string|min:6|max:6',
        ]);

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user || $this->secret === '') {
            return;
        }

        $totp = TOTP::createFromSecret($this->secret);
        $appName = config('app.name');
        $email = $user->email !== '' ? $user->email : 'user@example.com';
        $appNameStr = is_string($appName) && $appName !== '' ? $appName : 'App';
        $code = $this->confirmationCode !== '' ? $this->confirmationCode : '000000';

        $totp->setLabel($email);
        $totp->setIssuer($appNameStr);

        if (! $totp->verify($code)) {
            $this->addError('confirmationCode', __('The verification code is invalid.'));

            return;
        }

        // Save the secret
        $user->saveAppAuthenticationSecret($this->secret);

        // Generate recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        }
        $user->saveAppAuthenticationRecoveryCodes($recoveryCodes);

        $this->setupComplete = true;

        // Set remember device cookie if requested
        if ($this->remember) {
            $userAgent = request()->userAgent() ?? '';
            $userKey = (string) $user->getKey();
            $cookieSecure = config('session.secure');
            $cookieSameSite = config('session.same_site');

            cookie()->queue(cookie()->make(
                name: 'two_factor_remember',
                value: encrypt($userKey.'|'.sha1($userAgent)),
                minutes: 60 * 24 * 30, // 30 days
                httpOnly: true,
                secure: is_bool($cookieSecure) ? $cookieSecure : true,
                sameSite: is_string($cookieSameSite) ? $cookieSameSite : 'lax'
            ));
        }
    }

    public function continueToApplication(): void
    {
        // If user came from settings, redirect back to settings
        if (request()->header('referer') && str_contains(request()->header('referer'), 'settings')) {
            $this->redirect(route('settings.two-factor'));

            return;
        }

        $this->redirect(route('dashboard'));
    }

    public function render(): View
    {
        return view('livewire.auth.two-factor-setup');
    }
}
