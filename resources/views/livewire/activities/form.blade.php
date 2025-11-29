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

        <flux:calendar week-numbers mode="range" wire:model="range" />

        <flux:field>
            <flux:label>{{ __('Status') }}</flux:label>
            <flux:select variant="listbox" wire:model="status">
                @foreach ($this->activityStatuses as $activityStatus)
                    <flux:select.option value="{{ $activityStatus->value }}" wire:key="{{ $activityStatus->value }}">
                        {{ $activityStatus->getLabel() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="status" />
        </flux:field>

        <livewire:components.select-items-modal
            :title="__('Companies')"
            :addLabel="__('Attach companies')"
            modal-id="select-companies"
            :modal-title="__('Select companies')"
            :modal-sub-title="__('Select items below to add.')"
            :existingItems="$companies"
            :className="\App\Models\Company::class"
        />

        <livewire:components.select-items-modal
            :title="__('Participants')"
            :addLabel="__('Attach participants')"
            modal-id="select-participants"
            :modal-title="__('Select participants')"
            :modal-sub-title="__('Select items below to add.')"
            :existingItems="$participants"
            :className="\App\Models\Participant::class"
        />

        <livewire:components.select-items-modal
            :title="__('Resources')"
            :addLabel="__('Attach resources')"
            modal-id="select-resources"
            :modal-title="__('Select resources')"
            :modal-sub-title="__('Select items below to add.')"
            :existingItems="$resources"
            :className="\App\Models\Resource::class"
        />

        <livewire:components.workflows-panel :model="$activity" />

        <livewire:components.notes-panel :model="$activity" />

        <flux:separator class="my-4" />
        <div class="flex flex-row-reverse gap-3">
            <flux:button size="sm" variant="primary" type="submit">{{ __('Save') }}</flux:button>
            <flux:button size="sm" variant="filled" href="{{ route('activities.index') }}" type="submit">{{ __('Back') }}</flux:button>
        </div>
    </form>
</div>
