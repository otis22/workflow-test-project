<?php

declare(strict_types=1);

namespace App\Application\Task\Exception;

use RuntimeException;

final class NotAProjectMemberException extends RuntimeException
{
    public static function forUserAndProject(int $userId, int $projectId): self
    {
        return new self(sprintf('User %d is not a member of project %d', $userId, $projectId));
    }
}
