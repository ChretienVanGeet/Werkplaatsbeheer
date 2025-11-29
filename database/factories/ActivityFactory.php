<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ActivityStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $betweenStart = now()->addDays(2);
        $betweenEnd = now()->addWeeks(2)->endOfWeek();

        $startDate = fake()->dateTimeBetween($betweenStart, $betweenEnd);
        $endDate = fake()->dateTimeBetween($startDate, $betweenEnd);

        return [
            'name'       => $this->getFakeName(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date'   => $endDate->format('Y-m-d'),
            'status'     => fake()->randomElement(ActivityStatus::values()),
        ];
    }

    private function getFakeName(): string
    {
        $eventPrefixes = ['Summit', 'Expo', 'Forum', 'Workshop', 'Meetup', 'Symposium', 'Conference'];
        $topics = ['AI', 'Climate Tech', 'E-Commerce', 'FinTech', 'Laravel', 'Marketing'];

        return fake()->randomElement($topics) . ' ' . fake()->randomElement($eventPrefixes);
    }

}
