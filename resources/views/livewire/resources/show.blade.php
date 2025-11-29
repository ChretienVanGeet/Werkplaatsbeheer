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
                    <flux:button size="xs" :href="route('activities.show', $activity)" icon:leading="calendar" icon:trailing="arrow-up-right">
                        {{ $activity->name }}
                    </flux:button>
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
                            @php
                                $statusEnum = \App\Enums\ResourceStatus::from($slot['status']);
                                $slotBg = 'bg-white dark:bg-zinc-900';

                                if (empty($slot['status']) || $statusEnum === \App\Enums\ResourceStatus::AVAILABLE) {
                                    $slotBg = 'bg-gray-50/50 dark:bg-zinc-800/30';
                                } elseif ($statusEnum === \App\Enums\ResourceStatus::MAINTENANCE || empty($slot['assignments'])) {
                                    $slotBg = 'bg-rose-50 dark:bg-rose-900/20';
                                } elseif ($statusEnum === \App\Enums\ResourceStatus::OCCUPIED && ! empty($slot['assignments'])) {
                                    $slotBg = 'bg-green-50 dark:bg-green-900/20';
                                }
                            @endphp
                            <div class="space-y-2 rounded border border-gray-100 p-2 dark:border-zinc-800 {{ $slotBg }}">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="text-sm font-medium">{{ $slot['start'] }} - {{ $slot['end'] }}</div>
                                    <div class="flex items-center gap-2">
                                        <flux:badge size="sm" :color="$statusEnum->badgeColor()">
                                            {{ $statusEnum->getLabel() }}
                                        </flux:badge>
                                        @php
                                            $loadColor = 'gray';
                                            if (in_array($statusEnum, [\App\Enums\ResourceStatus::RESERVED, \App\Enums\ResourceStatus::OCCUPIED], true)) {
                                                $loadColor = match (true) {
                                                    $slot['total_load'] === 0 => 'rose',
                                                    $slot['total_load'] >= 100 => 'green',
                                                    default => 'yellow',
                                                };
                                            } else {
                                                $loadColor = $slot['total_load'] === 0 ? 'gray' : ($slot['uncovered'] ? 'rose' : 'green');
                                            }
                                        @endphp
                                        <flux:badge size="sm" color="{{ $loadColor }}">{{ $slot['total_load'] }}%</flux:badge>
                                        <flux:button size="xs" variant="ghost" icon="arrow-path" wire:click="toggleSlotStatus('{{ $slot['start_raw'] }}')"></flux:button>
                                    </div>
                                </div>
                                @if($slot['activity_name'])
                                    <div class="text-xs">
                                        @if($slot['activity_id'])
                                            <div class="flex items-center justify-between gap-2">
                                                <flux:button size="xs" :href="route('activities.show', $slot['activity_id'])" icon:leading="calendar" icon:trailing="arrow-up-right">
                                                    {{ $slot['activity_name'] }}
                                                </flux:button>
                                                <flux:button size="xs" variant="danger" icon="trash" wire:click="removeActivityFromSlot('{{ $slot['start_raw'] }}')"></flux:button>
                                            </div>
                                        @else
                                            <span class="text-gray-500">{{ $slot['activity_name'] }}</span>
                                        @endif
                                    </div>
                                @elseif(in_array($statusEnum, [\App\Enums\ResourceStatus::RESERVED, \App\Enums\ResourceStatus::OCCUPIED], true))
                                    <div class="text-xs">
                                        <flux:modal.trigger name="select-activity">
                                            <flux:button size="xs" variant="ghost" color="rose" icon:leading="calendar" wire:click="openActivitySelectionModal('{{ $slot['start_raw'] }}')">
                                                {{ __('No link with activity') }}
                                            </flux:button>
                                        </flux:modal.trigger>
                                    </div>
                                @endif
                                @if(empty($slot['assignments']) && $slot['uncovered'] && in_array($statusEnum, [\App\Enums\ResourceStatus::OCCUPIED, \App\Enums\ResourceStatus::RESERVED], true))
                                    <div class="text-xs">
                                        <flux:modal.trigger name="assign-instructor">
                                            <flux:button size="xs" variant="ghost" color="rose" icon:leading="academic-cap" wire:click="openAssignInstructorModal('{{ $slot['start_raw'] }}')">
                                                {{ __('Not covered by an instructor') }}
                                            </flux:button>
                                        </flux:modal.trigger>
                                    </div>
                                @else
                                    <div class="space-y-1">
                                        @foreach($slot['assignments'] as $assignment)
                                            <div class="flex items-center justify-between text-xs">
                                                <div class="flex flex-col gap-0.5">
                                                    <div class="flex items-center gap-2">
                                                        @if(!empty($assignment['instructor_id']))
                                                            <flux:button size="xs" :href="route('instructors.show', $assignment['instructor_id'])" icon:leading="academic-cap" icon:trailing="arrow-up-right">
                                                                {{ $assignment['name'] ?? __('Instructor') }}
                                                            </flux:button>
                                                        @else
                                                            <span class="font-semibold">{{ $assignment['name'] ?? __('Instructor') }}</span>
                                                        @endif
                                                    </div>
                                                    @if($assignment['activity_name'])
                                                        @if(!empty($assignment['activity_id']))
                                                            <flux:button size="xs" variant="ghost" :href="route('activities.show', $assignment['activity_id'])" icon:leading="calendar" icon:trailing="arrow-up-right">
                                                                {{ $assignment['activity_name'] }}
                                                            </flux:button>
                                                        @else
                                                            <span class="text-gray-500">{{ $assignment['activity_name'] }}</span>
                                                        @endif
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
            <flux:button variant="ghost" wire:click="setActivityForRange">{{ __('Set activity') }}</flux:button>
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
                                    <flux:button size="xs" :href="route('activities.show', $slot['activity_id'])" icon:leading="calendar" icon:trailing="arrow-up-right">
                                        {{ $slot['activity_name'] }}
                                    </flux:button>
                                @else
                                    <span class="text-xs text-gray-500">{{ $slot['activity_name'] }}</span>
                                @endif
                            @endif
                            <flux:badge size="sm" :color="$statusEnum->badgeColor()">{{ $statusEnum->getLabel() }}</flux:badge>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($rangeChecked)
            <flux:separator />
            <flux:heading size="xs" class="text-gray-600">{{ __('Result for selected range') }}</flux:heading>
            <flux:text class="text-sm text-gray-500">{{ __('No slots found for this selection') }}</flux:text>
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
            <flux:modal.trigger name="instructor-availability">
                <flux:button variant="ghost" wire:click="openInstructorAvailabilityModal">{{ __('Check instructor') }}</flux:button>
            </flux:modal.trigger>
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
        @elseif($instructorRangeChecked)
            <flux:separator />
            <flux:heading size="xs" class="text-gray-600">{{ __('Instructor availability for selected range') }}</flux:heading>
            <flux:text class="text-sm text-gray-500">{{ __('No slots found for this selection') }}</flux:text>
        @endif
    </flux:card>

    <div class="mb-4">
        <livewire:components.workflows-panel :model="$resource" :read-only="true" />
    </div>
    <div class="mb-4">
        <livewire:components.notes-panel :model="$resource" :read-only="true" />
    </div>

    <flux:modal name="assign-instructor" flyout variant="floating">
        <div class="space-y-3">
            <flux:heading size="sm">{{ __('Assign instructor') }}</flux:heading>
            @if($assignSlotStart)
                <flux:text class="text-sm text-gray-600">
                    {{ __('Slot') }}: {{ \Illuminate\Support\Carbon::parse($assignSlotStart)->format('Y-m-d H:i') }} - {{ \Illuminate\Support\Carbon::parse($assignSlotEnd)->format('H:i') }}
                </flux:text>
            @endif
            @if(empty($assignableInstructors))
                <flux:text class="text-sm text-gray-500">{{ __('No instructors available for this slot.') }}</flux:text>
            @else
                @php
                    $selectedInstructor = collect($assignableInstructors)->firstWhere('id', $assignInstructorId);
                @endphp
                <flux:dropdown>
                    <flux:button size="sm" variant="outline" icon="academic-cap">
                        {{ $selectedInstructor['name'] ?? __('Select instructor') }}
                    </flux:button>
                    <flux:menu class="min-w-[240px]">
                        <flux:menu.radio.group>
                            @foreach($assignableInstructors as $instructor)
                                <flux:menu.item
                                    as="button"
                                    type="button"
                                    wire:click="$set('assignInstructorId', {{ $instructor['id'] }})"
                                    :disabled="!$instructor['available']"
                                >
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="academic-cap" variant="micro" class="inline-block" />
                                        <span class="flex-1 text-left">{{ $instructor['name'] }}</span>
                                        @if(!$instructor['available'])
                                            <flux:badge size="xs" color="rose">{{ __('Not available') }}</flux:badge>
                                        @else
                                            <flux:badge size="xs" color="green">{{ __('Available') }}</flux:badge>
                                        @endif
                                    </div>
                                </flux:menu.item>
                            @endforeach
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>

                <div class="flex items-center gap-2 mt-3">
                    <flux:button variant="primary" wire:click="assignInstructorToSlot" :disabled="!$assignInstructorId">{{ __('Assign') }}</flux:button>
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Annuleren') }}</flux:button>
                    </flux:modal.close>
                </div>
            @endif
        </div>
    </flux:modal>

    <flux:modal name="instructor-availability" flyout variant="floating">
        <div class="space-y-3">
            <flux:heading size="sm">{{ __('Instructor availability') }}</flux:heading>
            <flux:text class="text-sm text-gray-600">
                {{ __('Range') }}: {{ \Illuminate\Support\Carbon::parse($instructorRangeStart)->format('Y-m-d H:i') }} - {{ \Illuminate\Support\Carbon::parse($instructorRangeEnd)->format('Y-m-d H:i') }}
            </flux:text>

            @if(empty($instructorsAvailability) && $instructorsAvailabilityChecked)
                <flux:text class="text-sm text-gray-500">{{ __('No instructors available for this range.') }}</flux:text>
            @else
                <div class="space-y-2">
                    @foreach($instructorsAvailability as $item)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <flux:icon name="academic-cap" variant="micro" class="inline-block" />
                                <span>{{ $item['name'] }}</span>
                            </div>
                            <flux:badge size="sm" :color="$item['available'] ? 'green' : 'rose'">
                                {{ $item['available'] ? __('Available') : __('Not available') }}
                            </flux:badge>
                        </div>
                    @endforeach
                </div>
            @endif

            <flux:modal.close>
                <flux:button variant="filled">{{ __('Close') }}</flux:button>
            </flux:modal.close>
        </div>
    </flux:modal>

    <flux:modal name="select-activity" flyout variant="floating">
        <div class="space-y-3">
            <flux:heading size="sm">{{ __('Link activity') }}</flux:heading>
            @if($activitySelectSlotStart)
                <flux:text class="text-sm text-gray-600">
                    {{ __('Slot') }}: {{ \Illuminate\Support\Carbon::parse($activitySelectSlotStart)->format('Y-m-d H:i') }} - {{ \Illuminate\Support\Carbon::parse($activitySelectSlotEnd)->format('H:i') }}
                </flux:text>
            @endif

            @if(empty($openActivities))
                <flux:text class="text-sm text-gray-500">{{ __('No open activities available') }}</flux:text>
            @else
                @php
                    $selectedActivity = collect($openActivities)->firstWhere('id', $activitySelectId);
                @endphp
                <flux:dropdown>
                    <flux:button size="sm" variant="outline" icon="calendar">
                        {{ $selectedActivity['name'] ?? __('Select activity') }}
                    </flux:button>
                    <flux:menu class="min-w-[240px]">
                        <flux:menu.radio.group>
                            @foreach($openActivities as $activity)
                                <flux:menu.item
                                    as="button"
                                    type="button"
                                    wire:click="$set('activitySelectId', {{ $activity['id'] }})"
                                >
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="calendar" variant="micro" class="inline-block" />
                                        <span class="flex-1 text-left">{{ $activity['name'] }}</span>
                                        <flux:badge size="xs" :color="$activity['status']->badgeColor()">{{ $activity['status']->getLabel() }}</flux:badge>
                                    </div>
                                </flux:menu.item>
                            @endforeach
                        </flux:menu.radio.group>
                    </flux:menu>
                </flux:dropdown>

                <div class="flex items-center gap-2 mt-3">
                    <flux:button variant="primary" wire:click="assignActivityToSlot" :disabled="!$activitySelectId">{{ __('Link') }}</flux:button>
                    <flux:modal.close>
                        <flux:button variant="filled">{{ __('Annuleren') }}</flux:button>
                    </flux:modal.close>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
