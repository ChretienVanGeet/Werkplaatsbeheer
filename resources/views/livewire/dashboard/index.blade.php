<div>
    <div class="grid gap-4 lg:grid-cols-2">
        @foreach($widgets as $widget)
            <flux:card class="space-y-2 {{ $widget['card-style'] ?? '' }}">
                @livewire($widget['component'], $widget['params'], key($widget['component'] . json_encode($widget['params'])))
            </flux:card>
        @endforeach
    </div>
</div>
