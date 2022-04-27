<?php

namespace App\UseCases\Comment;

use App\Repositories\Comment\CommentRepositoryInterface;

class DestroyAction
{
    protected $comment_repository;

    public function __construct(CommentRepositoryInterface $commentRepositoryInterface)
    {
        $this->comment_repository = $commentRepositoryInterface;
    }

    public function invoke(array $hypothesis)
    {

        $this->comment_repository->destroyComment($hypothesis);
        
        return; 
    }
}