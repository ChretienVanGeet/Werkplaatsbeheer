<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\InstructorAssignment;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<InstructorAssignment>
 */
class InstructorAssignmentFactory extends Factory
{
    protected $model = InstructorAssignment::class;

    public function definition(): array
    {
        $start = Carbon::now()->startOfWeek()->setTime(8, 0);

        return [
            'instructor_id' => Instructor::factory(),
            'resource_id' => Resource::factory(),
            'activity_id' => null,
            'load_percentage' => 100,
            'starts_at' => $start,
            'ends_at' => (clone $start)->addHours(2),
        ];
    }
}
