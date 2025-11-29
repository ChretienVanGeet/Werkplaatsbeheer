<div class="flex flex-col gap-6">
    @if(!$setupComplete)
        <x-auth-header
            title="{{ __('Two-Factor Authentication Setup') }}"
            description="{{ __('Two-factor authentication is required for all users. Please scan the QR code with your authenticator app.') }}"
        />

        <div class="flex justify-center">
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border">
                <img src="{{ $qrCode }}" alt="QR Code" class="mx-auto">
            </div>
        </div>

        <div class="text-center">
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('Manual entry key:') }} <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded font-mono text-xs">{{ $secret }}</code>
            </flux:text>
        </div>

        <form wire:submit="confirmSetup" class="flex flex-col gap-6">
            <flux:input
                wire:model="confirmationCode"
                label="{{ __('Enter verification code') }}"
                type="text"
                placeholder="{{ __('Enter 6-digit code') }}"
                maxlength="6"
                pattern="[0-9]{6}"
                inputmode="numeric"
                required
            />

            <flux:checkbox
                wire:model="remember"
                label="{{ __('Remember this device for 30 days') }}"
            />

            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Verify & Enable 2FA') }}
            </flux:button>
        </form>
    @else
        <x-auth-header
            title="{{ __('Two-Factor Authentication Enabled') }}"
            description="{{ __('Your account is now secured with two-factor authentication.') }}"
        />

        <div class="text-center">
            <div class="text-green-600 dark:text-green-400 mb-6">
                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <flux:text size="sm" class="text-yellow-800 dark:text-yellow-200">
                <strong>{{ __('Important:') }}</strong> {{ __('Save these recovery codes in a safe place. You can use them to access your account if you lose access to your authenticator app.') }}
            </flux:text>

            @if(auth()->user()->app_authentication_recovery_codes)
                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach(auth()->user()->app_authentication_recovery_codes as $code)
                        <div class="bg-white dark:bg-gray-800 px-2 py-1 rounded border font-mono text-xs text-center">{{ $code }}</div>
                    @endforeach
                </div>
            @endif
        </div>

        <flux:button wire:click="continueToApplication" variant="primary" class="w-full">
            {{ __('Continue to Application') }}
        </flux:button>
    @endif
</div>