@props([
    'name' => 'confirm-delete',
    'modelName' => __('item'),
    'submitAction' => 'delete',
    'actionType' => 'submit'
])

<flux:modal name="{{ $name }}" class="min-w-[22rem]">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Delete :model', ['model' => $modelName]) }}?</flux:heading>
            <flux:text class="mt-2">
                <p>{{ __("You're about to delete this object.") }}</p>
                <p>{{ __('This action cannot be reversed.') }}</p>
            </flux:text>
        </div>
        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>
            <flux:button type="{{ $actionType }}" wire:click="{{ $submitAction }}" variant="danger">{{ __('Delete') }}</flux:button>
        </div>
    </div>
</flux:modal>
