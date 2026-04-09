<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Infrastructure\Persistence\Eloquent\Model\ProjectMemberModel;
use App\Infrastructure\Persistence\Eloquent\Model\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectMemberModel>
 */
final class ProjectMemberModelFactory extends Factory
{
    protected $model = ProjectMemberModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => ProjectModel::factory(),
            'user_id' => UserModel::factory(),
            'created_at' => now(),
        ];
    }
}
