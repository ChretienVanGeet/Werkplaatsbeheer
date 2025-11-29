<?php

declare(strict_types=1);

namespace Tests\Unit\Livewire\Components;

use App\Livewire\Components\SelectGroups;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class SelectGroupsTest extends TestCase
{
    public function test_component_only_shows_groups_user_has_access_to(): void
    {
        // Create user and groups
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Accessible Group']);
        $group2 = Group::factory()->create(['name' => 'Inaccessible Group']);

        // User only has access to group1
        $user->groups()->attach($group1);

        // Test component only shows accessible groups
        Auth::login($user);

        $component = Livewire::test(SelectGroups::class);

        $this->assertArrayHasKey($group1->id, $component->get('selectableGroups'));
        $this->assertArrayNotHasKey($group2->id, $component->get('selectableGroups'));
        $this->assertEquals('Accessible Group', $component->get('selectableGroups')[$group1->id]);
    }

    public function test_component_separates_visible_and_hidden_groups_on_mount(): void
    {
        // Create user and groups
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Visible Group']);
        $group2 = Group::factory()->create(['name' => 'Hidden Group']);

        // User only has access to group1
        $user->groups()->attach($group1);

        Auth::login($user);

        // Component is initialized with both groups selected
        $component = Livewire::test(SelectGroups::class, [
            'groups' => [$group1->id, $group2->id],
        ]);

        // Should separate into visible and hidden
        $this->assertEquals([$group1->id], $component->get('groups')); // Only visible group
        $this->assertEquals([$group2->id], $component->get('hiddenGroups')); // Hidden group preserved
    }

    public function test_component_preserves_hidden_groups_when_updating(): void
    {
        // Create user and groups
        $user = User::factory()->create();
        $group1 = Group::factory()->create(['name' => 'Visible Group 1']);
        $group2 = Group::factory()->create(['name' => 'Visible Group 2']);
        $group3 = Group::factory()->create(['name' => 'Hidden Group']);

        // User has access to group1 and group2 only
        $user->groups()->attach([$group1, $group2]);

        Auth::login($user);

        // Component starts with group1 and hidden group3
        $component = Livewire::test(SelectGroups::class, [
            'groups' => [$group1->id, $group3->id],
        ]);

        // Verify the initial separation
        $this->assertEquals([$group1->id], $component->get('groups')); // Only visible
        $this->assertEquals([$group3->id], $component->get('hiddenGroups')); // Hidden preserved

        // User adds group2 to visible selection by updating groups property
        $component->set('groups', [$group1->id, $group2->id]);

        // Should merge visible and hidden groups
        $expectedGroups = [$group1->id, $group2->id, $group3->id];
        sort($expectedGroups);
        $actualGroups = $component->get('groups');
        sort($actualGroups);

        $this->assertEquals($expectedGroups, $actualGroups);
    }

    public function test_component_shows_hidden_groups_count_in_template(): void
    {
        // Create user and groups
        $user = User::factory()->create();
        $visibleGroup = Group::factory()->create(['name' => 'Visible Group']);
        $hiddenGroup1 = Group::factory()->create(['name' => 'Hidden Group 1']);
        $hiddenGroup2 = Group::factory()->create(['name' => 'Hidden Group 2']);

        // User only has access to visible group
        $user->groups()->attach($visibleGroup);

        Auth::login($user);

        // Component has visible and hidden groups
        $component = Livewire::test(SelectGroups::class, [
            'groups' => [$visibleGroup->id, $hiddenGroup1->id, $hiddenGroup2->id],
        ]);

        // Should show message about hidden groups
        $component->assertSee('Let op: 2 extra groepen zijn toegekend');
    }

    public function test_component_handles_user_without_groups(): void
    {
        // Create user without groups
        $user = User::factory()->create();
        $group = Group::factory()->create(['name' => 'Some Group']);

        Auth::login($user);

        $component = Livewire::test(SelectGroups::class);

        // Should have empty selectable groups
        $this->assertEquals([], $component->get('selectableGroups'));
        $this->assertEquals([], $component->get('groups'));
        $this->assertEquals([], $component->get('hiddenGroups'));
    }

    public function test_component_handles_unauthenticated_user(): void
    {
        // Create groups but no authenticated user
        $group = Group::factory()->create(['name' => 'Some Group']);

        Auth::logout();

        $component = Livewire::test(SelectGroups::class);

        // Should have empty selectable groups
        $this->assertEquals([], $component->get('selectableGroups'));
        $this->assertEquals([], $component->get('groups'));
        $this->assertEquals([], $component->get('hiddenGroups'));
    }

    public function test_component_renders_successfully(): void
    {
        // Create user with groups
        $user = User::factory()->create();
        $group = Group::factory()->create(['name' => 'Test Group']);
        $user->groups()->attach($group);

        Auth::login($user);

        $component = Livewire::test(SelectGroups::class);

        $component->assertStatus(200);
        $component->assertSee(__('Groups'));
        $component->assertSee('Test Group');
    }
}
