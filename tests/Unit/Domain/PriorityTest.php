<?php

declare(strict_types=1);

use App\Domain\Task\Priority;

it('exposes the three MVP priority cases as backed string enum', function (): void {
    expect(Priority::Low->value)->toBe('low')
        ->and(Priority::Medium->value)->toBe('medium')
        ->and(Priority::High->value)->toBe('high');
});

it('builds from string', function (): void {
    expect(Priority::from('low'))->toBe(Priority::Low)
        ->and(Priority::from('medium'))->toBe(Priority::Medium)
        ->and(Priority::from('high'))->toBe(Priority::High);
});

it('rejects unknown string', function (): void {
    Priority::from('urgent');
})->throws(ValueError::class);
