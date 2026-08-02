<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id'       => Project::factory(),
            'title'            => $this->faker->sentence(4),
            'description'      => $this->faker->paragraph(),
            'priority'         => $this->faker->randomElement(TaskPriority::cases())->value,
            'status'           => $this->faker->randomElement(TaskStatus::cases())->value,
            'due_date'         => $this->faker->dateTimeBetween('-1 month', '+2 months'),
            'overdue_notified' => false,
        ];
    }
}
