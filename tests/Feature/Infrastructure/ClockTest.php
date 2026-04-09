<?php

declare(strict_types=1);

use App\Application\Clock\Clock;

it('SystemClock::now returns a DateTimeImmutable close to current time', function (): void {
    /** @var Clock $clock */
    $clock = app(Clock::class);

    $before = new DateTimeImmutable;
    $now = $clock->now();
    $after = new DateTimeImmutable;

    expect($now)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($now >= $before)->toBeTrue()
        ->and($now <= $after)->toBeTrue();
});
