<div>
    <flux:heading>{{ $heading }}</flux:heading>
    <flux:text class="mt-2">{{ $subHeading  }}</flux:text>
    <flux:separator class="my-4" />

    <form wire:submit="save" class="flex flex-col gap-6">
        <flux:input
            wire:model="name"
            label="{{ __('Name') }}"
            type="text"
            :label="__('Name')"
            autofocus
            :error="$errors->first('workflowTemplate.name')"
        />

        <flux:textarea
            wire:model="description"
            :label="__('Description')"
            name="description"
            :error="$errors->first('workflowTemplate.description')"
        />

        <flux:field>
            <livewire:components.select-groups wire:model="groups" />
            <flux:error name="groups" />
        </flux:field>

        <div>
             <div class="flex justify-between">
                 <div>
                    <flux:heading>{{ __('Steps') }}</flux:heading>
                    <flux:text class="my-2">{{ __('The steps for this workflow template') }}</flux:text>
                 </div>
                 <div>
                     <flux:button size="sm" icon="plus" variant="primary" color="green" wire:click="createWorkflowTemplateStep">{{ __('Add new step') }}</flux:button>
                 </div>
             </div>

            <div>
                @foreach($workflowTemplateSteps as $index => $workflowTemplateStep)
                    <div id="workflow-step-index-{{$index}}" wire:key="workflow-step-{{ $workflowTemplateStep['id'] }}">
                        <flux:card class="mb-2 p-2 w-full">
                            <div class="flex justify-between gap-2 mt-0 mb-2">
                                <div class="flex flex-col gap-1">
                                    @if($index > 0)
                                        <flux:button size="xs" icon="chevron-up" wire:click="moveWorkflowStepUp({{ $index }})" :tooltip="__('Move up')" />
                                    @endif
                                    @if($index < count($workflowTemplateSteps) - 1)
                                        <flux:button size="xs" icon="chevron-down" wire:click="moveWorkflowStepDown({{ $index }})" :tooltip="__('Move down')" />
                                    @endif
                                </div>
                                <flux:input
                                    wire:model="workflowTemplateSteps.{{ $index }}.name"
                                    :placeholder="__('Name')"
                                    name="workflowTemplateSteps[{{ $index }}][name]"
                                    id="workflowTemplateStep-name-{{ $index }}"
                                    :error="$errors->first('workflowTemplateSteps.' . $index . '.name')"
                                />
                                <span>
                                    @if(count($workflowTemplateSteps) > 1)
                                        <flux:button variant="danger" icon="trash" wire:click="confirmDeleteWorkflowTemplateStep({{ $index }})"></flux:button>
                                    @else
                                        <flux:button variant="filled" icon="trash" :tooltip="__('Delete disabled: A minimum of 1 step is required')"></flux:button>
                                    @endif
                                </span>
                            </div>
                            <div class="space-y-2">

                                <flux:textarea
                                    wire:model="workflowTemplateSteps.{{ $index }}.description"
                                    :placeholder="__('Description')"
                                    name="workflowTemplateSteps[{{ $index }}][description]"
                                    :error="$errors->first('workflowTemplateSteps.' . $index . '.description')"
                                />
                            </div>
                        </flux:card>
                    </div>
                @endforeach
            </div>

        </div>

        <flux:separator class="my-4" />
        <div class="flex flex-row-reverse gap-3">
            <flux:button size="sm" variant="primary" type="submit">{{ __('Save') }}</flux:button>
            <flux:button size="sm" variant="filled" href="{{ route('workflow-templates.index') }}" type="submit">{{ __('Back') }}</flux:button>
        </div>
    </form>

    <x-modals.confirm-delete :modelName="__('WorkflowTemplateStep')" submit-action="deleteWorkflowStep" />

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
