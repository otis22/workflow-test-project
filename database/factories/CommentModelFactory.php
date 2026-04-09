<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Model\CommentModel;
use App\Infrastructure\Persistence\Eloquent\Model\TaskModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommentModel>
 */
final class CommentModelFactory extends Factory
{
    protected $model = CommentModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_id' => TaskModel::factory(),
            'author_id' => UserModel::factory(),
            'body' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}
