<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\TaskModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskModel>
 */
final class TaskModelFactory extends Factory
{
    protected $model = TaskModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => ProjectModel::factory(),
            'creator_id' => UserModel::factory(),
            'assignee_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['todo', 'in_progress', 'done']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'due_date' => null,
        ];
    }
}
