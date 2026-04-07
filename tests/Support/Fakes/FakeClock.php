<?php

declare(strict_types=1);

namespace Tests\Support\Fakes;

use App\Application\Clock\Clock;
use DateTimeImmutable;

final class FakeClock implements Clock
{
    public function __construct(private DateTimeImmutable $now = new DateTimeImmutable('2026-01-01T00:00:00Z')) {}

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function set(DateTimeImmutable $now): void
    {
        $this->now = $now;
    }
}
