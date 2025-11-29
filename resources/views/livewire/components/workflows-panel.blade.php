<div>
    @if($model->id)
        <div class="space-y-4">

            <flux:heading class="mb-2">{{ __('Workflows') }}</flux:heading>

            {{-- Notes list --}}
            <div class="space-y-2">
                @forelse ($workflows as $workflow)

                    <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700" wire:key="workflow-{{ $workflow->id }}">
                        <flux:accordion>
                            <flux:accordion.item>
                                <flux:accordion.heading>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            {{$workflow->workflowTemplate->name}}
                                        </div>
                                        <div class="w-48 ml-2">
                                            <div class="progress-bar-wrapper" style="--progress: {{ $workflow->progress_percentage }}%">
                                                <div class="progress-bar"></div>
                                                <div class="progress-label">{{ $workflow->progress_percentage }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                </flux:accordion.heading>
                                <flux:accordion.content>
                                    <p><small>{{ $workflow->workflowTemplate->description }}</small></p>
                                    <flux:table>
                                        <flux:table.rows>
                                            @foreach($workflow->workflowSteps as $workflowStep)
                                                <flux:table.row wire:key="workflow-step-{{ $workflowStep->id }}">
                                                    <flux:table.cell>
                                                        <span class="ps-4 flex items-center gap-1">
                                                            {{ __('Step') }} {{ $loop->iteration }}: {{ $workflowStep->workflowTemplateStep->name }}

                                                            <flux:tooltip toggleable>
                                                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                                                <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                                                    {{ $workflowStep->workflowTemplateStep->description }}
                                                                </flux:tooltip.content>
                                                            </flux:tooltip>
                                                        </span>
                                                    </flux:table.cell>
                                                    <flux:table.cell align="end" class="flex justify-end">
                                                        @if($readOnly)
                                                            <flux:badge size="sm" :color="$workflowStep->status->badgeColor()">
                                                                {{ $workflowStep->status->getLabel() }}
                                                            </flux:badge>
                                                        @else
                                                            <flux:select
                                                                class="max-w-36"
                                                                wire:change="updateStatus({{ $workflowStep->id }}, $event.target.value)"
                                                                variant="listbox"
                                                                :placeholder="__('Select status')"
                                                                wire:key="select-{{ $workflowStep->id }}"
                                                            >
                                                                @foreach (\App\Enums\WorkflowStepStatus::cases() as $case)
                                                                    <flux:select.option
                                                                        size="sm"
                                                                        :value="$case->value"
                                                                        :selected="$workflowStep->status->value === $case->value"
                                                                    >
                                                                        <div class="flex items-center gap-2">
                                                                            <flux:badge size="sm" :color="$case->badgeColor()">
                                                                                {{ $case->getLabel() }}
                                                                            </flux:badge>
                                                                        </div>
                                                                    </flux:select.option>
                                                                @endforeach
                                                            </flux:select>
                                                        @endif
                                                    </flux:table.cell>
                                                </flux:table.row>
                                            @endforeach
                                        </flux:table.rows>
                                    </flux:table>
                                    @if(!$readOnly)
                                        <flux:separator />
                                        <div class="flex justify-end my-2">
                                            <flux:button
                                                icon="trash"
                                                size="sm"
                                                variant="danger"
                                                color="red"
                                                :wire:click="'confirmDeleteWorkflow(' . $workflow->id . ')'"
                                            >{{ __("Remove this workflow") }}</flux:button>
                                        </div>
                                    @endif
                                </flux:accordion.content>
                            </flux:accordion.item>
                        </flux:accordion>
                    </flux:card>
                @empty
                    <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 relative">
                        <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
                        <flux:text class="text-gray-500">{{ __('No workflows yet.') }}</flux:text>
                    </flux:card>

                @endforelse
            </div>

            @if(!$readOnly)

            {{-- Note input (no form) --}}
                <div wire:submit.prevent="save">
                    <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 relative">
                        <flux:heading class="flex justify-between items-start">{{ $editingWorkflow ? __('Edit existing workflow') : __('Attach new workflow') }}</flux:heading>
                        <div class="flex flex-col gap-2 mt-2">

                            <flux:select wire:model="selectedWorkflowTemplateId" variant="listbox" :placeholder='__("Select workflow template")'>
                                @foreach($this->getSelectableWorkflowTemplates() as  $workflowTemplate)
                                    <flux:select.option value="{{ $workflowTemplate->id }}">{{$workflowTemplate->name}}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <div class="flex flex-row-reverse gap-1">

                                <flux:button wire:click="addSelectedWorkflow" size="sm" variant="primary" type="button" >{{ $editingWorkflow ? __('Update workflow') : __('Add workflow') }}</flux:button>
                                @if ($editingWorkflow)
                                    <flux:button wire:click="cancelEdit" size="sm" variant="primary" type="button" >{{ __('Cancel') }}</flux:button>
                                @endif
                            </div>
                        </div>
                    </flux:card>
                </div>

                <x-modals.confirm-delete name="confirm-workflow-delete" submitAction="deleteWorkflow" actionType="button" :modelName="__('Workflow')" />
            @endif
        </div>
    @else
        {{--        Parent not saved, so cannot add notes--}}
    @endif
</div>
