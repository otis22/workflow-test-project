<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Shared\DomainRuleViolation;

final readonly class User
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $passwordHash,
    ) {
        if (trim($this->id) === '') {
            throw new DomainRuleViolation('User id is required.');
        }

        if (trim($this->name) === '') {
            throw new DomainRuleViolation('User name is required.');
        }

        if (trim($this->email) === '') {
            throw new DomainRuleViolation('User email is required.');
        }

        if (trim($this->passwordHash) === '') {
            throw new DomainRuleViolation('User password hash is required.');
        }
    }
}
