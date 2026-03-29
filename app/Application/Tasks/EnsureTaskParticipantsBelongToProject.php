<?php

namespace App\Application\Tasks;

use App\Application\Tasks\Exceptions\InvalidTaskParticipant;
use App\Models\Project;
use App\Models\User;

class EnsureTaskParticipantsBelongToProject
{
    public function __invoke(Project $project, User $creator, ?User $assignee = null): void
    {
        if (! $project->members()->whereKey($creator->id)->exists()) {
            throw InvalidTaskParticipant::creatorMustBelongToProject();
        }

        if ($assignee instanceof User && ! $project->members()->whereKey($assignee->id)->exists()) {
            throw InvalidTaskParticipant::assigneeMustBelongToProject();
        }
    }
}
