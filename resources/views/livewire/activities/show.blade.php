<div>
    <div class="flex justify-between">
        <div>
            <flux:heading>{{ $heading }}</flux:heading>
            <flux:text class="mt-0">{{ $subHeading  }}</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="ghost" href="{{ route('activities.index') }}">{{ __('Back') }}</flux:button>
            @can('write')
                @if(!empty($editRoute))
                    <flux:button size="sm" :href="$editRoute" variant="primary" color="sky" icon="pencil"></flux:button>
                @endif
            @endcan
        </div>
    </div>
    <flux:separator class="mt-4 mb-1" />

    <livewire:components.show-groups :groups="$activity->groups" />

    <div class="mb-4">
        <flux:heading>{{ __('Name') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{{ $activity->name }}</flux:text>
        <flux:heading>{{ __('Start') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{{ $activity->start_date }}</flux:text>
        <flux:heading>{{ __('End') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{{ $activity->end_date }}</flux:text>
        <flux:heading>{{ __('Status') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{!! $activity->status->getLabel() !!}</flux:text>
    </div>
    <div class="mb-4">
        <livewire:components.select-items-modal
            :title="__('Companies')"
            :addLabel="__('Attach companies')"
            modal-id="select-companies"
            :modal-title="__('Select companies')"
            :modal-sub-title="__('Select items below to add.')"
            :existingItems="$companies"
            :className="\App\Models\Company::class"
            :view-only="true"
        />
    </div>
    <div class="mb-4">
        <livewire:components.select-items-modal
            :title="__('Participants')"
            :addLabel="__('Attach participants')"
            modal-id="select-participants"
            :modal-title="__('Select participants')"
            :modal-sub-title="__('Select items below to add.')"
            :existingItems="$participants"
            :className="\App\Models\Participant::class"
            :view-only="true"
        />
    </div>
    <div class="mb-4">
        <livewire:components.select-items-modal
            :title="__('Resources')"
            :addLabel="__('Attach resources')"
            modal-id="select-resources"
            :modal-title="__('Select resources')"
            :modal-sub-title="__('Select items below to add.')"
            :existingItems="$resources"
            :className="\App\Models\Resource::class"
            :view-only="true"
        />
    </div>

    <div class="mb-4">
        <livewire:components.workflows-panel :model="$activity" :read-only="true" />
    </div>
    <div class="mb-4">
        <livewire:components.notes-panel :model="$activity" :read-only="true" />
    </div>
    <flux:separator class="my-4" />
</div>
