<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ResourceStatus as ResourceStatusEnum;
use App\Models\Activity;
use App\Models\Resource;
use App\Models\ResourceStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ResourceStatus>
 */
class ResourceStatusFactory extends Factory
{
    protected $model = ResourceStatus::class;

    public function definition(): array
    {
        $start = CarbonImmutable::now()->startOfWeek()->addHours(8);

        return [
            'resource_id' => Resource::factory(),
            'activity_id' => Activity::factory(),
            'status' => ResourceStatusEnum::RESERVED,
            'starts_at' => $start,
            'ends_at' => $start->addHours(2),
        ];
    }
}
