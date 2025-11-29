<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">{{ __('Groups') }}</h2>
        <div class="flex items-center gap-4">
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
            <x-responsive-new-button :href="route('groups.create')" label="{{ __('New').' '.__('Group') }}" />
        </div>
    </div>

    <flux:checkbox.group>
        <flux:table :paginate="$this->rows">
            <flux:table.columns>
                <flux:table.column><x-tables.select-all key="idsOnPage"></x-tables.select-all></flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'description'" :direction="$sortDirection" wire:click="sort('description')">{{ __('Description') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('Created') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'updated_at'" :direction="$sortDirection" wire:click="sort('updated_at')">{{ __('Updated') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                <flux:checkbox.group wire:model="selectedItems">
                @foreach ($this->rows() as $group)
                    <flux:table.row :key="$group->id">
                        <flux:table.cell>
                            <flux:checkbox wire:model="selectedItems" value="{{$group->id}}" />
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $group->id }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $group->name }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $group->description }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <flux:tooltip>
                                <flux:text>{{ $group->created_at->diffForHumans() }}</flux:text>
                                <flux:tooltip.content>{{ $group->created_at->format('d-m-Y H:i') }} {{ __('by') }} {{ $group->creator?->name }}</flux:tooltip.content>
                            </flux:tooltip>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <flux:tooltip>
                                <flux:text>{{ $group->updated_at->diffForHumans() }}</flux:text>
                                <flux:tooltip.content>{{ $group->updated_at->format('d-m-Y H:i') }} {{ __('by') }} {{ $group->updater?->name }}</flux:tooltip.content>
                            </flux:tooltip>
                        </flux:table.cell>
                        <x-tables.action-column :model="$group" :disabled="$group->id === auth()->id()" :deleteClick="'confirmDelete('.$group->id.')'" :editRoute="route('groups.edit', $group)" />
                    </flux:table.row>
                @endforeach
                </flux:checkbox.group>
            </flux:table.rows>
        </flux:table>
    </flux:checkbox.group>

    <x-modals.confirm-delete :modelName="__('Group')" />
</div>
