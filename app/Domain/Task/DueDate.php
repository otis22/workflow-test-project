<?php

declare(strict_types=1);

namespace App\Domain\Task;

use DateTimeImmutable;

final readonly class DueDate
{
    public function __construct(public DateTimeImmutable $value) {}

    public function isOverdue(DateTimeImmutable $now): bool
    {
        return $now > $this->value;
    }

    public function equals(self $other): bool
    {
        // Compare instants (UTC microseconds), not timezone metadata.
        return $this->value->format('U.u') === $other->value->format('U.u');
    }
}
