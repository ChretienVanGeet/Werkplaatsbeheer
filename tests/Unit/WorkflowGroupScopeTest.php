<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Group;
use App\Models\Participant;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowTemplate;
use App\Scopes\WorkflowGroupScope;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WorkflowGroupScopeTest extends TestCase
{
    public function test_user_can_only_see_workflows_for_activities_in_their_groups(): void
    {
        // Create users and groups
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 2']);

        // Assign users to groups
        $user1->groups()->attach($group1);
        $user2->groups()->attach($group2);

        // Create activities and assign to groups
        $activity1 = Activity::factory()->create(['name' => 'Activity 1']);
        $activity2 = Activity::factory()->create(['name' => 'Activity 2']);
        $activity3 = Activity::factory()->create(['name' => 'Activity 3']);

        $activity1->groups()->attach($group1);
        $activity2->groups()->attach($group2);
        $activity3->groups()->attach([$group1, $group2]); // Both groups

        // Create workflow template and workflows
        $workflowTemplate = WorkflowTemplate::factory()->create();

        $workflow1 = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Activity::class,
            'subject_id'           => $activity1->id,
        ]);

        $workflow2 = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Activity::class,
            'subject_id'           => $activity2->id,
        ]);

        $workflow3 = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Activity::class,
            'subject_id'           => $activity3->id,
        ]);

        // Test user1 can only see workflows for activities in group1
        Auth::login($user1);
        $user1Workflows = Workflow::all();

        $this->assertCount(2, $user1Workflows);
        $this->assertTrue($user1Workflows->contains('id', $workflow1->id));
        $this->assertTrue($user1Workflows->contains('id', $workflow3->id));
        $this->assertFalse($user1Workflows->contains('id', $workflow2->id));

        // Test user2 can only see workflows for activities in group2
        Auth::login($user2);
        $user2Workflows = Workflow::all();

        $this->assertCount(2, $user2Workflows);
        $this->assertTrue($user2Workflows->contains('id', $workflow2->id));
        $this->assertTrue($user2Workflows->contains('id', $workflow3->id));
        $this->assertFalse($user2Workflows->contains('id', $workflow1->id));
    }

    public function test_user_can_only_see_workflows_for_companies_in_their_groups(): void
    {
        // Create users and groups
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 2']);

        // Assign users to groups
        $user1->groups()->attach($group1);
        $user2->groups()->attach($group2);

        // Create companies and assign to groups
        $company1 = Company::factory()->create(['name' => 'Company 1']);
        $company2 = Company::factory()->create(['name' => 'Company 2']);

        $company1->groups()->attach($group1);
        $company2->groups()->attach($group2);

        // Create workflow template and workflows
        $workflowTemplate = WorkflowTemplate::factory()->create();

        $workflow1 = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Company::class,
            'subject_id'           => $company1->id,
        ]);

        $workflow2 = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Company::class,
            'subject_id'           => $company2->id,
        ]);

        // Test user1 can only see workflows for companies in group1
        Auth::login($user1);
        $user1Workflows = Workflow::all();

        $this->assertCount(1, $user1Workflows);
        $this->assertEquals($workflow1->id, $user1Workflows->first()->id);

        // Test user2 can only see workflows for companies in group2
        Auth::login($user2);
        $user2Workflows = Workflow::all();

        $this->assertCount(1, $user2Workflows);
        $this->assertEquals($workflow2->id, $user2Workflows->first()->id);
    }

    public function test_user_can_only_see_workflows_for_participants_in_their_groups(): void
    {
        // Create users and groups
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 2']);

        // Assign users to groups
        $user1->groups()->attach($group1);
        $user2->groups()->attach($group2);

        // Create participants and assign to groups
        $participant1 = Participant::factory()->create(['name' => 'Participant 1']);
        $participant2 = Participant::factory()->create(['name' => 'Participant 2']);

        $participant1->groups()->attach($group1);
        $participant2->groups()->attach($group2);

        // Create workflow template and workflows
        $workflowTemplate = WorkflowTemplate::factory()->create();

        $workflow1 = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Participant::class,
            'subject_id'           => $participant1->id,
        ]);

        $workflow2 = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Participant::class,
            'subject_id'           => $participant2->id,
        ]);

        // Test user1 can only see workflows for participants in group1
        Auth::login($user1);
        $user1Workflows = Workflow::all();

        $this->assertCount(1, $user1Workflows);
        $this->assertEquals($workflow1->id, $user1Workflows->first()->id);

        // Test user2 can only see workflows for participants in group2
        Auth::login($user2);
        $user2Workflows = Workflow::all();

        $this->assertCount(1, $user2Workflows);
        $this->assertEquals($workflow2->id, $user2Workflows->first()->id);
    }

    public function test_user_can_see_workflows_for_mixed_subject_types_in_their_groups(): void
    {
        // Create user and group
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 2']);

        $user->groups()->attach($group1);

        // Create subjects in different groups
        $activity = Activity::factory()->create();
        $company = Company::factory()->create();
        $participant = Participant::factory()->create();

        $activity->groups()->attach($group1);    // User's group
        $company->groups()->attach($group2);     // Different group
        $participant->groups()->attach($group1); // User's group

        // Create workflows
        $workflowTemplate = WorkflowTemplate::factory()->create();

        $activityWorkflow = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Activity::class,
            'subject_id'           => $activity->id,
        ]);

        $companyWorkflow = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Company::class,
            'subject_id'           => $company->id,
        ]);

        $participantWorkflow = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Participant::class,
            'subject_id'           => $participant->id,
        ]);

        // Test user can only see workflows for subjects in their group
        Auth::login($user);
        $workflows = Workflow::all();

        $this->assertCount(2, $workflows);
        $this->assertTrue($workflows->contains('id', $activityWorkflow->id));
        $this->assertTrue($workflows->contains('id', $participantWorkflow->id));
        $this->assertFalse($workflows->contains('id', $companyWorkflow->id));
    }

    public function test_user_with_no_groups_sees_no_workflows(): void
    {
        // Create user without groups
        $user = User::factory()->create();
        $group = Group::factory()->create();

        // Create subject with group
        $activity = Activity::factory()->create();
        $activity->groups()->attach($group);

        // Create workflow
        $workflowTemplate = WorkflowTemplate::factory()->create();
        $workflow = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Activity::class,
            'subject_id'           => $activity->id,
        ]);

        // Test user sees no workflows
        Auth::login($user);
        $this->assertCount(0, Workflow::all());
    }

    public function test_unauthenticated_user_sees_no_workflows(): void
    {
        // Create subject and workflow
        $group = Group::factory()->create();
        $activity = Activity::factory()->create();
        $activity->groups()->attach($group);

        $workflowTemplate = WorkflowTemplate::factory()->create();
        $workflow = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Activity::class,
            'subject_id'           => $activity->id,
        ]);

        // Test unauthenticated user sees nothing
        Auth::logout();
        $this->assertCount(0, Workflow::all());
    }

    public function test_workflow_global_scope_can_be_bypassed_when_needed(): void
    {
        // Create user and groups
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        $user->groups()->attach($group1);

        // Create activities in different groups
        $activity1 = Activity::factory()->create();
        $activity2 = Activity::factory()->create();

        $activity1->groups()->attach($group1);
        $activity2->groups()->attach($group2);

        // Create workflows
        $workflowTemplate = WorkflowTemplate::factory()->create();

        $workflow1 = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Activity::class,
            'subject_id'           => $activity1->id,
        ]);

        $workflow2 = Workflow::factory()->create([
            'workflow_template_id' => $workflowTemplate->id,
            'subject_type'         => Activity::class,
            'subject_id'           => $activity2->id,
        ]);

        Auth::login($user);

        // Normal query should only show user's group workflows
        $this->assertCount(1, Workflow::all());

        // Bypassing scope should show all workflows
        $allWorkflows = Workflow::withoutGlobalScope(WorkflowGroupScope::class)->get();
        $this->assertCount(2, $allWorkflows);
        $this->assertTrue($allWorkflows->contains('id', $workflow1->id));
        $this->assertTrue($allWorkflows->contains('id', $workflow2->id));
    }
}
