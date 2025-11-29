<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">{{ __('Users') }}</h2>
        <div class="flex items-center gap-4">
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
            <x-responsive-new-button :href="route('users.create')" label="{{ __('New').' '.__('User') }}" />
        </div>
    </div>

    <flux:checkbox.group>
        <flux:table :paginate="$this->rows">
            <flux:table.columns>
                <flux:table.column><x-tables.select-all key="idsOnPage"></x-tables.select-all></flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection" wire:click="sort('email')">{{ __('Email') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'mobile'" :direction="$sortDirection" wire:click="sort('mobile')">{{ __('Mobile') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'organisation'" :direction="$sortDirection" wire:click="sort('organisation')">{{ __('Organisation') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'role'" :direction="$sortDirection" wire:click="sort('role')">{{ __('User role') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'groups'" :direction="$sortDirection" wire:click="sort('groups')">{{ __('Groups') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'email_verified_at'" :direction="$sortDirection" wire:click="sort('email_verified_at')">{{ __('Verified') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('Created') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'updated_at'" :direction="$sortDirection" wire:click="sort('updated_at')">{{ __('Updated') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                <flux:checkbox.group wire:model="selectedItems">
                @foreach ($this->rows() as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell>
                            <flux:checkbox wire:model="selectedItems" value="{{$user->id}}" />
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $user->id }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $user->email }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $user->mobile }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $user->organisation }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                           {{ $user->role->getLabel() }}
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <flux:tooltip>
                                <flux:badge size="sm" color="lime" inset="top bottom">{{ $user->groups->count() }}</flux:badge>
                                <flux:tooltip.content>
                                    <ul>
                                    @foreach($user->groups as $group)
                                        <li>{{ $group->name }}</li>
                                    @endforeach
                                   </ul>
                                </flux:tooltip.content>
                            </flux:tooltip>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            @if($user->email_verified_at)
                                <flux:tooltip>
                                    <flux:badge size="sm" color="lime" inset="top bottom">{{ __('Verified') }}</flux:badge>
                                    <flux:tooltip.content>{{ $user->email_verified_at->format('d-m-Y H:i') }}</flux:tooltip.content>
                                </flux:tooltip>
                            @else
                                <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Unverified') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <flux:tooltip>
                                <flux:text>{{ $user->created_at->diffForHumans() }}</flux:text>
                                <flux:tooltip.content>{{ $user->created_at->format('d-m-Y H:i') }} {{ __('by') }} {{ $user->creator?->name }}</flux:tooltip.content>
                            </flux:tooltip>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <flux:tooltip>
                                <flux:text>{{ $user->updated_at->diffForHumans() }}</flux:text>
                                <flux:tooltip.content>{{ $user->updated_at->format('d-m-Y H:i') }} {{ __('by') }} {{ $user->updater?->name }}</flux:tooltip.content>
                            </flux:tooltip>
                        </flux:table.cell>
                        <x-tables.action-column :model="$user" :disabled="$user->id === auth()->id()" :deleteClick="'confirmDelete('.$user->id.')'" :editRoute="route('users.edit', $user)" />
                    </flux:table.row>
                @endforeach
                </flux:checkbox.group>
            </flux:table.rows>
        </flux:table>
    </flux:checkbox.group>

    <x-modals.confirm-delete :modelName="__('User')" />
</div>
