<?php

declare(strict_types=1);

namespace App\Application\User\Exception;

use RuntimeException;

final class WeakPasswordException extends RuntimeException
{
    public static function tooShort(int $minLength): self
    {
        return new self(sprintf('Password must be at least %d characters', $minLength));
    }
}
