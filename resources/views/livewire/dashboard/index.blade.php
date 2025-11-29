<div>
    <div class="grid gap-4 lg:grid-cols-2">
        @foreach($widgets as $widget)
            <div class="{{ $widget['card-style'] ?? '' }}">
                <flux:accordion>
                    <flux:accordion.item wire:key="{{ $widget['id'] ?? $loop->index }}" expanded>
                        <flux:accordion.heading>
                            <div class="flex items-center justify-between w-full">
                                <flux:heading size="md">{{ __($widget['label'] ?? 'Widget') }}</flux:heading>
                            </div>
                        </flux:accordion.heading>
                        <flux:accordion.content class="mt-2">
                            <flux:card class="space-y-2">
                                @livewire($widget['component'], $widget['params'], key($widget['component'] . json_encode($widget['params'])))
                            </flux:card>
                        </flux:accordion.content>
                    </flux:accordion.item>
                </flux:accordion>
            </div>
        @endforeach
    </div>
</div>
