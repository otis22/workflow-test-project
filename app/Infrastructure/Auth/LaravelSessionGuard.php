<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Application\Auth\SessionGuard;
use Illuminate\Contracts\Session\Session;

final readonly class LaravelSessionGuard implements SessionGuard
{
    private const string SESSION_KEY = 'auth.user_id';

    public function __construct(private Session $session) {}

    #[\Override]
    public function login(int $userId): void
    {
        $this->session->put(self::SESSION_KEY, $userId);
        // Regenerate session id on login to prevent session fixation.
        $this->session->migrate(true);
    }

    #[\Override]
    public function logout(): void
    {
        $this->session->forget(self::SESSION_KEY);
        $this->session->invalidate();
    }

    #[\Override]
    public function currentUserId(): ?int
    {
        $value = $this->session->get(self::SESSION_KEY);

        return is_int($value) ? $value : null;
    }
}
