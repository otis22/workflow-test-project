<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\Clock\Clock;
use App\Application\Hashing\PasswordHasher;
use App\Application\User\Exception\EmailAlreadyTakenException;
use App\Application\User\Exception\WeakPasswordException;
use App\Domain\User\User;
use App\Domain\User\UserRepository;

final readonly class RegisterUser
{
    public const int MIN_PASSWORD_LENGTH = 8;

    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private Clock $clock,
    ) {}

    public function execute(string $name, string $email, string $plainPassword): User
    {
        if (strlen($plainPassword) < self::MIN_PASSWORD_LENGTH) {
            throw WeakPasswordException::tooShort(self::MIN_PASSWORD_LENGTH);
        }

        if ($this->users->findByEmail($email) instanceof User) {
            throw EmailAlreadyTakenException::forEmail($email);
        }

        $now = $this->clock->now();

        $user = new User(
            id: 0,
            name: $name,
            email: $email,
            passwordHash: $this->hasher->hash($plainPassword),
            createdAt: $now,
            updatedAt: $now,
        );

        return $this->users->save($user);
    }
}
