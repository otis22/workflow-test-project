<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Model;

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
    protected $table = 'project_members';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'project_id' => 'int',
        'user_id' => 'int',
        'created_at' => 'datetime',
    ];
}
