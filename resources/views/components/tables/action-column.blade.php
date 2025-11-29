@props([
    'deleteClick',
    'editRoute',
    'showRoute',
    'disabled' => false,
    'model',
    'noteClick' => null,
])

<flux:table.cell class="w-28" align="end">
    <div class="flex items-center justify-between gap-3">
        @if(isset($model->notes_count) && $model->notes_count > 0 )
            <div class="flex items-center">
                <flux:tooltip :content="$model->notes_count. ' '. __('Note(s)')" position="top">
                    @if($noteClick)
                        <flux:button variant="ghost" size="xs" icon="newspaper" :wire:click="$noteClick" />
                    @else
                        <flux:icon name="newspaper" class="text-gray-500" />
                    @endif
                </flux:tooltip>
            </div>
        @endif

        <flux:button.group class="ml-auto">
            @can('write')
                <flux:button :disabled="$disabled" size="sm" variant="danger" icon="trash" :wire:click="$deleteClick"></flux:button>
                <flux:button size="sm" :href="$editRoute" variant="primary" color="sky" icon="pencil"></flux:button>
            @endcan

            @can('read')
                @if(isset($showRoute))
                    <flux:button size="sm" :href="$showRoute" variant="primary" color="sky" icon="eye"></flux:button>
                @endif
            @endcan

        </flux:button.group>
    </div>
</flux:table.cell>
