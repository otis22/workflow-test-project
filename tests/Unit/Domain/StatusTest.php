<?php

declare(strict_types=1);

use App\Domain\Task\Status;

it('exposes the three MVP status cases as backed string enum', function (): void {
    expect(Status::Todo->value)->toBe('todo')
        ->and(Status::InProgress->value)->toBe('in_progress')
        ->and(Status::Done->value)->toBe('done');
});

it('builds from string', function (): void {
    expect(Status::from('todo'))->toBe(Status::Todo)
        ->and(Status::from('in_progress'))->toBe(Status::InProgress)
        ->and(Status::from('done'))->toBe(Status::Done);
});

it('rejects unknown string', function (): void {
    Status::from('archived');
})->throws(ValueError::class);
