<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Group;
use App\Models\Participant;
use App\Models\User;
use App\Scopes\UserGroupScope;
use Tests\TestCase;

class GroupTest extends TestCase
{
    public function test_group_can_be_created(): void
    {
        $group = Group::factory()->create([
            'name'        => 'Test Group',
            'description' => 'A test group',
        ]);

        $this->assertDatabaseHas('groups', [
            'name'        => 'Test Group',
            'description' => 'A test group',
        ]);

        $this->assertEquals('Test Group', $group->name);
        $this->assertEquals('A test group', $group->description);
    }

    public function test_group_can_have_users(): void
    {
        $group = Group::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $group->users()->attach([$user1->id, $user2->id]);

        $this->assertCount(2, $group->users);
        $this->assertTrue($group->users->contains($user1));
        $this->assertTrue($group->users->contains($user2));
    }

    public function test_user_can_belong_to_multiple_groups(): void
    {
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 2']);

        $user->groups()->attach([$group1->id, $group2->id]);

        $this->assertCount(2, $user->groups);
        $this->assertTrue($user->groups->contains($group1));
        $this->assertTrue($user->groups->contains($group2));
    }

    public function test_group_can_have_activities(): void
    {
        $group = Group::factory()->create();
        $activity1 = Activity::factory()->create();
        $activity2 = Activity::factory()->create();

        $group->activities()->attach([$activity1->id, $activity2->id]);

        // Load activities without the UserGroupScope since this test is about group relationships
        $group->load(['activities' => function ($query) {
            $query->withoutGlobalScope(UserGroupScope::class);
        }]);

        $this->assertCount(2, $group->activities);
        $this->assertTrue($group->activities->contains($activity1));
        $this->assertTrue($group->activities->contains($activity2));
    }

    public function test_activity_can_belong_to_multiple_groups(): void
    {
        $activity = Activity::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 2']);

        $activity->groups()->attach([$group1->id, $group2->id]);

        $this->assertCount(2, $activity->groups);
        $this->assertTrue($activity->groups->contains($group1));
        $this->assertTrue($activity->groups->contains($group2));
    }

    public function test_group_can_have_companies(): void
    {
        $group = Group::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $group->companies()->attach([$company1->id, $company2->id]);

        // Load companies without the UserGroupScope since this test is about group relationships
        $group->load(['companies' => function ($query) {
            $query->withoutGlobalScope(UserGroupScope::class);
        }]);

        $this->assertCount(2, $group->companies);
        $this->assertTrue($group->companies->contains($company1));
        $this->assertTrue($group->companies->contains($company2));
    }

    public function test_company_can_belong_to_multiple_groups(): void
    {
        $company = Company::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 2']);

        $company->groups()->attach([$group1->id, $group2->id]);

        $this->assertCount(2, $company->groups);
        $this->assertTrue($company->groups->contains($group1));
        $this->assertTrue($company->groups->contains($group2));
    }

    public function test_group_can_have_participants(): void
    {
        $group = Group::factory()->create();
        $participant1 = Participant::factory()->create();
        $participant2 = Participant::factory()->create();

        $group->participants()->attach([$participant1->id, $participant2->id]);

        // Load participants without the UserGroupScope since this test is about group relationships
        $group->load(['participants' => function ($query) {
            $query->withoutGlobalScope(UserGroupScope::class);
        }]);

        $this->assertCount(2, $group->participants);
        $this->assertTrue($group->participants->contains($participant1));
        $this->assertTrue($group->participants->contains($participant2));
    }

    public function test_participant_can_belong_to_multiple_groups(): void
    {
        $participant = Participant::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 2']);

        $participant->groups()->attach([$group1->id, $group2->id]);

        $this->assertCount(2, $participant->groups);
        $this->assertTrue($participant->groups->contains($group1));
        $this->assertTrue($participant->groups->contains($group2));
    }

    public function test_group_search_functionality(): void
    {
        $group1 = Group::factory()->create(['name' => 'Marketing Team', 'description' => 'Marketing department']);
        $group2 = Group::factory()->create(['name' => 'Development Team', 'description' => 'Software development']);

        $marketingResults = Group::search('Marketing')->get();
        $developmentResults = Group::search('development')->get();

        $this->assertTrue($marketingResults->contains($group1));
        $this->assertFalse($marketingResults->contains($group2));

        $this->assertTrue($developmentResults->contains($group2));
        $this->assertFalse($developmentResults->contains($group1));
    }

    public function test_admin_users_are_attached_on_group_creation(): void
    {
        $admin = User::factory()->admin()->create();

        $group = Group::factory()->create();

        $group->load('users');

        $this->assertTrue($group->users->contains($admin));
    }
}
