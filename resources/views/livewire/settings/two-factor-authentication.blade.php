<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout
        :heading="__('Two-Factor Authentication')"
        :subheading="__('Add additional security to your account using two-factor authentication.')"
    >
        <!-- Status Messages -->
        @if (session('status') === '2fa-enabled')
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                <flux:text class="text-green-800 dark:text-green-200">
                    {{ __('Two-factor authentication has been enabled.') }}
                </flux:text>
            </div>
        @endif

        @if (session('status') === '2fa-disabled')
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                <flux:text class="text-red-800 dark:text-red-200">
                    {{ __('Two-factor authentication has been disabled.') }}
                </flux:text>
            </div>
        @endif

        @if (session('status') === 'recovery-codes-regenerated')
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                <flux:text class="text-green-800 dark:text-green-200">
                    {{ __('Recovery codes have been regenerated.') }}
                </flux:text>
            </div>
        @endif

        @if (session('status') === '2fa-reset')
            <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                <flux:text class="text-blue-800 dark:text-blue-200">
                    {{ __('Two-factor authentication has been reset. Please set up a new authenticator.') }}
                </flux:text>
            </div>
        @endif

        <div class="mt-6 space-y-6">
            @if ($twoFactorEnabled && !$confirming)
                <!-- 2FA is Enabled -->
                <div class="rounded-lg border border-green-200 bg-green-50 p-6 dark:border-green-800 dark:bg-green-900/20">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <flux:heading size="base" class="text-green-800 dark:text-green-200">
                                {{ __('Two-factor authentication is enabled') }}
                            </flux:heading>
                            <flux:text size="sm" class="text-green-700 dark:text-green-300 mt-1">
                                {{ __('Your account is protected with two-factor authentication.') }}
                            </flux:text>
                        </div>
                    </div>
                </div>

                <!-- Recovery Codes -->
                @if (count($recoveryCodes) > 0)
                    <div class="space-y-4">
                        <div>
                            <flux:heading size="sm">{{ __('Recovery Codes') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400 mt-1">
                                {{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.') }}
                            </flux:text>
                        </div>

                        <div class="max-w-xl grid grid-cols-2 gap-2">
                            @foreach ($recoveryCodes as $code)
                                <div class="bg-white dark:bg-gray-800 px-3 py-2 rounded border font-mono text-sm text-center">
                                    {{ $code }}
                                </div>
                            @endforeach
                        </div>

                        <flux:button wire:click="regenerateRecoveryCodes" variant="outline" size="sm">
                            {{ __('Regenerate Recovery Codes') }}
                        </flux:button>
                    </div>
                @endif

                <!-- Reset 2FA -->
                <div class="pt-4">
                    @if($showPasswordConfirm)
                        @php
                            $isRegenerateAction = session('pending_action') === 'regenerate_codes';
                        @endphp

                        <div class="space-y-4 p-4 border rounded-lg {{ $isRegenerateAction ? 'border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-900/20' : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' }}">
                            <div>
                                <flux:heading size="sm" class="{{ $isRegenerateAction ? 'text-orange-800 dark:text-orange-200' : 'text-red-800 dark:text-red-200' }}">
                                    @if($isRegenerateAction)
                                        {{ __('Confirm Password to Regenerate Recovery Codes') }}
                                    @else
                                        {{ __('Confirm Password to Reset 2FA') }}
                                    @endif
                                </flux:heading>
                                <flux:text size="sm" class="{{ $isRegenerateAction ? 'text-orange-700 dark:text-orange-300' : 'text-red-700 dark:text-red-300' }} mt-1">
                                    {{ __('Please enter your current password to confirm this action.') }}
                                </flux:text>
                            </div>

                            <form wire:submit="confirmPassword" class="space-y-4">
                                <flux:input
                                    wire:model="password"
                                    label="{{ __('Current Password') }}"
                                    type="password"
                                    placeholder="{{ __('Enter your current password') }}"
                                    required
                                />

                                <div class="flex items-center gap-4">
                                    @if($isRegenerateAction)
                                        <flux:button type="submit" variant="primary">
                                            {{ __('Confirm Regenerate') }}
                                        </flux:button>
                                    @else
                                        <flux:button type="submit" variant="danger">
                                            {{ __('Confirm Reset') }}
                                        </flux:button>
                                    @endif

                                    <flux:button wire:click="resetState" variant="outline" type="button">
                                        {{ __('Cancel') }}
                                    </flux:button>
                                </div>
                            </form>
                        </div>
                    @else
                        <flux:button
                            wire:click="showPasswordConfirmation"
                            variant="danger"
                        >
                            {{ __('Reset Two-Factor Authentication') }}
                        </flux:button>
                    @endif
                </div>

            @elseif ($confirming)
                <!-- Setting up 2FA -->
                <div class="space-y-6">
                    <div>
                        <flux:heading size="sm">{{ __('Scan QR Code') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400 mt-1">
                            {{ __('Scan the following QR code using your phone\'s authenticator application.') }}
                        </flux:text>
                    </div>

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

                    <form wire:submit="confirmEnable" class="space-y-4">
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

                        <div class="flex items-center gap-4">
                            <flux:button type="submit" variant="primary">
                                {{ __('Enable') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="outline" type="button">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>

            @else
                <!-- 2FA is Disabled -->
                <div class="space-y-4">
                    <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-6 dark:border-yellow-800 dark:bg-yellow-900/20">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <flux:heading size="base" class="text-yellow-800 dark:text-yellow-200">
                                    {{ __('Two-factor authentication is not enabled') }}
                                </flux:heading>
                                <flux:text size="sm" class="text-yellow-700 dark:text-yellow-300 mt-1">
                                    {{ __('Add additional security to your account by enabling two-factor authentication.') }}
                                </flux:text>
                            </div>
                        </div>
                    </div>

                    <flux:button wire:click="enable2FA" variant="primary">
                        {{ __('Enable Two-Factor Authentication') }}
                    </flux:button>
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>