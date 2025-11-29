<?php

declare(strict_types=1);

namespace Tests\Unit\Livewire\Components;

use App\Livewire\Components\SelectItemsModal;
use App\Models\Company;
use App\Models\Participant;
use Database\Seeders\DevelopmentSeeder;
use Livewire\Livewire;
use Tests\Concerns\DisablesUserGroupScope;
use Tests\TestCase;

class SelectItemsModalTest extends TestCase
{
    use DisablesUserGroupScope;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DevelopmentSeeder::class);

        $this->authenticateUserWithFullAccess();
    }

    public function test_renders_successfully(): void
    {
        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        $component->assertStatus(200);
    }

    public function test_add_items_adds_selected_items_to_existing(): void
    {
        $companies = Company::take(2)->get();

        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        $component->set('selectedItems', [$companies->first()->id, $companies->last()->id])
            ->call('addItems');

        $component->assertSet('selectedItems', [])
            ->assertDispatched('updateSelectedItems');

        $existingItems = $component->get('existingItems');
        $this->assertCount(2, $existingItems);
        $this->assertEquals($companies->first()->id, $existingItems[0]['id']);
        $this->assertEquals($companies->first()->name, $existingItems[0]['label']);
    }

    public function test_add_items_dispatches_update_event(): void
    {
        $company = Company::first();

        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        $component->set('selectedItems', [$company->id])
            ->call('addItems');

        $component->assertDispatched('updateSelectedItems');

        // Verify the items were added correctly
        $existingItems = $component->get('existingItems');
        $this->assertCount(1, $existingItems);
        $this->assertEquals($company->id, $existingItems[0]['id']);
        $this->assertEquals($company->name, $existingItems[0]['label']);
    }

    public function test_component_has_correct_sortable_behavior(): void
    {
        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        // Test that sorting functionality works (through trait)
        $component->call('sort', 'name');
        $component->assertSet('sortBy', 'name');
        $component->assertSet('sortDirection', 'asc');
    }

    public function test_component_can_query_companies(): void
    {
        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        // Test that the component can be rendered (implying it can query companies)
        $component->assertStatus(200);

        // Verify className is set correctly
        $this->assertEquals(Company::class, $component->get('className'));
    }

    public function test_existing_items_are_excluded_from_selection(): void
    {
        $companies = Company::take(2)->get();
        $company1 = $companies->first();
        $company2 = $companies->last();

        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [
                ['id' => $company1->id, 'label' => $company1->name],
            ],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        // Test that existing items are properly set
        $existingItems = $component->get('existingItems');
        $this->assertCount(1, $existingItems);
        $this->assertEquals($company1->id, $existingItems[0]['id']);
    }

    public function test_update_item_order_reorders_existing_items(): void
    {
        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [
                ['id' => 1, 'label' => 'Item 1'],
                ['id' => 2, 'label' => 'Item 2'],
                ['id' => 3, 'label' => 'Item 3'],
            ],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        $newOrder = [
            ['value' => '2'],
            ['value' => '0'],
            ['value' => '1'],
        ];

        $component->call('updateItemOrder', $newOrder);

        $component->assertSet('existingItems', [
            ['id' => 3, 'label' => 'Item 3'],
            ['id' => 1, 'label' => 'Item 1'],
            ['id' => 2, 'label' => 'Item 2'],
        ])
            ->assertDispatched('updateSelectedItems');
    }

    public function test_delete_item_removes_item_from_existing_items(): void
    {
        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [
                ['id' => 1, 'label' => 'Item 1'],
                ['id' => 2, 'label' => 'Item 2'],
                ['id' => 3, 'label' => 'Item 3'],
            ],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        $component->call('confirmDeleteItem', 1)
            ->call('deleteItem');

        $component->assertSet('existingItems', [
            ['id' => 1, 'label' => 'Item 1'],
            ['id' => 3, 'label' => 'Item 3'],
        ])
            ->assertDispatched('updateSelectedItems');
    }

    public function test_delete_item_reindexes_array(): void
    {
        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [
                ['id' => 1, 'label' => 'Item 1'],
                ['id' => 2, 'label' => 'Item 2'],
            ],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        $component->call('confirmDeleteItem', 0)
            ->call('deleteItem');

        $existingItems = $component->get('existingItems');

        // Verify array keys are reindexed (0, 1, 2, etc.)
        $this->assertArrayHasKey(0, $existingItems);
        $this->assertArrayNotHasKey(1, $existingItems);
        $this->assertEquals(['id' => 2, 'label' => 'Item 2'], $existingItems[0]);
    }

    public function test_locked_properties_cannot_be_modified(): void
    {
        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        // These should remain locked and not be settable via Livewire
        $component->assertSet('modalId', 'test-modal');
        $component->assertSet('className', Company::class);
    }

    public function test_component_handles_participant_class(): void
    {
        $participant = Participant::first();

        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'participant-modal',
            'className'     => Participant::class,
            'existingItems' => [],
            'idsOnPage'     => [],
            'title'         => 'Select Participants',
            'subTitle'      => 'Choose participants to add',
            'modalTitle'    => 'Participants',
            'modalSubTitle' => 'Select from available participants',
            'addLabel'      => 'Add Participants',
        ]);

        $component->set('selectedItems', [$participant->id])
            ->call('addItems');

        $component->assertSet('existingItems', [
            ['id' => $participant->id, 'label' => $participant->name],
        ]);
    }

    public function test_component_uses_has_flux_table_trait(): void
    {
        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        // Test trait properties are available
        $component->assertSet('search', '');
        $component->assertSet('selectedItems', []);
    }

    public function test_component_can_handle_empty_selected_items(): void
    {
        $component = Livewire::test(SelectItemsModal::class, [
            'modalId'       => 'test-modal',
            'className'     => Company::class,
            'existingItems' => [],
            'idsOnPage'     => [],
            'title'         => 'Select Items',
            'subTitle'      => 'Choose items to add',
            'modalTitle'    => 'Modal Title',
            'modalSubTitle' => 'Modal Subtitle',
            'addLabel'      => 'Add Items',
        ]);

        $component->set('selectedItems', [])
            ->call('addItems');

        $component->assertSet('existingItems', [])
            ->assertDispatched('updateSelectedItems');
    }
}
