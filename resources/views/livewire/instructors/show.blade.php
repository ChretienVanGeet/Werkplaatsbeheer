<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading>{{ $heading }}</flux:heading>
            <flux:text class="mt-1 text-sm text-gray-500">{{ __('Can supervise :count resources', ['count' => $instructor->supportedResources->count()]) }}</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="ghost" href="{{ route('instructors.index') }}">{{ __('Back') }}</flux:button>
            <flux:button size="sm" variant="primary" color="sky" icon="pencil" href="{{ route('instructors.edit', $instructor) }}"></flux:button>
        </div>
    </div>

    <flux:card class="space-y-2">
        <flux:heading size="sm">{{ __('Details') }}</flux:heading>
        <div class="text-sm text-gray-700 dark:text-zinc-100">{{ $instructor->description ?? __('No description') }}</div>
        @if($instructor->supportedResources->isNotEmpty())
            <flux:separator />
            <flux:heading size="xs">{{ __('Resources') }}</flux:heading>
            <div class="flex flex-wrap gap-2">
                @foreach($instructor->supportedResources as $resource)
                    <flux:button size="xs" :href="route('resources.show', $resource)" icon:leading="wrench-screwdriver" icon:trailing="arrow-up-right">
                        {{ $resource->name }}
                    </flux:button>
                @endforeach
            </div>
        @endif
        <flux:separator />
        <flux:heading size="xs">{{ __('Activities') }}</flux:heading>
        @if(!empty($activities))
            <div class="flex flex-wrap gap-2">
                @foreach($activities as $activity)
                    <flux:button size="xs" :href="route('activities.show', $activity['id'])" icon:leading="calendar" icon:trailing="arrow-up-right">
                        {{ $activity['name'] }}
                    </flux:button>
                @endforeach
            </div>
        @else
            <flux:text class="text-sm text-gray-500">{{ __('No active linked activities') }}</flux:text>
        @endif
        @if($instructor->groups->isNotEmpty())
            <flux:separator />
            <flux:heading size="xs">{{ __('Groups') }}</flux:heading>
            <div class="flex flex-wrap gap-2">
                @foreach($instructor->groups as $group)
                    <flux:badge size="sm" color="blue">{{ $group->name }}</flux:badge>
                @endforeach
            </div>
        @endif
    </flux:card>

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <flux:heading size="sm">{{ __('Weekly assignments') }}</flux:heading>
            <div class="flex items-center gap-2">
                <flux:button size="xs" variant="ghost" icon="chevron-left" wire:click="previousWeek">{{ __('Previous') }}</flux:button>
                <flux:button size="xs" variant="ghost" icon="chevron-right" wire:click="nextWeek">{{ __('Next') }}</flux:button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @forelse($weeklySlots as $day)
                <flux:card class="space-y-3">
                    <div class="flex justify-between text-sm font-semibold">
                        <span>{{ $day['label'] }}</span>
                        <span class="text-gray-500">{{ $day['date'] }}</span>
                    </div>
                    <div class="space-y-2">
                        @forelse($day['slots'] as $slot)
                            <div class="space-y-1 rounded border border-gray-100 p-2 dark:border-zinc-800">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-sm font-medium">{{ $slot['start'] }} - {{ $slot['end'] }}</div>
                                    <div class="flex items-center gap-2">
                                        <flux:badge size="sm" color="{{ empty($slot['assignments']) ? 'gray' : 'green' }}">
                                            {{ empty($slot['assignments']) ? __('Unassigned') : __('Assigned') }}
                                        </flux:badge>
                                        <flux:badge size="sm" color="{{ $slot['available'] > 0 ? 'green' : 'rose' }}">
                                            {{ __('Available: :pct%', ['pct' => $slot['available']]) }}
                                        </flux:badge>
                                    </div>
                                </div>
                                @if(empty($slot['assignments']))
                                    <div class="text-xs text-gray-500">{{ __('No assignment') }}</div>
                                @else
                                    <div class="space-y-1">
                                        @foreach($slot['assignments'] as $assignment)
                                            <div class="text-xs text-gray-700 dark:text-zinc-100">
                                                <span class="font-semibold">{{ $assignment['resource'] ?? __('Resource') }}</span>
                                                @if($assignment['activity'])
                                                    <span class="text-gray-500">— {{ $assignment['activity'] }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <flux:text class="text-sm text-gray-500">{{ __('No slots for this week') }}</flux:text>
                        @endforelse
                    </div>
                </flux:card>
            @empty
                <flux:text class="text-sm text-gray-500">{{ __('No slots for this week') }}</flux:text>
            @endforelse
        </div>
    </div>

    <div class="mb-4">
        <livewire:components.workflows-panel :model="$instructor" :read-only="true" />
    </div>
    <div class="mb-4">
        <livewire:components.notes-panel :model="$instructor" :read-only="true" />
    </div>
</div>
