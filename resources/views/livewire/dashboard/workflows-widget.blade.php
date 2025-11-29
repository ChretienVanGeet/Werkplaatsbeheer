<div>
    <div class="flex items-center justify-between mb-4">
        <flux:heading size="lg">{{ __('Workflows') }}</flux:heading>
        <div>
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
        </div>
    </div>

    <div class="filters">
        <flux:callout variant="secondary">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 p-1">
                    <flux:icon name="funnel" variant="solid" class="w-4 h-4 text-gray-500 dark:text-zinc-50" />
                    <h3 class="text-sm font-medium text-gray-700 dark:text-zinc-50">{{ __('Filters') }}</h3>
                </div>
                <div class="w-px h-4 bg-gray-300"></div>
                <div class="flex items-center gap-3">
                    <flux:label class="text-sm shrink-0"><small>{{ __('Status') }}</small></flux:label>
                    <flux:select variant="listbox" wire:model.live="stepStatusFilter" size="sm" class="!w-40">
                        <flux:select.option value=""><flux:badge size="sm" color="grey">{{ __('All') }}</flux:badge></flux:select.option>
                        @foreach (\App\Enums\WorkflowStepStatus::cases() as $status)
                            <flux:select.option value="{{ $status->value }}" wire:key="{{ $status->value }}">
                                <flux:badge size="sm" :color="$status->badgeColor()">{{ $status->getLabel() }}</flux:badge>
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="w-px h-4 bg-gray-300"></div>
                <div class="flex items-center gap-3">
                    <flux:label class="text-sm shrink-0"><small>{{ __('Object type') }}</small></flux:label>
                    @php
                        $objectTypes = [
                            '' => __('All'),
                            'App\\Models\\Activity' => __('Activity'),
                            'App\\Models\\Company' => __('Company'),
                            'App\\Models\\Participant' => __('Participant'),
                            'App\\Models\\Resource' => __('Resource'),
                            'App\\Models\\Instructor' => __('Instructor'),
                        ];
                    @endphp
                    <flux:select variant="listbox" wire:model.live="objectTypeFilter" size="sm" class="!w-44">
                        @foreach($objectTypes as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </flux:callout>
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
                        @php
                            $iconName = match ($workflow->subject_type) {
                                'App\\Models\\Company'     => 'building-office-2',
                                'App\\Models\\Participant' => 'user',
                                'App\\Models\\Activity'    => 'calendar',
                                'App\\Models\\Resource'    => 'wrench-screwdriver',
                                'App\\Models\\Instructor'  => 'academic-cap',
                                default                   => 'question-mark-circle',
                            };
                        @endphp
                        @if($workflow->subjectLink)
                            <flux:button size="xs" :href="$workflow->subjectLink" icon:trailing="arrow-up-right">
                                @if($iconName)
                                    <flux:icon variant="micro" name="{{ $iconName }}" class="inline-block" />
                                @endif
                                {{ $workflow->subject?->name }}
                            </flux:button>
                        @else
                            <span class="text-sm text-gray-600 flex items-center gap-1">
                                @if($iconName)
                                    <flux:icon variant="micro" name="{{ $iconName }}" class="inline-block" />
                                @endif
                                {{ $workflow->subject?->name ?? __('Unknown') }}
                            </span>
                        @endif
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
