<div>
    <flux:label>{{ __('Groups') }}</flux:label>
    <flux:pillbox wire:model.live="groups" multiple searchable placeholder="{{ __('Choose groups...') }}">
        @foreach ($this->selectableGroups as $id => $label)
            <flux:pillbox.option value="{{ $id }}">
                {{ $label }}
            </flux:pillbox.option>
        @endforeach
    </flux:pillbox>
    @if(!$this->showAll && count($this->hiddenGroups) > 0)
        <div class="text-amber-600 text-xs mt-0">
            @if(count($this->hiddenGroups) === 1)
                {{ __('Note: :count additional group is assigned but not visible to you.', ['count' => count($this->hiddenGroups)]) }}
            @else
                {{ __('Note: :count additional groups are assigned but not visible to you.', ['count' => count($this->hiddenGroups)]) }}
            @endif
        </div>
    @endif
</div>
