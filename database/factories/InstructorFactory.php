<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Instructor>
 */
class InstructorFactory extends Factory
{
    protected $model = Instructor::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
