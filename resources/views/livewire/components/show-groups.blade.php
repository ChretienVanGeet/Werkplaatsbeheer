<div class="flex items-center justify-end gap-1">
    <span class="text-xs">{{__('Visible in groups')}}: </span>
    @foreach($groups as $group)
        <flux:badge size="sm">
            {{ $group->name }}
        </flux:badge>
    @endforeach
</div>
