<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<resource>
 */
class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'machine_type' => $this->faker->words(asText: true),
            'description' => $this->faker->sentence(),
            'instructor_capacity' => $this->faker->randomElement([25, 50, 75, 100]),
        ];
    }
}
