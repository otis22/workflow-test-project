<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\Clock\Clock;
use App\Application\Hashing\PasswordHasher;
use App\Application\User\Exception\EmailAlreadyTakenException;
use App\Application\User\Exception\WeakPasswordException;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use InvalidArgumentException;

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

        $email = $this->normalizeEmail($email);

        // Pre-validate domain invariants before touching the repository or hashing.
        // The domain User constructor re-validates as the ultimate guarantee.
        if (trim($name) === '') {
            throw new InvalidArgumentException('User name must not be empty');
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('User email is not valid');
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

    private function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}
