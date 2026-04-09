<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectModel>
 */
final class ProjectModelFactory extends Factory
{
    protected $model = ProjectModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => UserModel::factory(),
            'name' => fake()->company(),
            'description' => fake()->sentence(),
        ];
    }
}
