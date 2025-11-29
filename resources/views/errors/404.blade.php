<x-layouts.auth.simple title="Pagina niet gevonden">
    <div class="text-center">
        <div class="mb-8">
            <flux:heading size="xl" class="mb-4">{{ __('Page not found') }}</flux:heading>

            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('The page you are trying to access does not exist or you no longer have access to this information. This can happen when, for example, a group has been removed or your access rights have been changed.') }}
            </flux:text>
        </div>
        <div>
            <flux:button
                variant="primary"
                href="{{ route('dashboard') }}"
                icon="home"
            >
                {{ __('Go to dashboard') }}
            </flux:button>
        </div>
    </div>
</x-layouts.auth.simple>
