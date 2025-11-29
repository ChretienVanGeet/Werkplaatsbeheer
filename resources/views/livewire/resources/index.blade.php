<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">{{ __('Resources') }}</h2>
        <div class="flex items-center gap-4">
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
            <x-responsive-new-button :href="route('resources.create')" label="{{ __('New') }} {{ __('Resource') }}"/>
        </div>
    </div>

    <flux:checkbox.group>
        <flux:table :paginate="$this->rows">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'machine_type'" :direction="$sortDirection" wire:click="sort('machine_type')">{{ __('Machine type') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'instructor_capacity'" :direction="$sortDirection" wire:click="sort('instructor_capacity')">{{ __('Instructor load (%)') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->rows() as $resource)
                    <flux:table.row :key="$resource->id">
                        <flux:table.cell class="whitespace-nowrap">{{ $resource->id }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $resource->name }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $resource->machine_type }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $resource->instructor_capacity }}%</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <x-tables.action-column
                                :model="$resource"
                                :deleteClick="'confirmDelete('.$resource->id.')'"
                                :editRoute="route('resources.edit', $resource)"
                                :showRoute="route('resources.show', $resource)"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:checkbox.group>

    <x-modals.confirm-delete :modelName="__('Resource')" />
</div>
