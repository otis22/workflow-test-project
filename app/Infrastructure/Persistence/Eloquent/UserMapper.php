<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\User;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use DateTimeImmutable;

final class UserMapper
{
    public function toDomain(UserModel $model): User
    {
        return new User(
            id: (int) $model->id,
            name: $model->name,
            email: $model->email,
            passwordHash: $model->password_hash,
            createdAt: DateTimeImmutable::createFromInterface($model->created_at),
            updatedAt: DateTimeImmutable::createFromInterface($model->updated_at),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toRow(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'password_hash' => $user->passwordHash,
            'created_at' => $user->createdAt,
            'updated_at' => $user->updatedAt,
        ];
    }
}
