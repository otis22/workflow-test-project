<?php

namespace App\Application\Comments\Exceptions;

use DomainException;

class InvalidCommentAuthor extends DomainException
{
    public static function mustBelongToProject(): self
    {
        return new self('The comment author must belong to the project.');
    }
}
