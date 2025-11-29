<div>
    <flux:heading>{{ $heading }}</flux:heading>
    <flux:text class="mt-2">{{ $subHeading }}</flux:text>
    <flux:separator class="my-4" />

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:input
            wire:model="name"
            label="{{ __('Name') }}"
            type="text"
            name="name"
            autofocus
        />

        <flux:textarea
            wire:model="description"
            label="{{ __('Description') }}"
            name="description"
        />

        <flux:field>
            <livewire:components.select-groups wire:model="groups" />
            <flux:error name="groups" />
        </flux:field>

        <livewire:components.select-items-modal
            :title="__('Resources this instructor can supervise')"
            :addLabel="__('Attach resources')"
            modal-id="select-resources"
            :modal-title="__('Select resources')"
            :modal-sub-title="__('Select items below to add.')"
            :existingItems="$resources"
            :className="\App\Models\Resource::class"
        />

        <livewire:components.workflows-panel :model="$instructor" />
        <livewire:components.notes-panel :model="$instructor" />

        <flux:separator class="my-4" />
        <div class="flex flex-row-reverse gap-3">
            <flux:button size="sm" variant="primary" type="submit">{{ __('Save') }}</flux:button>
            <flux:button size="sm" variant="filled" href="{{ route('instructors.index') }}" type="submit">{{ __('Back') }}</flux:button>
        </div>
    </form>
</div>
