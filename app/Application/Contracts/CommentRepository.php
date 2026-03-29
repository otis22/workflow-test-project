<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use App\Domain\Comment\Comment;

interface CommentRepository
{
    public function save(Comment $comment): void;
}
