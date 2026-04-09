<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Database\Factories\CommentModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $task_id
 * @property int $author_id
 * @property string $body
 * @property Carbon $created_at
 */
final class CommentModel extends Model
{
    /** @use HasFactory<CommentModelFactory> */
    use HasFactory;

    protected $table = 'comments';

    protected $guarded = [];

    /**
     * comments has only created_at (domain entity is immutable per
     * AssumptionLog 1.5). created_at is populated either by the DB default
     * (useCurrent() in migration 3.1) for raw Eloquent inserts, or
     * explicitly by the mapper from a domain entity's createdAt value
     * (subtask 3.2.6).
     */
    public $timestamps = false;

    protected $casts = [
        'task_id' => 'int',
        'author_id' => 'int',
        'created_at' => 'datetime',
    ];

    // Explicit factory binding: see UserModel for rationale.
    protected static string $factory = CommentModelFactory::class;
}
