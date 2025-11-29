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

        <flux:input
            wire:model="machineType"
            label="{{ __('Machine type') }}"
            type="text"
            name="machineType"
        />

        <flux:input
            wire:model="instructorCapacity"
            label="{{ __('Instructor load (%)') }}"
            type="number"
            min="1"
            max="100"
            name="instructorCapacity"
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
            :title="__('Activities')"
            :addLabel="__('Attach activities')"
            modal-id="select-activities"
            :modal-title="__('Select activities')"
            :modal-sub-title="__('Select items below to add.')"
            :existingItems="$activities"
            :className="\App\Models\Activity::class"
        />

        <livewire:components.workflows-panel :model="$resource" />
        <livewire:components.notes-panel :model="$resource" />

        <flux:separator class="my-4" />
        <div class="flex flex-row-reverse gap-3">
            <flux:button size="sm" variant="primary" type="submit">{{ __('Save') }}</flux:button>
            <flux:button size="sm" variant="filled" href="{{ route('resources.index') }}" type="submit">{{ __('Back') }}</flux:button>
        </div>
    </form>
</div>
