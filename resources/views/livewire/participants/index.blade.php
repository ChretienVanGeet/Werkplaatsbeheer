<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">{{ __('Participants') }}</h2>
        <div class="flex items-center gap-4">
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
            <x-responsive-new-button :href="route('participants.create')" label="{{ __('New') }} {{__('Participant') }}" />
        </div>
    </div>

    <flux:checkbox.group>
        <flux:table :paginate="$this->rows">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'phone'" :direction="$sortDirection" wire:click="sort('phone')">{{ __('Phone') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection" wire:click="sort('email')">{{ __('Email') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'city'" :direction="$sortDirection" wire:click="sort('city')">{{ __('City') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'comments'" :direction="$sortDirection" wire:click="sort('comments')">{{ __('Comments') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->rows() as $participant)
                    <flux:table.row :key="$participant->id">
                        <flux:table.cell class="whitespace-nowrap">{{ $participant->id }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $participant->name }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $participant->phone }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $participant->email }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $participant->city }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap html-content">{!! $participant->comments !!}</flux:table.cell>
                        <x-tables.action-column :model="$participant" :deleteClick="'confirmDelete('.$participant->id.')'" :editRoute="route('participants.edit', $participant)" :showRoute="route('participants.show', $participant)" />
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:checkbox.group>

    <x-modals.confirm-delete :modelName="__('Participant')" />
</div>
