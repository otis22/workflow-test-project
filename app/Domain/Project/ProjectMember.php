<?php

declare(strict_types=1);

namespace App\Domain\Project;

use App\Domain\Shared\DomainRuleViolation;

final readonly class ProjectMember
{
    public function __construct(
        public string $projectId,
        public string $userId,
    ) {
        if (trim($this->projectId) === '') {
            throw new DomainRuleViolation('Project member requires a project id.');
        }

        if (trim($this->userId) === '') {
            throw new DomainRuleViolation('Project member requires a user id.');
        }
    }
}
