<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'      => fake()->company(),
            'industry'  => fake()->randomElement([__('Technology'),__('Healthcare'), __('Construction'), __('Retail'), __('Automotive industry')]),
            'comments'  => fake()->text(100),
            'locations' => fake()->address(),
        ];
    }
}
