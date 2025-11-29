<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use OTPHP\TOTP;

class TwoFactorAuthentication extends Component
{
    public string $qrCode = '';

    public string $secret = '';

    public string $confirmationCode = '';

    public bool $showQrCode = false;

    public bool $confirming = false;

    public bool $showPasswordConfirm = false;

    public string $password = '';

    public function mount(): void
    {
        $this->resetState();
    }

    public function resetState(): void
    {
        $this->qrCode = '';
        $this->secret = '';
        $this->confirmationCode = '';
        $this->showQrCode = false;
        $this->confirming = false;
        $this->showPasswordConfirm = false;
        $this->password = '';
        session()->forget('pending_action');
    }

    public function enable2FA(): void
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

        $this->showQrCode = true;
        $this->confirming = true;
    }

    public function confirmEnable(): void
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

        // Generate new recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        }
        $user->saveAppAuthenticationRecoveryCodes($recoveryCodes);

        $this->resetState();

        session()->flash('status', '2fa-enabled');
    }

    public function showPasswordConfirmation(): void
    {
        $this->showPasswordConfirm = true;
        $this->password = '';
    }

    public function confirmPassword(): void
    {
        $this->validate([
            'password' => 'required|string',
        ]);

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return;
        }

        if (! Hash::check($this->password, $user->password)) {
            $this->addError('password', __('The provided password is incorrect.'));

            return;
        }

        // Password is correct, check what action to perform
        $pendingAction = session()->get('pending_action');

        if ($pendingAction === 'regenerate_codes') {
            $this->performRegenerateRecoveryCodes();
        } else {
            // Default action is 2FA reset
            $this->disable2FA();
        }
    }

    public function disable2FA(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return;
        }

        $user->saveAppAuthenticationSecret(null);
        $user->saveAppAuthenticationRecoveryCodes(null);

        // Clear remember device cookies
        cookie()->queue(cookie()->forget('two_factor_remember'));

        // Reset state
        $this->showPasswordConfirm = false;
        $this->password = '';

        // Immediately start setup process for new 2FA
        $this->enable2FA();

        session()->flash('status', '2fa-reset');
    }

    public function regenerateRecoveryCodes(): void
    {
        // For recovery codes, we'll also require password confirmation
        $this->showPasswordConfirm = true;
        $this->password = '';

        // Set a flag so we know what action to take after password confirmation
        session()->put('pending_action', 'regenerate_codes');
    }

    private function performRegenerateRecoveryCodes(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return;
        }

        // Generate new recovery codes
        $recoveryCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $recoveryCodes[] = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        }
        $user->saveAppAuthenticationRecoveryCodes($recoveryCodes);

        session()->flash('status', 'recovery-codes-regenerated');
        session()->forget('pending_action');
    }

    public function cancel(): void
    {
        $this->resetState();
    }

    public function render(): View
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            // This shouldn't happen in practice due to auth middleware
            $twoFactorEnabled = false;
            $recoveryCodes = [];
        } else {
            $twoFactorEnabled = ! empty($user->app_authentication_secret);
            $recoveryCodes = $user->app_authentication_recovery_codes ?? [];
        }

        return view('livewire.settings.two-factor-authentication', [
            'twoFactorEnabled' => $twoFactorEnabled,
            'recoveryCodes'    => $recoveryCodes,
        ]);
    }
}
