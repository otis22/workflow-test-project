<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Model;

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
    protected $table = 'comments';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'task_id' => 'int',
        'author_id' => 'int',
        'created_at' => 'datetime',
    ];
}
