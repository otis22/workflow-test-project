<?php

declare(strict_types=1);

namespace App\Application\Task\Exception;

use RuntimeException;

final class ProjectNotFoundException extends RuntimeException
{
    public static function forId(int $projectId): self
    {
        return new self(sprintf('Project with id %d not found', $projectId));
    }
}
