<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">{{ __('Instructors') }}</h2>
        <div class="flex items-center gap-4">
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
            <x-responsive-new-button :href="route('instructors.create')" label="{{ __('New') }} {{ __('Instructor') }}"/>
        </div>
    </div>

    <flux:checkbox.group>
        <flux:table :paginate="$this->rows">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'supported_resources_count'" :direction="$sortDirection" wire:click="sort('supported_resources_count')">{{ __('Resources') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'assignments_count'" :direction="$sortDirection" wire:click="sort('assignments_count')">{{ __('Assignments') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->rows() as $instructor)
                    <flux:table.row :key="$instructor->id">
                        <flux:table.cell class="whitespace-nowrap">{{ $instructor->id }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $instructor->name }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $instructor->supported_resources_count }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $instructor->assignments_count }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            <x-tables.action-column
                                :model="$instructor"
                                :deleteClick="'confirmDelete('.$instructor->id.')'"
                                :editRoute="route('instructors.edit', $instructor)"
                                :showRoute="route('instructors.show', $instructor)"
                                :noteClick="'openNotes('.$instructor->id.')'"
                            />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:checkbox.group>

    <x-modals.confirm-delete :modelName="__('Instructor')" />
    <flux:modal name="instructor-notes" variant="floating" flyout>
        <div class="space-y-3 max-w-3xl">
            <flux:heading size="sm">{{ __('Notes') }}</flux:heading>
            @if(empty($notesModal))
                <flux:text class="text-sm text-gray-500">{{ __('No notes yet.') }}</flux:text>
            @else
                <div class="space-y-2">
                    @foreach($notesModal as $note)
                        <flux:card size="sm" class="p-3 space-y-2">
                            <div class="flex items-center justify-between text-xs text-gray-600">
                                <span>{{ $note['updated_at'] }}</span>
                                <span>{{ $note['updater'] ?? $note['creator'] ?? '' }}</span>
                            </div>
                            <flux:heading size="sm">{{ $note['subject'] }}</flux:heading>
                            <flux:text class="text-sm">{!! $note['content'] !!}</flux:text>
                        </flux:card>
                    @endforeach
                </div>
            @endif
            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
