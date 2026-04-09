<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Infrastructure\Persistence\Eloquent\Model\UserModel;

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
        $model = $user->id > 0
            ? (UserModel::query()->find($user->id) ?? new UserModel)
            : new UserModel;

        $model->fill($this->mapper->toRow($user));
        $model->save();

        return $this->mapper->toDomain($model->refresh());
    }
}
