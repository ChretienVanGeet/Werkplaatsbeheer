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
            autofocus
        />

        <flux:field>
            <livewire:components.select-groups wire:model="groups" />
            <flux:error name="groups" />
        </flux:field>

        <flux:input
            wire:model="phone"
            label="{{ __('Phone') }}"
            type="text"
            name="phone"
        />

        <flux:input
            wire:model="email"
            label="{{ __('Email') }}"
            type="text"
            name="email"
        />

        <flux:input
            wire:model="city"
            label="{{ __('City') }}"
            type="text"
            name="city"
        />

        <flux:editor
            wire:model="comments"
            label="{{ __('Comments') }}"
            name="comments"
        />

        <livewire:components.workflows-panel :model="$participant" />

        <livewire:components.notes-panel :model="$participant" />

        <flux:separator class="my-4" />
        <div class="flex flex-row-reverse gap-3">
            <flux:button size="sm" variant="primary" type="submit">{{ __('Save') }}</flux:button>
            <flux:button size="sm" variant="filled" href="{{ route('participants.index') }}" type="submit">{{ __('Back') }}</flux:button>
        </div>
    </form>
</div>
