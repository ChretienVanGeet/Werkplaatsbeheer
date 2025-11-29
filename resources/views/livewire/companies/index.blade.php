<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">{{ __('Companies') }}</h2>
        <div class="flex items-center gap-4">
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
            <x-responsive-new-button :href="route('companies.create')" label="{{ __('New') }} {{__('Company') }}" />
        </div>
    </div>

    <flux:checkbox.group>
        <flux:table :paginate="$this->rows">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'industry'" :direction="$sortDirection" wire:click="sort('email')">{{ __('Industry') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'comments'" :direction="$sortDirection" wire:click="sort('comments')">{{ __('Comments') }}</flux:table.column>
{{--                <flux:table.column sortable :sorted="$sortBy === 'locations'" :direction="$sortDirection" wire:click="sort('locations')">{{ __('Locations') }}</flux:table.column>--}}
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->rows() as $company)
                    <flux:table.row :key="$company->id">
                        <flux:table.cell class="whitespace-nowrap">{{ $company->id }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $company->name }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $company->industry }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap html-content">{!! $company->comments !!}</flux:table.cell>
                        <x-tables.action-column :model="$company" :deleteClick="'confirmDelete('.$company->id.')'" :editRoute="route('companies.edit', $company)" :showRoute="route('companies.show', $company)" />
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:checkbox.group>

    <x-modals.confirm-delete :modelName="__('Company')" />
</div>
