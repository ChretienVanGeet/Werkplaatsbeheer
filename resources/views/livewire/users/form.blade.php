<div>
    <flux:heading>{{ $heading }}</flux:heading>
    <flux:text class="mt-2">{{ $subHeading  }}</flux:text>
    <flux:separator class="my-4" />

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:input
            wire:model="name"
            label="{{ __('Name') }}"
            type="text"
            name="name"
            required
            autofocus
        />

        <flux:input
            wire:model="email"
            label="{{ __('Email') }}"
            name="email"
            required
        />

        <flux:input
            mask="06-99999999"
            wire:model="mobile"
            label="{{ __('Mobile') }}"
            name="mobile"
        />

        <flux:input
            wire:model="organisation"
            label="{{ __('Organisation') }}"
            name="organisation"
        />

        <flux:field>
            <flux:label>{{ __('User role') }}</flux:label>
            <flux:select variant="listbox" wire:model="role">
                @foreach ($this->userRoles as $role)
                    <flux:select.option value="{{ $role->value }}" wire:key="{{ $role->value }}">
                        {{ $role->getLabel() }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="status" />
        </flux:field>

        <livewire:components.select-groups wire:model="groups" showAll="true" />

        @if(!$user->exists) {{-- New user only --}}
            <flux:input
                wire:model="password"
                type="password"
                label="{{ __('Password') }}"
                name="password"
                required
            />

            <flux:input
                wire:model="password_confirmation"
                type="password"
                label="{{ __('Password confirm') }}"
                name="password_confirmation"
                required
            />
        @endif

        <flux:separator class="my-4" />
        <div class="flex flex-row-reverse gap-3">
            <flux:button size="sm" variant="primary" type="submit">{{ __('Save') }}</flux:button>
            <flux:button size="sm" variant="filled" href="{{ route('users.index') }}" type="submit">{{ __('Back') }}</flux:button>
        </div>
    </form>
</div>
