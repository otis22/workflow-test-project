<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'creator_id' => User::factory(),
            'assignee_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => Task::STATUS_TODO,
            'priority' => Task::PRIORITY_MEDIUM,
            'due_date' => fake()->optional()->dateTimeBetween('today', '+14 days'),
        ];
    }
}
