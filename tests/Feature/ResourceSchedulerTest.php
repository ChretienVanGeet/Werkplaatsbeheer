<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ResourceStatus as ResourceStatusEnum;
use App\Models\Activity;
use App\Models\Group;
use App\Models\Resource;
use App\Models\User;
use App\Services\ResourceScheduler;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ResourceSchedulerTest extends TestCase
{
    public function test_slots_default_to_available_when_empty(): void
    {
        $resource = Resource::factory()->create();
        $scheduler = new ResourceScheduler;

        $start = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY)->setTime(8, 0);
        $end = $start->addHours(2);

        $slots = $scheduler->generateSlots($resource, $start, $end);

        $this->assertCount(1, $slots);
        $this->assertTrue($slots->first()['status'] === ResourceStatusEnum::AVAILABLE);
    }

    public function test_set_status_creates_two_hour_slots(): void
    {
        $resource = Resource::factory()->create();
        $scheduler = new ResourceScheduler;

        $start = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY)->setTime(8, 0);
        $end = $start->addHours(4);

        $scheduler->setStatus($resource, $start, $end, ResourceStatusEnum::RESERVED, null, false);

        $this->assertDatabaseCount('resource_statuses', 2);

        $slots = $scheduler->generateSlots($resource, $start, $end);
        $this->assertTrue($slots->every(fn (array $slot) => $slot['status'] === ResourceStatusEnum::RESERVED));
    }

    public function test_set_status_requires_confirmation_when_overlapping(): void
    {
        $resource = Resource::factory()->create();
        $scheduler = new ResourceScheduler;

        $start = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY)->setTime(8, 0);
        $end = $start->addHours(2);

        $scheduler->setStatus($resource, $start, $end, ResourceStatusEnum::RESERVED, null, false);

        $this->expectException(ValidationException::class);
        $scheduler->setStatus($resource, $start, $end, ResourceStatusEnum::MAINTENANCE, null, false);
    }

    public function test_set_status_allows_override_when_confirmed(): void
    {
        $resource = Resource::factory()->create();
        $scheduler = new ResourceScheduler;

        $start = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY)->setTime(8, 0);
        $end = $start->addHours(2);

        $scheduler->setStatus($resource, $start, $end, ResourceStatusEnum::RESERVED, null, false);

        $scheduler->setStatus($resource, $start, $end, ResourceStatusEnum::MAINTENANCE, null, true);

        $this->assertDatabaseHas('resource_statuses', [
            'resource_id' => $resource->id,
            'status' => ResourceStatusEnum::MAINTENANCE->value,
        ]);
    }

    public function test_status_can_be_linked_to_activity(): void
    {
        $resource = Resource::factory()->create();
        $activity = Activity::factory()->create();
        $group = Group::factory()->create();
        $user = User::factory()->create();

        $user->groups()->attach($group->id);
        $activity->groups()->attach($group->id);
        $resource->groups()->attach($group->id);
        $this->actingAs($user);
        $scheduler = new ResourceScheduler;

        $start = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY)->setTime(10, 0);
        $end = $start->addHours(2);

        $scheduler->setStatus($resource, $start, $end, ResourceStatusEnum::OCCUPIED, $activity->id, true);

        $this->assertDatabaseHas('resource_statuses', [
            'resource_id' => $resource->id,
            'activity_id' => $activity->id,
            'status' => ResourceStatusEnum::OCCUPIED->value,
        ]);

        $this->assertTrue($resource->activities()->whereKey($activity->id)->exists());
    }

    public function test_invalid_hours_throw_validation_exception(): void
    {
        $resource = Resource::factory()->create();
        $scheduler = new ResourceScheduler;

        $start = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY)->setTime(9, 30);
        $end = $start->addHours(2);

        $this->expectException(ValidationException::class);
        $scheduler->setStatus($resource, $start, $end, ResourceStatusEnum::RESERVED, null, false);
    }

    public function test_multi_day_range_creates_slots_across_days(): void
    {
        $resource = Resource::factory()->create();
        $scheduler = new ResourceScheduler;

        $start = CarbonImmutable::now()->startOfWeek(CarbonInterface::MONDAY)->setTime(20, 0);
        $end = $start->addDay()->setTime(12, 0);

        $scheduler->setStatus($resource, $start, $end, ResourceStatusEnum::RESERVED, null, true);

        $slots = $scheduler->generateSlots($resource, $start, $end);

        $this->assertCount(3, $slots);
        $this->assertTrue($slots->every(fn (array $slot) => $slot['status'] === ResourceStatusEnum::RESERVED));
    }

    public function test_range_spanning_sunday_skips_sunday(): void
    {
        $resource = Resource::factory()->create();
        $scheduler = new ResourceScheduler;

        $start = CarbonImmutable::now()->startOfWeek(CarbonInterface::SATURDAY)->setTime(20, 0);
        $end = $start->addDays(2)->setTime(12, 0); // Monday 12:00

        $scheduler->setStatus($resource, $start, $end, ResourceStatusEnum::RESERVED, null, true);

        $slots = $scheduler->generateSlots($resource, $start, $end);

        $this->assertCount(3, $slots); // Sat 20-22, Mon 08-10, 10-12
        $this->assertTrue($slots->every(fn (array $slot) => $slot['status'] === ResourceStatusEnum::RESERVED));
    }
}
