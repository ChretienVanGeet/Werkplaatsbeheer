<?php

declare(strict_types=1);

namespace Tests\Unit\Livewire\Components;

use App\Enums\WorkflowStepStatus;
use App\Livewire\Components\WorkflowsPanel;
use App\Models\Company;
use App\Models\Workflow;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateStep;
use Database\Seeders\DevelopmentSeeder;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Tests\Concerns\DisablesUserGroupScope;
use Tests\TestCase;

class WorkflowsPanelTest extends TestCase
{
    use DisablesUserGroupScope;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DevelopmentSeeder::class);

        // Set up authenticated user with access to all groups for component tests
        $this->authenticateUserWithFullAccess();
    }

    public function test_renders_successfully(): void
    {
        $company = Company::first();

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->assertStatus(200);
    }

    public function test_mount_sets_model(): void
    {
        $company = Company::first();

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->assertSet('model', $company);
    }

    public function test_get_selectable_workflow_templates_excludes_used_templates(): void
    {
        $company = Company::first();
        $template1 = WorkflowTemplate::factory()->create();
        $template2 = WorkflowTemplate::factory()->create();
        $template3 = WorkflowTemplate::factory()->create();

        // Create workflows using templates 1 and 2
        Workflow::factory()->create([
            'subject_id'           => $company->id,
            'subject_type'         => Company::class,
            'workflow_template_id' => $template1->id,
        ]);

        Workflow::factory()->create([
            'subject_id'           => $company->id,
            'subject_type'         => Company::class,
            'workflow_template_id' => $template2->id,
        ]);

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $templates = $component->instance()->getSelectableWorkflowTemplates();

        $this->assertInstanceOf(Collection::class, $templates);
        $this->assertTrue($templates->contains('id', $template3->id));
        $this->assertFalse($templates->contains('id', $template1->id));
        $this->assertFalse($templates->contains('id', $template2->id));
    }

    public function test_confirm_delete_workflow_sets_deleting_workflow_id(): void
    {
        $company = Company::first();

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->call('confirmDeleteWorkflow', 123);

        $component->assertSet('deletingWorkflowId', 123);
    }

    public function test_update_status_updates_workflow_step_status(): void
    {
        $company = Company::first();

        $template = WorkflowTemplate::factory()->create();
        $templateStep = WorkflowTemplateStep::factory()->for($template)->create();

        $workflow = Workflow::factory()->create([
            'subject_id'           => $company->id,
            'subject_type'         => Company::class,
            'workflow_template_id' => $template->id,
        ]);

        $step = WorkflowStep::factory()->for($workflow)->create([
            'workflow_template_step_id' => $templateStep->id,
            'status'                    => WorkflowStepStatus::CREATED,
        ]);

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->call('updateStatus', $step->id, 'in_progress');

        $component->assertHasNoErrors();

        $step->refresh();
        $this->assertEquals(WorkflowStepStatus::IN_PROGRESS, $step->status);
    }

    public function test_add_selected_workflow_creates_workflow_and_steps(): void
    {
        $company = Company::first();
        $template = WorkflowTemplate::factory()->create();

        $templateStep1 = WorkflowTemplateStep::factory()->create([
            'workflow_template_id' => $template->id,
        ]);
        $templateStep2 = WorkflowTemplateStep::factory()->create([
            'workflow_template_id' => $template->id,
        ]);

        $initialWorkflowStepsCount = WorkflowStep::count();

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->set('selectedWorkflowTemplateId', $template->id)
            ->call('addSelectedWorkflow');

        $component->assertSet('selectedWorkflowTemplateId', null);

        $this->assertDatabaseHas('workflows', [
            'subject_id'           => $company->id,
            'subject_type'         => Company::class,
            'workflow_template_id' => $template->id,
        ]);

        $this->assertEquals($initialWorkflowStepsCount + 2, WorkflowStep::count());
    }

    public function test_add_selected_workflow_creates_steps_with_correct_data(): void
    {
        $company = Company::first();
        $template = WorkflowTemplate::factory()->create();

        $templateStep1 = WorkflowTemplateStep::factory()->create([
            'workflow_template_id' => $template->id,
        ]);
        $templateStep2 = WorkflowTemplateStep::factory()->create([
            'workflow_template_id' => $template->id,
        ]);

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->set('selectedWorkflowTemplateId', $template->id)
            ->call('addSelectedWorkflow');

        $this->assertDatabaseHas('workflow_steps', [
            'workflow_template_step_id' => $templateStep1->id,
            'status'                    => WorkflowStepStatus::CREATED,
        ]);

        $this->assertDatabaseHas('workflow_steps', [
            'workflow_template_step_id' => $templateStep2->id,
            'status'                    => WorkflowStepStatus::CREATED,
        ]);
    }

    public function test_delete_workflow_removes_workflow(): void
    {
        $company = Company::first();
        $template = WorkflowTemplate::factory()->create();
        $workflow = Workflow::factory()->create([
            'subject_id'           => $company->id,
            'subject_type'         => Company::class,
            'workflow_template_id' => $template->id,
        ]);

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->set('deletingWorkflowId', $workflow->id)
            ->call('deleteWorkflow');

        $component->assertSet('deletingWorkflowId', null);
        $this->assertDatabaseMissing('workflows', ['id' => $workflow->id]);
    }

    public function test_workflow_template_id_is_nullable(): void
    {
        $company = Company::first();

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->assertSet('selectedWorkflowTemplateId', null);
    }

    public function test_editing_workflow_is_nullable(): void
    {
        $company = Company::first();

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->assertSet('editingWorkflow', null);
    }

    public function test_deleting_workflow_id_is_nullable(): void
    {
        $company = Company::first();

        $component = Livewire::test(WorkflowsPanel::class, ['model' => $company]);

        $component->assertSet('deletingWorkflowId', null);
    }
}
