<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Database\Factories\TaskModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property int $creator_id
 * @property int|null $assignee_id
 * @property string $title
 * @property string $description
 * @property string $status
 * @property string $priority
 * @property Carbon|null $due_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class TaskModel extends Model
{
    /** @use HasFactory<TaskModelFactory> */
    use HasFactory;

    protected $table = 'tasks';

    protected $guarded = [];

    protected $casts = [
        'project_id' => 'int',
        'creator_id' => 'int',
        'assignee_id' => 'int',
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Explicit factory binding: see UserModel for rationale.
    protected static string $factory = TaskModelFactory::class;
}
