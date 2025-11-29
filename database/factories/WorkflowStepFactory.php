<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WorkflowStepStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkflowStep>
 */
class WorkflowStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => fake()->randomElement(WorkflowStepStatus::values()),
        ];
    }
}
