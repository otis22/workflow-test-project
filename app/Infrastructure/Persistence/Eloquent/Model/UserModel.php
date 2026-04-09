<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Database\Factories\UserModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class UserModel extends Model
{
    /** @use HasFactory<UserModelFactory> */
    use HasFactory;

    protected $table = 'users';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static string $factory = UserModelFactory::class;
}
