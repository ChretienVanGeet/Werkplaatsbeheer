<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('Resources') }}</flux:heading>
            <flux:text class="text-sm text-gray-500">{{ __('Activities with their assigned resources') }}</flux:text>
        </div>
        <div class="flex items-center gap-3">
            <flux:label class="text-sm shrink-0"><small>{{ __('Status') }}</small></flux:label>
            <flux:select variant="listbox" wire:model.live="statusFilter" size="sm" class="!w-40">
                <flux:select.option value=""><flux:badge size="sm" color="grey">{{ __('All') }}</flux:badge></flux:select.option>
                @foreach ($activityStatuses as $activityStatus)
                    <flux:select.option value="{{ $activityStatus->value }}" wire:key="{{ $activityStatus->value }}">
                        <flux:badge size="sm" :color="$activityStatus->badgeColor()">{{ $activityStatus->getLabel() }}</flux:badge>
                    </flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Activity') }}</flux:table.column>
            <flux:table.column class="text-right">{{ __('Status') }}</flux:table.column>
            <flux:table.column class="text-right">{{ __('Resources') }}</flux:table.column>
            <flux:table.column class="text-right">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($activities as $activity)
                <flux:table.row>
                    <flux:table.cell class="align-top">
                        <span class="font-semibold">{{ $activity['name'] }}</span>
                    </flux:table.cell>
                    <flux:table.cell class="text-right align-top">
                        <flux:badge size="sm" :color="$activity['status']->badgeColor()">{{ $activity['status']->getLabel() }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-right align-top">
                        <flux:accordion>
                            <flux:accordion.item>
                                <flux:accordion.heading>
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="text-xs text-gray-500">{{ count($activity['resources']) }} {{ __('resources') }}</span>
                                    </div>
                                </flux:accordion.heading>
                                <flux:accordion.content>
                                    @if(empty($activity['resources']))
                                        <flux:text class="text-sm text-gray-500">{{ __('No resources linked') }}</flux:text>
                                    @else
                                        <div class="divide-y divide-gray-100 dark:divide-zinc-800">
                                            @foreach($activity['resources'] as $resource)
                                                <div class="flex items-center justify-between py-2 gap-2">
                                                    <div class="flex items-center gap-2">
                                                        <flux:icon name="rectangle-group" variant="micro" class="w-4 h-4 text-gray-400" />
                                                        <div class="text-sm font-medium">{{ $resource['name'] }}</div>
                                                    </div>
                                                    <flux:modal.trigger name="resource-week-modal">
                                                        <flux:button size="xs" variant="ghost" icon="calendar" wire:click="showResourceSchedule({{ $resource['id'] }})">
                                                            {{ __('View week') }}
                                                        </flux:button>
                                                    </flux:modal.trigger>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </flux:accordion.content>
                            </flux:accordion.item>
                        </flux:accordion>
                    </flux:table.cell>
                    <flux:table.cell class="text-right align-top text-xs text-gray-500">
                        {{ __('Expand') }}
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">
                        <flux:text class="text-sm text-gray-500">{{ __('No activities found.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="resource-week-modal">
        <div class="space-y-2 text-[9px]">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="sm" class="text-[10px]">{{ $selectedResourceName ?: __('Resource') }}</flux:heading>
                <div class="flex-1 flex justify-center items-center gap-1">
                    <flux:button size="xs" variant="ghost" icon="chevron-left" wire:click="previousWeek">{{ __('Previous') }}</flux:button>
                    <flux:text class="text-[8px] text-gray-500">{{ $weekStart }}</flux:text>
                    <flux:button size="xs" variant="ghost" icon="chevron-right" wire:click="nextWeek">{{ __('Next') }}</flux:button>
                </div>
            </div>
            @if(empty($resourceWeek))
                <flux:text class="text-[8px] text-gray-500">{{ __('Select a resource to view its schedule.') }}</flux:text>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-1.5">
                    @foreach($resourceWeek as $day)
                        <flux:card class="space-y-1 text-[8px] px-2 py-2">
                            <div class="flex items-center justify-between font-semibold">
                                <span>{{ $day['label'] }}</span>
                            </div>
                            <div class="space-y-0.5">
                                @forelse($day['slots'] as $slot)
                                    @php $statusEnum = \App\Enums\ResourceStatus::from($slot['status']); @endphp
                                    <div class="flex flex-col gap-0.25">
                                        <flux:text class="text-xs font-semibold">{{ $slot['start'] }}</flux:text>
                                        <div class="flex items-center gap-2 pl-3">
                                            <flux:badge size="xs" class="text-[8px]" :color="$statusEnum->badgeColor()">{{ $statusEnum->getLabel() }}</flux:badge>
                                        </div>
                                    </div>
                                @empty
                                    <flux:text class="text-[8px] text-gray-500">{{ __('No slots') }}</flux:text>
                                @endforelse
                                <flux:text class="text-xs font-semibold">{{ __('22:00') }}</flux:text>
                            </div>
                        </flux:card>
                    @endforeach
                </div>
            @endif
        </div>
    </flux:modal>
</div>
