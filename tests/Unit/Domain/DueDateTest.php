<?php

declare(strict_types=1);

use App\Domain\Task\DueDate;

it('wraps a DateTimeImmutable', function (): void {
    $when = new DateTimeImmutable('2026-06-01T12:00:00Z');
    $due = new DueDate($when);

    expect($due->value)->toEqual($when);
});

it('reports overdue when now is strictly after the due date', function (): void {
    $due = new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z'));
    $later = new DateTimeImmutable('2026-06-02T00:00:00Z');

    expect($due->isOverdue($later))->toBeTrue();
});

it('reports not overdue when now is exactly the due date', function (): void {
    $when = new DateTimeImmutable('2026-06-01T00:00:00Z');
    $due = new DueDate($when);

    expect($due->isOverdue($when))->toBeFalse();
});

it('reports not overdue when now is before the due date', function (): void {
    $due = new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z'));
    $earlier = new DateTimeImmutable('2026-05-31T23:59:59Z');

    expect($due->isOverdue($earlier))->toBeFalse();
});

it('two DueDates with the same instant are equal', function (): void {
    $a = new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z'));
    $b = new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z'));

    expect($a->equals($b))->toBeTrue();
});

it('two DueDates with different instants are not equal', function (): void {
    $a = new DueDate(new DateTimeImmutable('2026-06-01T00:00:00Z'));
    $b = new DueDate(new DateTimeImmutable('2026-06-02T00:00:00Z'));

    expect($a->equals($b))->toBeFalse();
});

it('two DueDates representing the same instant in different timezones are equal', function (): void {
    $utc = new DueDate(new DateTimeImmutable('2026-06-01T12:00:00+00:00'));
    $msk = new DueDate(new DateTimeImmutable('2026-06-01T15:00:00+03:00'));

    expect($utc->equals($msk))->toBeTrue();
});
