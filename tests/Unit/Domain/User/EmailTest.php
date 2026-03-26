<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use App\Domain\User\Email;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    #[Test]
    public function it_creates_valid_email(): void
    {
        $email = new Email('user@example.com');

        $this->assertSame('user@example.com', $email->value());
    }

    #[Test]
    public function it_rejects_invalid_email(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('not-an-email');
    }

    #[Test]
    public function it_rejects_empty_email(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('');
    }

    #[Test]
    public function it_trims_whitespace(): void
    {
        $email = new Email('  user@example.com  ');

        $this->assertSame('user@example.com', $email->value());
    }

    #[Test]
    public function it_compares_equality(): void
    {
        $email1 = new Email('user@example.com');
        $email2 = new Email('user@example.com');
        $email3 = new Email('other@example.com');

        $this->assertTrue($email1->equals($email2));
        $this->assertFalse($email1->equals($email3));
    }
}
