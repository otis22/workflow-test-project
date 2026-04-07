<?php

declare(strict_types=1);

namespace App\Application\Auth;

final readonly class Logout
{
    public function __construct(private SessionGuard $session) {}

    public function execute(): void
    {
        $this->session->logout();
    }
}
