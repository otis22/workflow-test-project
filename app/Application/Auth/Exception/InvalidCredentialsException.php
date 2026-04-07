<?php

declare(strict_types=1);

namespace App\Application\Auth\Exception;

use RuntimeException;

final class InvalidCredentialsException extends RuntimeException
{
    public static function create(): self
    {
        return new self('Invalid credentials');
    }
}
