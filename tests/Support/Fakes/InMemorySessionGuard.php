<?php

declare(strict_types=1);

namespace Tests\Support\Fakes;

use App\Application\Auth\SessionGuard;

final class InMemorySessionGuard implements SessionGuard
{
    private ?int $userId = null;

    public function login(int $userId): void
    {
        $this->userId = $userId;
    }

    public function logout(): void
    {
        $this->userId = null;
    }

    public function currentUserId(): ?int
    {
        return $this->userId;
    }
}
