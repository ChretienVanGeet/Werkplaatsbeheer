<div class="mb-4">
    @if($userGroups->isNotEmpty())
        <flux:select
            wire:model.live="selectedGroupId"
            placeholder="{{ __('All groups') }}"
            variant="listbox"
            size="sm"
            class="w-full"
        >
            <flux:select.option value="">{{ __('All groups') }}</flux:select.option>
            @foreach($userGroups as $group)
                <flux:select.option value="{{ $group->id }}" wire:key="group-{{ $group->id }}">
                    {{ $group->name }}
                </flux:select.option>
            @endforeach
        </flux:select>
    @endif
</div>
