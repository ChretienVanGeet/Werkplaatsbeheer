<div>
    <div class="flex justify-between">
        <div>
            <flux:heading>{{ $heading }}</flux:heading>
            <flux:text class="mt-0">{{ $subHeading  }}</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="ghost" href="{{ route('participants.index') }}">{{ __('Back') }}</flux:button>
            @can('write')
                @if(!empty($editRoute))
                    <flux:button size="sm" :href="$editRoute" variant="primary" color="sky" icon="pencil"></flux:button>
                @endif
            @endcan
        </div>
    </div>
    <flux:separator class="mt-4 mb-1" />
    <livewire:components.show-groups :groups="$participant->groups" />

    <div class="mb-4">
        <flux:heading>{{ __('Name') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{{ $participant->name }}</flux:text>
        <flux:heading>{{ __('Phone') }}</flux:heading>
        <flux:text class="mt-1 mb-2 html-content"><a href="tel:{{ $participant->phone }}">{{ $participant->phone }}</a></flux:text>
        <flux:heading>{{ __('Email') }}</flux:heading>
        <flux:text class="mt-1 mb-2 html-content"><a href="tel:{{ $participant->email }}">{{$participant->email }}</a></flux:text>
        <flux:heading>{{ __('City') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{{ $participant->city }}</flux:text>
        <flux:heading>{{ __('Comments') }}</flux:heading>
        <flux:text class="mt-1 mb-2">{!! $participant->comments !!}</flux:text>
    </div>

    <div class="mb-4">
        <livewire:components.workflows-panel :model="$participant" :read-only="true" />
    </div>
    <div class="mb-4">
        <livewire:components.notes-panel :model="$participant" :read-only="true" />
    </div>
    <flux:separator class="my-4" />
</div>
