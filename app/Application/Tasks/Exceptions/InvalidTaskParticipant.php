<?php

namespace App\Application\Tasks\Exceptions;

use DomainException;

class InvalidTaskParticipant extends DomainException
{
    public static function creatorMustBelongToProject(): self
    {
        return new self('The task creator must belong to the project.');
    }

    public static function assigneeMustBelongToProject(): self
    {
        return new self('The task assignee must belong to the project.');
    }
}
