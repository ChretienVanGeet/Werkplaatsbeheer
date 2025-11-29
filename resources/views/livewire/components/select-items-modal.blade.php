<div>
    <div class="flex justify-between">
        <div>
            <flux:heading>{{ $title }}</flux:heading>
        </div>
    </div>

    <div wire:sortable="updateItemOrder" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($this->existingItems as  $index => $data)
            <div wire:key="item-{{ $index }}" @if(!$viewOnly) wire:sortable.item="{{ $index }}" @endif class="w-full">
                <flux:card size="sm" class="p-2 transition-transform duration-200">
                    <div class="flex justify-between m-0 items-center">
                        <div class="flex items-center">
                            @if(!$viewOnly)
                                <flux:button variant="ghost" class="cursor-move" size="xs" icon="bars-3" wire:sortable.handle />
                            @endif
                            <span class="ml-2">{{ $data['label'] }}</span>
                        </div>
                        <div>
                            <flux:button.group class="ml-auto">
                                @if(!$viewOnly)
                                    <flux:button size="xs" variant="danger" icon="trash" wire:click="confirmDeleteItem({{ $index }})" />
                                    @if(!empty($data['edit-link']))
                                        <flux:button size="xs" variant="primary" icon="pencil" color="sky" :href="$data['edit-link']" target="_blank" />
                                    @endif
                                @endif
                                @if(!empty($data['link']))
                                    <flux:button size="xs" variant="primary" icon="eye" color="sky" :href="$data['link']" target="_blank" />
                                @endif

                            </flux:button.group>
                        </div>
                    </div>
                </flux:card>
            </div>
        @empty

        @endforelse
            @if(!$viewOnly)
                <flux:card size="sm" class="hover:bg-green-50-50 dark:hover:bg-green-700 relative w-full text-center p-2">
                    <x-placeholder-pattern class="absolute inset-0 size-full stroke-green-900/20 dark:stroke-green-100/20" />
                    <flux:modal.trigger :name="$modalId">
                        <flux:button size="sm" icon="plus" variant="ghost" color="green">{{ $addLabel }}</flux:button>
                    </flux:modal.trigger>
                </flux:card>
            @endif

    </div>
    @if(!$viewOnly)
        <flux:modal :name="$modalId" class="md:w-96">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $modalTitle }}</flux:heading>
                    <flux:text class="mt-2">{{ $modalSubTitle }}</flux:text>
                </div>

                <flux:checkbox.group>
                    <flux:table :paginate="$this->rows">
                        <flux:table.columns>
                            <flux:table.column><x-tables.select-all key="idsOnPage"></x-tables.select-all></flux:table.column>
                            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
                            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            <flux:checkbox.group wire:model="selectedItems">
                                @foreach ($this->rows() as $activity)
                                    <flux:table.row :key="$activity->id">
                                        <flux:table.cell>
                                            <flux:checkbox wire:model="selectedItems" value="{{$activity->id}}" />
                                        </flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">{{ $activity->id }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">{{ $activity->name }}</flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:checkbox.group>
                        </flux:table.rows>
                    </flux:table>
                </flux:checkbox.group>

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="primary" wire:click="addItems">{{ __('Add') }}</flux:button>
                </div>
            </div>
        </flux:modal>

        <x-modals.confirm-delete name="confirm-item-delete-{{$modalId}}" submitAction="deleteItem" actionType="button" :modelName="__('Item')" />
    @endif
</div>
