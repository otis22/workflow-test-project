<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Project\Project;

interface ProjectRepository
{
    public function save(Project $project): void;

    public function getById(string $projectId): ?Project;
}
