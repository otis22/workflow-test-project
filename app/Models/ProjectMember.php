<?php

namespace App\Models;

use Database\Factories\ProjectMemberFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    /** @use HasFactory<ProjectMemberFactory> */
    use HasFactory;

    public $timestamps = true;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'updated_at',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
