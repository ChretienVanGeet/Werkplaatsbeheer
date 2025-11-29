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
                                @php
                                    $slotBg = 'bg-gray-50/50 dark:bg-zinc-800/30';
                                    if (!empty($slot['assignments'])) {
                                        $slotBg = $slot['available'] > 0 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-rose-50 dark:bg-rose-900/20';
                                    }
                                @endphp
                                <div class="space-y-2 rounded border border-gray-100 p-2 dark:border-zinc-800 {{ $slotBg }}">
                                    <div class="flex items-center justify-between gap-2">
        <div class="text-sm font-medium">{{ $slot['start'] }} - {{ $slot['end'] }}</div>
                                        <div class="flex items-center gap-2">
                                            @if(empty($slot['assignments']))
                                                <flux:badge size="sm" color="gray">
                                                    {{ __('Unassigned') }}
                                                </flux:badge>
                                            @else
                                                <flux:badge size="sm" color="green">
                                                    {{ __('Assigned: :pct%', ['pct' => $slot['total_load']]) }}
                                                </flux:badge>
                                            @endif
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
                                                <div class="flex items-center justify-between text-xs">
                                                    <div class="flex flex-col gap-0.5">
                                                        <div class="flex items-center gap-2">
                                                            @if(!empty($assignment['resource_id']))
                                                                <flux:button size="xs" :href="route('resources.show', $assignment['resource_id'])" icon:leading="wrench-screwdriver" icon:trailing="arrow-up-right">
                                                                    {{ $assignment['resource'] ?? __('Resource') }}
                                                                </flux:button>
                                                            @else
                                                                <span class="font-semibold">{{ $assignment['resource'] ?? __('Resource') }}</span>
                                                            @endif
                                                            <flux:badge size="xs" color="{{ $assignment['load_percentage'] >= 100 ? 'green' : 'yellow' }}">
                                                                {{ $assignment['load_percentage'] }}%
                                                            </flux:badge>
                                                        </div>
                                                        @if(!empty($assignment['activity']))
                                                            @if(!empty($assignment['activity_id']))
                                                                <flux:button size="xs" variant="ghost" :href="route('activities.show', $assignment['activity_id'])" icon:leading="calendar" icon:trailing="arrow-up-right">
                                                                    {{ $assignment['activity'] }}
                                                                </flux:button>
                                                            @else
                                                                <span class="text-gray-500">{{ $assignment['activity'] }}</span>
                                                            @endif
                                                        @endif
                                                    </div>
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
