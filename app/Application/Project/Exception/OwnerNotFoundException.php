<?php

declare(strict_types=1);

namespace App\Application\Project\Exception;

use RuntimeException;

final class OwnerNotFoundException extends RuntimeException
{
    public static function forId(int $ownerId): self
    {
        return new self(sprintf('User with id %d not found as project owner', $ownerId));
    }
}
