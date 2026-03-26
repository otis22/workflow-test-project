<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use App\Domain\User\Email;
use App\Domain\User\User;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function it_creates_user_with_valid_data(): void
    {
        $user = new User(
            id: 1,
            name: 'John Doe',
            email: new Email('john@example.com'),
        );

        $this->assertSame(1, $user->id);
        $this->assertSame('John Doe', $user->name);
        $this->assertSame('john@example.com', $user->email->value());
    }

    #[Test]
    public function it_rejects_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new User(
            id: 1,
            name: '',
            email: new Email('john@example.com'),
        );
    }

    #[Test]
    public function it_rejects_whitespace_only_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new User(
            id: 1,
            name: '   ',
            email: new Email('john@example.com'),
        );
    }
}
