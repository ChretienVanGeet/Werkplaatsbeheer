<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading>{{ $resource->name }}</flux:heading>
            <flux:text class="mt-1 text-sm text-gray-500">{{ $resource->machine_type }}</flux:text>
        </div>
        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="ghost" href="{{ route('resources.index') }}">{{ __('Back') }}</flux:button>
            <flux:button size="sm" variant="primary" color="sky" icon="pencil" href="{{ route('resources.edit', $resource) }}"></flux:button>
        </div>
    </div>

    <flux:card class="space-y-2">
        <flux:heading size="sm">{{ __('Details') }}</flux:heading>
        <div class="text-sm text-gray-700 dark:text-zinc-100">{{ $resource->description ?? __('No description') }}</div>
        <div class="text-sm text-gray-700 dark:text-zinc-100">
            <span class="font-semibold">{{ __('Instructor load') }}:</span>
            <span>{{ $resource->instructor_capacity }}%</span>
        </div>
        @if($resource->activities->isNotEmpty())
            <flux:separator />
            <flux:heading size="xs">{{ __('Activities') }}</flux:heading>
            <div class="flex flex-wrap gap-2">
                @foreach($resource->activities as $activity)
                    <a href="{{ route('activities.show', $activity) }}" class="inline-flex">
                        <flux:badge size="sm" color="blue">{{ $activity->name }}</flux:badge>
                    </a>
                @endforeach
            </div>
        @endif
    </flux:card>

    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="sm">{{ __('Schedule & coverage') }}</flux:heading>
                <flux:text class="text-sm text-gray-500">{{ __('Resource status with instructor load per slot') }}</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:button size="xs" variant="ghost" icon="chevron-left" wire:click="previousWeek">{{ __('Previous') }}</flux:button>
                <flux:button size="xs" variant="ghost" icon="chevron-right" wire:click="nextWeek">{{ __('Next') }}</flux:button>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @foreach($weeklySlots as $day)
                <flux:card class="space-y-3">
                    <div class="flex justify-between text-sm font-semibold">
                        <span>{{ $day['label'] }}</span>
                        <span class="text-gray-500">{{ $day['date'] }}</span>
                    </div>
                    <div class="space-y-2">
                        @forelse($day['slots'] as $slot)
                            @php $statusEnum = \App\Enums\ResourceStatus::from($slot['status']); @endphp
                            <div class="space-y-2 rounded border border-gray-100 p-2 dark:border-zinc-800 @if($slot['uncovered']) bg-rose-50 dark:bg-rose-900/20 @endif">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-sm font-medium">{{ $slot['start'] }} - {{ $slot['end'] }}</div>
                                    <div class="flex items-center gap-2">
                                        <flux:badge size="sm" :color="$statusEnum->badgeColor()">
                                            {{ $statusEnum->getLabel() }}
                                        </flux:badge>
                                        <flux:badge size="sm" color="{{ $slot['total_load'] === 0 ? 'gray' : ($slot['uncovered'] ? 'rose' : 'green') }}">{{ $slot['total_load'] }}%</flux:badge>
                                        <flux:button size="xs" variant="ghost" icon="arrow-path" wire:click="toggleSlotStatus('{{ $slot['start_raw'] }}')"></flux:button>
                                    </div>
                                </div>
                                @if($slot['activity_name'])
                                    <div class="text-xs text-gray-500">{{ $slot['activity_name'] }}</div>
                                @endif
                                @if(empty($slot['assignments']) && $slot['uncovered'])
                                    <div class="text-xs text-rose-600 dark:text-rose-200">{{ __('Not covered by an instructor') }}</div>
                                @else
                                    <div class="space-y-1">
                                        @foreach($slot['assignments'] as $assignment)
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex flex-col gap-0.5">
                                                    <span class="font-semibold">{{ $assignment['name'] ?? __('Instructor') }}</span>
                                                    @if($assignment['activity_name'])
                                                        <span class="text-gray-500">{{ $assignment['activity_name'] }}</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <flux:button size="xs" variant="danger" icon="trash" wire:click="removeInstructorAssignment({{ $assignment['id'] }})"></flux:button>
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
            @endforeach
        </div>
    </div>

    <flux:card class="space-y-4">
        <flux:heading size="sm">{{ __('Update slot status') }}</flux:heading>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input type="datetime-local" wire:model="rangeStart" label="{{ __('Start') }}" name="rangeStart"/>
            <flux:input type="datetime-local" wire:model="rangeEnd" label="{{ __('End') }}" name="rangeEnd"/>
            <flux:select wire:model="status" label="{{ __('Status') }}">
                <flux:select.option value="">{{ __('None') }}</flux:select.option>
                @foreach ($statusOptions as $statusOption)
                    <flux:select.option value="{{ $statusOption->value }}">
                        <div class="flex items-center gap-2">
                            <flux:badge size="xs" :color="$statusOption->badgeColor()">{{ $statusOption->getLabel() }}</flux:badge>
                            <span>{{ $statusOption->getLabel() }}</span>
                        </div>
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model="activityId" label="{{ __('Activity (optional)') }}">
                <flux:select.option value="">{{ __('None') }}</flux:select.option>
                @foreach ($activities as $activity)
                    <flux:select.option value="{{ $activity['id'] }}">{{ $activity['name'] }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:checkbox wire:model="confirmOverride" label="{{ __('Overwrite existing slots') }}" />
        </div>
        <div class="flex items-center gap-3">
            <flux:button variant="primary" wire:click="saveStatus">{{ __('Save status') }}</flux:button>
            <flux:button variant="ghost" wire:click="checkAvailability">{{ __('Check status') }}</flux:button>
        </div>

        @if(!empty($rangeSlots))
            <flux:separator />
            <flux:heading size="xs" class="text-gray-600">{{ __('Result for selected range') }}</flux:heading>
            @if($rangeMatchesSelection === true)
                <div class="flex items-center gap-2 text-sm">
                    <flux:badge color="green" size="sm">
                        {{ __('Matches selected status') }}
                    </flux:badge>
                </div>
            @endif
            <div class="space-y-2">
                @foreach($rangeSlots as $slot)
                    @php $statusEnum = \App\Enums\ResourceStatus::from($slot['status']); @endphp
                    <div class="flex items-center justify-between">
                        <div class="text-sm">{{ $slot['start'] }} - {{ $slot['end'] }}</div>
                        <div class="flex items-center gap-2">
                            @if($slot['activity_name'])
                                @if($slot['activity_id'])
                                    <a href="{{ route('activities.show', $slot['activity_id']) }}" class="text-xs text-blue-600 hover:underline">
                                        {{ $slot['activity_name'] }}
                                    </a>
                                @else
                                    <span class="text-xs text-gray-500">{{ $slot['activity_name'] }}</span>
                                @endif
                            @endif
                            <flux:badge size="sm" :color="$statusEnum->badgeColor()">{{ $statusEnum->getLabel() }}</flux:badge>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>

    <flux:card class="space-y-4">
        <flux:heading size="sm">{{ __('Plan instructors') }}</flux:heading>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:select wire:model="instructorId" label="{{ __('Instructor') }}">
                <flux:select.option value="">{{ __('Select instructor') }}</flux:select.option>
                @foreach ($instructors as $instructor)
                    <flux:select.option value="{{ $instructor['id'] }}">{{ $instructor['name'] }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model="instructorActivityId" label="{{ __('Activity (optional)') }}">
                <flux:select.option value="">{{ __('None') }}</flux:select.option>
                @foreach ($activities as $activity)
                    <flux:select.option value="{{ $activity['id'] }}">{{ $activity['name'] }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input type="datetime-local" wire:model="instructorRangeStart" label="{{ __('Start') }}" name="instructorRangeStart"/>
            <flux:input type="datetime-local" wire:model="instructorRangeEnd" label="{{ __('End') }}" name="instructorRangeEnd"/>
            <flux:checkbox wire:model="forceInstructorOverride" label="{{ __('Overwrite existing slots for this instructor') }}" />
            <flux:checkbox wire:model="confirmUnscheduleAll" label="{{ __('Confirm unschedule all activities in range') }}" />
        </div>
        <div class="flex items-center gap-3">
            <flux:button variant="primary" wire:click="scheduleInstructor">{{ __('Schedule instructor') }}</flux:button>
            <flux:button variant="ghost" wire:click="checkInstructorStatus">{{ __('Check status') }}</flux:button>
            <flux:button variant="danger" wire:click="unscheduleInstructor">{{ __('Unschedule') }}</flux:button>
        </div>

        @if(!empty($instructorRangeSlots))
            <flux:separator />
            <flux:heading size="xs" class="text-gray-600">{{ __('Instructor availability for selected range') }}</flux:heading>
            <div class="space-y-2">
                @foreach($instructorRangeSlots as $slot)
                    <div class="flex items-center justify-between text-sm">
                        <span>{{ $slot['start'] }} - {{ $slot['end'] }}</span>
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" color="{{ $slot['available'] ? 'green' : 'rose' }}">
                                {{ $slot['available'] ? __('Available') : __('Not available') }}
                            </flux:badge>
                            <span class="text-xs text-gray-500">{{ $slot['current_load'] }}% + {{ $slot['new_load'] }}% = {{ $slot['total_load'] }}%</span>
                        </div>
                    </div>
                    @if(!empty($slot['assignments']))
                        <div class="ml-2 text-xs text-gray-600 space-y-1">
                            @foreach($slot['assignments'] as $assignment)
                                <div class="flex items-center gap-2">
                                    <flux:badge size="xs" color="gray">{{ $assignment['load_percentage'] }}%</flux:badge>
                                    <span>{{ $assignment['resource'] ?? __('Resource') }}</span>
                                    @if($assignment['activity'])
                                        <span class="text-gray-500">({{ $assignment['activity'] }})</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </flux:card>

    <div class="mb-4">
        <livewire:components.workflows-panel :model="$resource" :read-only="true" />
    </div>
    <div class="mb-4">
        <livewire:components.notes-panel :model="$resource" :read-only="true" />
    </div>
</div>
