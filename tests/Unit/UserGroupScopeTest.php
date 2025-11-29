<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Group;
use App\Models\Participant;
use App\Models\User;
use App\Models\WorkflowTemplate;
use App\Scopes\UserGroupScope;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserGroupScopeTest extends TestCase
{
    public function test_user_can_only_see_activities_in_their_groups(): void
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

        // Test user1 can only see activities in group1
        Auth::login($user1);
        $user1Activities = Activity::all();

        $this->assertCount(2, $user1Activities);
        $this->assertTrue($user1Activities->contains('name', 'Activity 1'));
        $this->assertTrue($user1Activities->contains('name', 'Activity 3'));
        $this->assertFalse($user1Activities->contains('name', 'Activity 2'));

        // Test user2 can only see activities in group2
        Auth::login($user2);
        $user2Activities = Activity::all();

        $this->assertCount(2, $user2Activities);
        $this->assertTrue($user2Activities->contains('name', 'Activity 2'));
        $this->assertTrue($user2Activities->contains('name', 'Activity 3'));
        $this->assertFalse($user2Activities->contains('name', 'Activity 1'));
    }

    public function test_user_can_only_see_companies_in_their_groups(): void
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
        $company3 = Company::factory()->create(['name' => 'Company 3']);

        $company1->groups()->attach($group1);
        $company2->groups()->attach($group2);
        $company3->groups()->attach([$group1, $group2]); // Both groups

        // Test user1 can only see companies in group1
        Auth::login($user1);
        $user1Companies = Company::all();

        $this->assertCount(2, $user1Companies);
        $this->assertTrue($user1Companies->contains('name', 'Company 1'));
        $this->assertTrue($user1Companies->contains('name', 'Company 3'));
        $this->assertFalse($user1Companies->contains('name', 'Company 2'));

        // Test user2 can only see companies in group2
        Auth::login($user2);
        $user2Companies = Company::all();

        $this->assertCount(2, $user2Companies);
        $this->assertTrue($user2Companies->contains('name', 'Company 2'));
        $this->assertTrue($user2Companies->contains('name', 'Company 3'));
        $this->assertFalse($user2Companies->contains('name', 'Company 1'));
    }

    public function test_user_can_only_see_participants_in_their_groups(): void
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
        $participant3 = Participant::factory()->create(['name' => 'Participant 3']);

        $participant1->groups()->attach($group1);
        $participant2->groups()->attach($group2);
        $participant3->groups()->attach([$group1, $group2]); // Both groups

        // Test user1 can only see participants in group1
        Auth::login($user1);
        $user1Participants = Participant::all();

        $this->assertCount(2, $user1Participants);
        $this->assertTrue($user1Participants->contains('name', 'Participant 1'));
        $this->assertTrue($user1Participants->contains('name', 'Participant 3'));
        $this->assertFalse($user1Participants->contains('name', 'Participant 2'));

        // Test user2 can only see participants in group2
        Auth::login($user2);
        $user2Participants = Participant::all();

        $this->assertCount(2, $user2Participants);
        $this->assertTrue($user2Participants->contains('name', 'Participant 2'));
        $this->assertTrue($user2Participants->contains('name', 'Participant 3'));
        $this->assertFalse($user2Participants->contains('name', 'Participant 1'));
    }

    public function test_user_can_only_see_workflow_templates_in_their_groups(): void
    {
        // Create users and groups
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 2']);

        // Assign users to groups
        $user1->groups()->attach($group1);
        $user2->groups()->attach($group2);

        // Create workflow templates and assign to groups
        $workflowTemplate1 = WorkflowTemplate::factory()->create(['name' => 'Template 1']);
        $workflowTemplate2 = WorkflowTemplate::factory()->create(['name' => 'Template 2']);
        $workflowTemplate3 = WorkflowTemplate::factory()->create(['name' => 'Template 3']);

        $workflowTemplate1->groups()->attach($group1);
        $workflowTemplate2->groups()->attach($group2);
        $workflowTemplate3->groups()->attach([$group1, $group2]); // Both groups

        // Test user1 can only see workflow templates in group1
        Auth::login($user1);
        $user1Templates = WorkflowTemplate::all();

        $this->assertCount(2, $user1Templates);
        $this->assertTrue($user1Templates->contains('name', 'Template 1'));
        $this->assertTrue($user1Templates->contains('name', 'Template 3'));
        $this->assertFalse($user1Templates->contains('name', 'Template 2'));

        // Test user2 can only see workflow templates in group2
        Auth::login($user2);
        $user2Templates = WorkflowTemplate::all();

        $this->assertCount(2, $user2Templates);
        $this->assertTrue($user2Templates->contains('name', 'Template 2'));
        $this->assertTrue($user2Templates->contains('name', 'Template 3'));
        $this->assertFalse($user2Templates->contains('name', 'Template 1'));
    }

    public function test_user_with_no_groups_sees_no_items(): void
    {
        // Create user without groups
        $user = User::factory()->create();
        $group = Group::factory()->create();

        // Create items with groups
        $activity = Activity::factory()->create();
        $company = Company::factory()->create();
        $participant = Participant::factory()->create();
        $workflowTemplate = WorkflowTemplate::factory()->create();

        $activity->groups()->attach($group);
        $company->groups()->attach($group);
        $participant->groups()->attach($group);
        $workflowTemplate->groups()->attach($group);

        // Test user sees nothing
        Auth::login($user);

        $this->assertCount(0, Activity::all());
        $this->assertCount(0, Company::all());
        $this->assertCount(0, Participant::all());
        $this->assertCount(0, WorkflowTemplate::all());
    }

    public function test_unauthenticated_user_sees_no_items(): void
    {
        // Create items with groups
        $group = Group::factory()->create();
        $activity = Activity::factory()->create();
        $company = Company::factory()->create();
        $participant = Participant::factory()->create();
        $workflowTemplate = WorkflowTemplate::factory()->create();

        $activity->groups()->attach($group);
        $company->groups()->attach($group);
        $participant->groups()->attach($group);
        $workflowTemplate->groups()->attach($group);

        // Test unauthenticated user sees nothing
        Auth::logout();

        $this->assertCount(0, Activity::all());
        $this->assertCount(0, Company::all());
        $this->assertCount(0, Participant::all());
        $this->assertCount(0, WorkflowTemplate::all());
    }

    public function test_global_scope_can_be_bypassed_when_needed(): void
    {
        // Create user and group
        $user = User::factory()->create();
        $group1 = Group::factory()->create();
        $group2 = Group::factory()->create();

        $user->groups()->attach($group1);

        // Create activities in different groups
        $activity1 = Activity::factory()->create(['name' => 'Activity 1']);
        $activity2 = Activity::factory()->create(['name' => 'Activity 2']);

        $activity1->groups()->attach($group1);
        $activity2->groups()->attach($group2);

        Auth::login($user);

        // Normal query should only show user's group activities
        $this->assertCount(1, Activity::all());

        // Bypassing scope should show all activities
        $allActivities = Activity::withoutGlobalScope(UserGroupScope::class)->get();
        $this->assertCount(2, $allActivities);
        $this->assertTrue($allActivities->contains('name', 'Activity 1'));
        $this->assertTrue($allActivities->contains('name', 'Activity 2'));
    }
}
