<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Application\Auth\Exception\InvalidCredentialsException;
use App\Application\Hashing\PasswordHasher;
use App\Domain\User\User;
use App\Domain\User\UserRepository;

final readonly class Login
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private SessionGuard $session,
    ) {}

    public function execute(string $email, string $plainPassword): User
    {
        $user = $this->users->findByEmail($this->normalizeEmail($email));

        if (! $user instanceof User) {
            throw InvalidCredentialsException::create();
        }

        if (! $this->hasher->verify($plainPassword, $user->passwordHash)) {
            throw InvalidCredentialsException::create();
        }

        $this->session->login($user->id);

        return $user;
    }

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}
