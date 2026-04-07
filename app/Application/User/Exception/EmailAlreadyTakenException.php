<?php

declare(strict_types=1);

namespace App\Application\User\Exception;

use RuntimeException;

final class EmailAlreadyTakenException extends RuntimeException
{
    public static function forEmail(string $email): self
    {
        return new self(sprintf('Email "%s" is already taken', $email));
    }
}
