<div>
    <div class="flex items-center justify-between mb-4">
        <flux:heading size="lg">{{ __('Workflows') }}</flux:heading>
        <div>
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
        </div>
    </div>
    <flux:separator class="mt-2 mb-4"/>

    <flux:table :paginate="$this->rows">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
            <flux:table.column>{{ __('Subject') }}</flux:table.column>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->rows() as $workflow)
                <flux:table.row :key="$workflow->id">
                    <flux:table.cell class="whitespace-nowrap align-top">{{ $workflow->id }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap align-top">
                        <flux:button size="xs" :href="$workflow->subjectLink" icon:trailing="arrow-up-right"><flux:icon variant="micro" name="{{ $workflow->subjectIconName }}" class="inline-block" />{{ $workflow->subject?->name }}</flux:button>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                            <flux:accordion class="px-4">
                                <flux:accordion.item>
                                    <flux:accordion.heading>
                                        <div class="flex items-center justify-between">
                                            {{$workflow->workflowTemplate->name}}
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
                                                    <flux:table.row>
                                                        <flux:table.cell><span class="ps-4 flex items-center gap-1">
                                                            {{ __('Step') }} {{ $loop->iteration }}: {{ $workflowStep->workflowTemplateStep->name }}

                                                            <flux:tooltip toggleable>
                                                                <flux:button icon="information-circle" size="sm" variant="ghost" />
                                                                <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                                                    {{ $workflowStep->workflowTemplateStep->description }}
                                                                </flux:tooltip.content>
                                                            </flux:tooltip>
                                                        </span></flux:table.cell>
                                                        <flux:table.cell align="end"><flux:badge size="sm" :color="$workflowStep->status->badgeColor()">{{ $workflowStep->status->getLabel() }}</flux:badge></flux:table.cell>
                                                    </flux:table.row>
                                                @endforeach
                                            </flux:table.rows>
                                        </flux:table>
                                    </flux:accordion.content>
                                </flux:accordion.item>
                            </flux:accordion>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
