<div>
    <div class="flex items-center justify-between mb-4">
        <flux:heading size="lg">{{ __('Activities') }}
            @if($activityStatus)<flux:badge size="sm" :color="$activityStatus->badgeColor()">{{ $activityStatus->getLabel() }}</flux:badge>@endif
        </flux:heading>
        <div>
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
        </div>
    </div>

    <flux:separator class="mt-2 mb-4"/>

    @if(!$activityStatus)
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
                        @foreach ($this->activityStatuses as $activityStatus)
                            <flux:select.option value="{{ $activityStatus->value }}" wire:key="{{ $activityStatus->value }}">
                                <flux:badge size="sm" :color="$activityStatus->badgeColor()">{{ $activityStatus->getLabel() }}</flux:badge>
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
    @endif

    <flux:table :paginate="$this->rows">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'start_date'" :direction="$sortDirection" wire:click="sort('start_date')">{{ __('Start date') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'end_date'" :direction="$sortDirection" wire:click="sort('end_date')">{{ __('End date') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection" wire:click="sort('status')">{{ __('Status') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->rows() as $activity)
                <flux:table.row :key="$activity->id">
                    <flux:table.cell class="whitespace-nowrap">{{ $activity->id }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <flux:button size="xs" :href="route('activities.show', $activity->id)" icon:trailing="arrow-up-right"><flux:icon variant="micro" name="calendar" class="inline-block" />{{$activity->name }}</flux:button>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $activity->start_date?->format('d-m-Y') }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $activity->end_date?->format('d-m-Y') }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        <flux:badge size="sm" :color="$activity->status->badgeColor()">{{ $activity->status->getLabel() }}</flux:badge>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
