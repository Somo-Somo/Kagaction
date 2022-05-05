<?php

namespace App\UseCases\Comment;

use App\Repositories\Comment\CommentRepositoryInterface;

class StoreAction
{
    protected $comment_repository;

    public function __construct(CommentRepositoryInterface $commentRepositoryInterface)
    {
        $this->comment_repository = $commentRepositoryInterface;
    }

    public function invoke(array $comment)
    {

        $comments = $this->comment_repository->storeComment($comment);
        
        return;
    }
}