<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">{{ __('Workflow templates') }}</h2>
        <div class="flex items-center gap-4">
            <flux:input size="sm" icon="magnifying-glass" placeholder="{{ __('Search...') }}" wire:model.live.debounce.300ms="search"/>
            <x-responsive-new-button :href="route('workflow-templates.create')" label="{{ __('New').' '.__('Workflow template') }}" />
        </div>
    </div>

    <flux:checkbox.group>
        <flux:table :paginate="$this->rows">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'id'" :direction="$sortDirection" wire:click="sort('id')">{{ __('ID') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Name') }}</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'workflow_steps_count'" :direction="$sortDirection" wire:click="sort('workflow_template_steps_count')"># {{ __('Steps') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->rows() as $workflowTemplate)
                    <flux:table.row :key="$workflowTemplate->id">
                        <flux:table.cell class="whitespace-nowrap">{{ $workflowTemplate->id }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $workflowTemplate->name }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $workflowTemplate->workflow_template_steps_count }}</flux:table.cell>
                        <x-tables.action-column :model="$workflowTemplate" :deleteClick="'confirmDelete('.$workflowTemplate->id.')'" :editRoute="route('workflow-templates.edit', $workflowTemplate)" />
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:checkbox.group>

    <x-modals.confirm-delete :modelName="__('Workflow template')" />
</div>
