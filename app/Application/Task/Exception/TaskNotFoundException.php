<?php

declare(strict_types=1);

namespace App\Application\Task\Exception;

use RuntimeException;

final class TaskNotFoundException extends RuntimeException
{
    public static function forId(int $taskId): self
    {
        return new self(sprintf('Task with id %d not found', $taskId));
    }
}
