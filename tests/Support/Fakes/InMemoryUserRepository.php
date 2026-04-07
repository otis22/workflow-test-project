<?php

declare(strict_types=1);

namespace Tests\Support\Fakes;

use App\Domain\User\User;
use App\Domain\User\UserRepository;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<int, User> */
    private array $users = [];

    private int $nextId = 1;

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }

        return null;
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function save(User $user): User
    {
        $id = $user->id > 0 ? $user->id : $this->nextId++;
        $stored = new User(
            id: $id,
            name: $user->name,
            email: $user->email,
            passwordHash: $user->passwordHash,
            createdAt: $user->createdAt,
            updatedAt: $user->updatedAt,
        );
        $this->users[$id] = $stored;

        return $stored;
    }
}
