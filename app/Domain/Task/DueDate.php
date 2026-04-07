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
        return $this->value == $other->value;
    }
}
