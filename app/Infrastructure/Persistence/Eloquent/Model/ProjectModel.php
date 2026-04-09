<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Model;

use Database\Factories\ProjectModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    /** @use HasFactory<ProjectModelFactory> */
    use HasFactory;

    protected $table = 'projects';

    protected $guarded = [];

    protected $casts = [
        'owner_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Explicit factory binding: see UserModel for rationale.
    protected static string $factory = ProjectModelFactory::class;
}
