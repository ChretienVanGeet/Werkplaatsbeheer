<div>
    <flux:heading>{{ $heading }}</flux:heading>
    <flux:text class="mt-2">{{ $subHeading  }}</flux:text>
    <flux:separator class="my-4" />

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:input
            wire:model="name"
            label="{{ __('Name') }}"
            type="text"
            name="name"
            required
            autofocus
        />

        <flux:textarea
            wire:model="description"
            label="{{ __('Description') }}"
            name="description"
        />

        <flux:separator class="my-4" />
        <div class="flex flex-row-reverse gap-3">
            <flux:button size="sm" variant="primary" type="submit">{{ __('Save') }}</flux:button>
            <flux:button size="sm" variant="filled" href="{{ route('groups.index') }}" type="submit">{{ __('Back') }}</flux:button>
        </div>
    </form>
</div>
