<?php

declare(strict_types=1);

namespace App\Infrastructure\Clock;

use App\Application\Clock\Clock;
use DateTimeImmutable;

final readonly class SystemClock implements Clock
{
    #[\Override]
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable;
    }
}
