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
            autofocus
        />

        <flux:field>
            <livewire:components.select-groups wire:model="groups" />
            <flux:error name="groups" />
        </flux:field>

        <flux:input
            wire:model="industry"
            label="{{ __('Industry') }}"
            name="industry"
        />

        <div>
            <div class="flex justify-between">
                <div>
                    <flux:heading>{{ __('Contacts') }}</flux:heading>
                    <flux:text class="my-2">{{ __('The contacts at this company') }}</flux:text>
                </div>
                <div>
                    <flux:button size="sm" icon="plus" variant="primary" color="green" wire:click="createCompanyContact()">{{ __('Add new contact') }}</flux:button>
                </div>
            </div>

            <div>
                @foreach($companyContacts as $index => $companyContact)
                    <div id="company-contact-index-{{$index}}" wire:key="company-contact-{{ $companyContact['id'] }}">
                        <flux:card class="mb-2 p-2 w-full">
                            <div class="flex justify-between gap-2 mt-0 mb-2">
                                <div class="flex flex-col gap-1">
                                    @if($index > 0)
                                        <flux:button size="xs" icon="chevron-up" wire:click="moveCompanyContactUp({{ $index }})" :tooltip="__('Move up')" />
                                    @endif
                                    @if($index < count($companyContacts) - 1)
                                        <flux:button size="xs" icon="chevron-down" wire:click="moveCompanyContactDown({{ $index }})" :tooltip="__('Move down')" />
                                    @endif
                                </div>
                                <flux:input
                                    wire:model="companyContacts.{{ $index }}.name"
                                    :placeholder="__('Name')"
                                    name="companyContacts[{{ $index }}][name]"
                                    id="companyContact-name-{{ $index }}"
                                    :error="$errors->first('companyContacts.' . $index . '.name')"
                                />
                                <span>
                                    <flux:button variant="danger" icon="trash" wire:click="confirmDeleteCompanyContact({{ $index }})"></flux:button>
                                </span>
                            </div>
                            <div class="space-y-2">

                                <flux:input
                                    wire:model="companyContacts.{{ $index }}.location"
                                    :placeholder="__('Location')"
                                    name="companyContacts[{{ $index }}][location]"
                                    :error="$errors->first('companyContacts.' . $index . '.location')"
                                />
                                <flux:input
                                    wire:model="companyContacts.{{ $index }}.phone"
                                    :placeholder="__('Phone')"
                                    name="companyContacts[{{ $index }}][phone]"
                                    :error="$errors->first('companyContacts.' . $index . '.phone')"
                                />
                                <flux:input
                                    wire:model="companyContacts.{{ $index }}.email"
                                    :placeholder="__('Email')"
                                    name="companyContacts[{{ $index }}][email]"
                                    :error="$errors->first('companyContacts.' . $index . '.email')"
                                />
                                <flux:input
                                    wire:model="companyContacts.{{ $index }}.role"
                                    :placeholder="__('Role')"
                                    name="companyContacts[{{ $index }}][role]"
                                    :error="$errors->first('companyContacts.' . $index . '.role')"
                                />
                            </div>
                        </flux:card>
                    </div>
                @endforeach
            </div>

        </div>


        <flux:editor
            wire:model="comments"
            label="{{ __('Comments') }}"
            name="comments"
        />

        <flux:editor
            wire:model="locations"
            label="{{ __('Locations') }}"
            name="locations"
        />

        <livewire:components.workflows-panel :model="$company" />

        <livewire:components.notes-panel :model="$company" />

        <flux:separator class="my-4" />
        <div class="flex flex-row-reverse gap-3">
            <flux:button size="sm" variant="primary" type="submit">{{ __('Save') }}</flux:button>
            <flux:button size="sm" variant="filled" href="{{ route('companies.index') }}" type="submit">{{ __('Back') }}</flux:button>
        </div>
    </form>

    <x-modals.confirm-delete :modelName="__('CompanyContact')" submit-action="deleteCompanyContact" />

    <script>
        window.addEventListener('scroll-to-new-step', (event) => {
            const id = event.detail.id;

            let attempts = 0;

            const tryScroll = () => {
                const el = document.getElementById(id);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Focus the first input (or textarea/select) inside the element
                    const firstInput = el.querySelector('input, textarea, select, [contenteditable]');
                    if (firstInput) {
                        setTimeout(() => firstInput.focus(), 200);
                    }
                } else if (attempts < 10) {
                    attempts++;
                    setTimeout(tryScroll, 50);
                } else {
                    console.warn(`Element with ID '${id}' not found after multiple attempts.`);
                }
            };

            tryScroll();
        });
    </script>
</div>
