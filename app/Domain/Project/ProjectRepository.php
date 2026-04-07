<?php

declare(strict_types=1);

namespace App\Domain\Project;

interface ProjectRepository
{
    public function findById(int $id): ?Project;

    public function save(Project $project): Project;
}
