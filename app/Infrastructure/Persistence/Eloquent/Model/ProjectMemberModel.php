<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Database\Factories\ProjectMemberModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $user_id
 * @property Carbon $created_at
 */
final class ProjectMemberModel extends Model
{
    /** @use HasFactory<ProjectMemberModelFactory> */
    use HasFactory;

    protected $table = 'project_members';

    protected $guarded = [];

    /**
     * project_members has only created_at (domain entity is immutable).
     * created_at is populated either by the DB default (useCurrent() in
     * migration 3.1) for raw Eloquent inserts, or explicitly by the mapper
     * from a domain entity's createdAt value (subtask 3.2.3).
     */
    public $timestamps = false;

    protected $casts = [
        'project_id' => 'int',
        'user_id' => 'int',
        'created_at' => 'datetime',
    ];

    // Explicit factory binding: see UserModel for rationale.
    protected static string $factory = ProjectMemberModelFactory::class;
}
