<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="lg">{{ __('Resources') }}</flux:heading>
            <flux:text class="text-sm text-gray-500">{{ __('Overzicht van resources met status en instructeurs') }}</flux:text>
        </div>
        <div class="flex items-center gap-3">
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
        </div>
    </div>

    <div class="filters">
        <flux:callout variant="secondary">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 p-1">
                    <flux:icon name="funnel" variant="solid" class="w-4 h-4 text-gray-500 dark:text-zinc-50" />
                    <h3 class="text-sm font-medium text-gray-700 dark:text-zinc-50">{{ __('Filters') }}</h3>
                </div>
                <div class="w-px h-4 bg-gray-300"></div>
                <div class="flex items-center gap-3">
                    <flux:label class="text-sm shrink-0"><small>{{ __('Status') }}</small></flux:label>
                    <flux:select variant="listbox" wire:model.live="statusFilter" size="sm" class="!w-40">
                        <flux:select.option value=""><flux:badge size="sm" color="grey">{{ __('All') }}</flux:badge></flux:select.option>
                        @foreach ($resourceStatuses as $resourceStatus)
                            <flux:select.option value="{{ $resourceStatus->value }}" wire:key="{{ $resourceStatus->value }}">
                                <flux:badge size="sm" :color="$resourceStatus->badgeColor()">{{ $resourceStatus->getLabel() }}</flux:badge>
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="w-px h-4 bg-gray-300"></div>
                <div class="flex items-center gap-3">
                    <flux:label class="text-sm shrink-0"><small>{{ __('Periode') }}</small></flux:label>
                    <div class="flex items-center gap-2">
                        <flux:input type="date" size="sm" wire:model.live="periodStart" class="!w-36" />
                        <span class="text-xs text-gray-500 dark:text-zinc-200">t/m</span>
                        <flux:input type="date" size="sm" wire:model.live="periodEnd" class="!w-36" />
                    </div>
                </div>
            </div>
        </flux:callout>
    </div>

    <flux:table :paginate="$resources">
        <flux:table.columns>
            <flux:table.column class="w-16 whitespace-nowrap">{{ __('ID') }}</flux:table.column>
            <flux:table.column class="text-left">{{ __('Name') }}</flux:table.column>
            <flux:table.column class="text-right">{{ __('Status') }}</flux:table.column>
            <flux:table.column class="text-right">{{ __('Instructor') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse($resources as $resource)
                <flux:table.row>
                    <flux:table.cell class="w-16 whitespace-nowrap">
                        {{ $resource['id'] }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <flux:button size="xs" :href="route('resources.show', $resource['id'])" icon:trailing="arrow-up-right">
                                <flux:icon variant="micro" name="wrench-screwdriver" class="inline-block" />
                                {{ $resource['name'] }}
                            </flux:button>
                            <flux:modal.trigger name="resource-week-modal">
                                <flux:button size="xs" variant="ghost" icon="calendar" wire:click="showResourceSchedule({{ $resource['id'] }})">
                                    {{ __('Week') }}
                                </flux:button>
                            </flux:modal.trigger>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex justify-start flex-wrap gap-1">
                            @foreach($resource['statuses'] as $status)
                                <flux:badge size="sm" :color="$status->badgeColor()">{{ $status->getLabel() }}</flux:badge>
                            @endforeach
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        @if(!empty($resource['instructors']))
                            <div class="flex justify-start flex-wrap gap-2">
                                @foreach($resource['instructors'] as $instructor)
                                    <flux:button size="xs" :href="route('instructors.show', $instructor['id'])" icon:trailing="arrow-up-right">
                                        <flux:icon variant="micro" name="academic-cap" class="inline-block" />
                                        {{ $instructor['name'] }}
                                    </flux:button>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs text-gray-500">{{ __('No instructor') }}</span>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">
                        <flux:text class="text-sm text-gray-500">{{ __('No resources found.') }}</flux:text>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="resource-week-modal" flyout variant="floating">
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
                                            @if(in_array($statusEnum, [\App\Enums\ResourceStatus::RESERVED, \App\Enums\ResourceStatus::OCCUPIED], true) && empty($slot['assignments']))
                                                <flux:icon name="exclamation-triangle" variant="micro" class="w-3 h-3 text-rose-600" />
                                            @endif
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
