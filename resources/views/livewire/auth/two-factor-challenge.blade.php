<div class="flex flex-col gap-6">
    <x-auth-header
        title="{{ __('Two-Factor Authentication') }}"
        description="{{ __('Enter your authentication code to continue') }}"
    />

    <form wire:submit.prevent="submit" class="flex flex-col gap-6">
        <flux:input
            wire:model.defer="code"
            label="{{ __('Authenticator code') }}"
            type="text"
            inputmode="numeric"
            autocomplete="one-time-code"
            placeholder="{{ __('Enter 6-digit code') }}"
            maxlength="6"
            pattern="[0-9]{6}"
            :required="!$showRecoveryCode"
        />

        @if($showRecoveryCode)
            <flux:input
                wire:model.defer="recovery_code"
                label="{{ __('Recovery code') }}"
                type="text"
                placeholder="{{ __('Enter recovery code') }}"
                required
            />
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400 -mt-4">
                {{ __('Use this if you don\'t have access to your authenticator app') }}
            </flux:text>
        @endif

        <div class="text-center">
            <flux:button
                wire:click="toggleRecoveryCode"
                variant="ghost"
                size="sm"
                type="button"
            >
                {{ $showRecoveryCode ? __('Hide recovery code') : __('Use recovery code instead') }}
            </flux:button>
        </div>

        <flux:checkbox
            wire:model="remember"
            label="{{ __('Remember this device for 30 days') }}"
        />

        <flux:button type="submit" variant="primary" class="w-full">
            {{ __('Verify') }}
        </flux:button>
    </form>
</div>
