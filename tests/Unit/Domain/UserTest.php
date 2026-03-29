<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Domain\Shared\DomainRuleViolation;
use App\Domain\User\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_user_requires_core_account_fields(): void
    {
        $this->expectException(DomainRuleViolation::class);

        new User(
            id: '',
            name: 'Alice',
            email: 'alice@example.com',
            passwordHash: 'hash',
        );
    }

    public function test_user_can_be_created_with_valid_account_fields(): void
    {
        $user = new User(
            id: 'user-1',
            name: 'Alice',
            email: 'alice@example.com',
            passwordHash: 'hash',
        );

        $this->assertSame('user-1', $user->id);
        $this->assertSame('Alice', $user->name);
        $this->assertSame('alice@example.com', $user->email);
    }
}
