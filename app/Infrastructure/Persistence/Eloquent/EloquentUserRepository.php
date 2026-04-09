<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;
use RuntimeException;

final readonly class EloquentUserRepository implements UserRepository
{
    public function __construct(private UserMapper $mapper) {}

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::query()->where('email', $email)->first();

        return $model instanceof UserModel ? $this->mapper->toDomain($model) : null;
    }

    public function findById(int $id): ?User
    {
        $model = UserModel::query()->find($id);

        return $model instanceof UserModel ? $this->mapper->toDomain($model) : null;
    }

    public function save(User $user): User
    {
        $model = $this->resolveModelForSave($user->id);

        $model->fill($this->mapper->toRow($user));
        $model->save();

        return $this->mapper->toDomain($model->refresh());
    }

    private function resolveModelForSave(int $id): UserModel
    {
        if ($id <= 0) {
            return new UserModel;
        }

        $existing = UserModel::query()->find($id);
        if (! $existing instanceof UserModel) {
            throw new RuntimeException(sprintf('User with id %d not found for update', $id));
        }

        return $existing;
    }
}
