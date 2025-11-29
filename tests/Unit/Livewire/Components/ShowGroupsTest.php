<?php

declare(strict_types=1);

namespace Tests\Unit\Livewire\Components;

use App\Livewire\Components\ShowGroups;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class ShowGroupsTest extends TestCase
{
    public function test_component_only_shows_groups_user_has_access_to(): void
    {
        // Create user and groups
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Accessible Group']);
        $group2 = Group::factory()->create(['name' => 'Inaccessible Group']);

        // User only has access to group1
        $user->groups()->attach($group1);

        Auth::login($user);

        // Create collection with both groups
        $allGroups = collect([$group1, $group2]);

        $component = Livewire::test(ShowGroups::class, [
            'groups' => $allGroups,
        ]);

        // Component should only have the accessible group
        $filteredGroups = $component->get('groups');
        $this->assertCount(1, $filteredGroups);
        $this->assertTrue($filteredGroups->contains($group1));
        $this->assertFalse($filteredGroups->contains($group2));
    }

    public function test_component_shows_multiple_accessible_groups(): void
    {
        // Create user and groups
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Accessible Group 1']);
        $group2 = Group::factory()->create(['name' => 'Accessible Group 2']);
        $group3 = Group::factory()->create(['name' => 'Inaccessible Group']);

        // User has access to group1 and group2
        $user->groups()->attach([$group1, $group2]);

        Auth::login($user);

        // Create collection with all groups
        $allGroups = collect([$group1, $group2, $group3]);

        $component = Livewire::test(ShowGroups::class, [
            'groups' => $allGroups,
        ]);

        // Component should only have the accessible groups
        $filteredGroups = $component->get('groups');
        $this->assertCount(2, $filteredGroups);
        $this->assertTrue($filteredGroups->contains($group1));
        $this->assertTrue($filteredGroups->contains($group2));
        $this->assertFalse($filteredGroups->contains($group3));
    }

    public function test_component_shows_empty_collection_for_user_without_groups(): void
    {
        // Create user without groups
        $user = User::factory()->create();
        $group = Group::factory()->create(['name' => 'Some Group']);

        Auth::login($user);

        // Create collection with groups
        $allGroups = collect([$group]);

        $component = Livewire::test(ShowGroups::class, [
            'groups' => $allGroups,
        ]);

        // Component should have empty collection
        $filteredGroups = $component->get('groups');
        $this->assertCount(0, $filteredGroups);
    }

    public function test_component_shows_empty_collection_for_unauthenticated_user(): void
    {
        // Create groups but no authenticated user
        $group = Group::factory()->create(['name' => 'Some Group']);

        Auth::logout();

        // Create collection with groups
        $allGroups = collect([$group]);

        $component = Livewire::test(ShowGroups::class, [
            'groups' => $allGroups,
        ]);

        // Component should have empty collection
        $filteredGroups = $component->get('groups');
        $this->assertCount(0, $filteredGroups);
    }

    public function test_component_preserves_group_order(): void
    {
        // Create user and groups
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Group A']);
        $group2 = Group::factory()->create(['name' => 'Group B']);
        $group3 = Group::factory()->create(['name' => 'Group C']);

        // User has access to all groups
        $user->groups()->attach([$group1, $group2, $group3]);

        Auth::login($user);

        // Create collection with specific order
        $allGroups = collect([$group3, $group1, $group2]);

        $component = Livewire::test(ShowGroups::class, [
            'groups' => $allGroups,
        ]);

        // Component should preserve the order
        $filteredGroups = $component->get('groups');
        $this->assertCount(3, $filteredGroups);
        $this->assertEquals($group3->id, $filteredGroups->get(0)->id);
        $this->assertEquals($group1->id, $filteredGroups->get(1)->id);
        $this->assertEquals($group2->id, $filteredGroups->get(2)->id);
    }

    public function test_component_renders_successfully_with_groups(): void
    {
        // Create user with groups
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Test Group 1']);
        $group2 = Group::factory()->create(['name' => 'Test Group 2']);
        $user->groups()->attach([$group1, $group2]);

        Auth::login($user);

        // Create collection with groups
        $allGroups = collect([$group1, $group2]);

        $component = Livewire::test(ShowGroups::class, [
            'groups' => $allGroups,
        ]);

        $component->assertStatus(200);
        $component->assertSee(__('Visible in groups'));
        $component->assertSee('Test Group 1');
        $component->assertSee('Test Group 2');
    }

    public function test_component_renders_successfully_with_empty_groups(): void
    {
        // Create user without groups
        $user = User::factory()->create();
        Auth::login($user);

        // Create empty collection
        $emptyGroups = collect();

        $component = Livewire::test(ShowGroups::class, [
            'groups' => $emptyGroups,
        ]);

        $component->assertStatus(200);
        $component->assertSee(__('Visible in groups'));
    }

    public function test_component_handles_mixed_accessibility(): void
    {
        // Create user and groups
        $user = User::factory()->create();
        $accessibleGroup1 = Group::factory()->create(['name' => 'Accessible 1']);
        $inaccessibleGroup = Group::factory()->create(['name' => 'Inaccessible']);
        $accessibleGroup2 = Group::factory()->create(['name' => 'Accessible 2']);

        // User has access to some groups
        $user->groups()->attach([$accessibleGroup1, $accessibleGroup2]);

        Auth::login($user);

        // Create collection with mixed accessibility
        $allGroups = collect([$accessibleGroup1, $inaccessibleGroup, $accessibleGroup2]);

        $component = Livewire::test(ShowGroups::class, [
            'groups' => $allGroups,
        ]);

        // Component should filter correctly
        $filteredGroups = $component->get('groups');
        $this->assertCount(2, $filteredGroups);
        $this->assertTrue($filteredGroups->contains($accessibleGroup1));
        $this->assertTrue($filteredGroups->contains($accessibleGroup2));
        $this->assertFalse($filteredGroups->contains($inaccessibleGroup));
    }
}
