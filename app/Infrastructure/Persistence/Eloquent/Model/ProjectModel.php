<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string $description
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class ProjectModel extends Model
{
    protected $table = 'projects';

    protected $guarded = [];

    protected $casts = [
        'owner_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
