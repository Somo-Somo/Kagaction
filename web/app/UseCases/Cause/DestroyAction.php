<?php

namespace App\UseCases\Cause;

use App\Repositories\Cause\CauseRepositoryInterface;

class DestroyAction
{
    protected $cause_repository;

    public function __construct(CauseRepositoryInterface $causeRepositoryInterface)
    {
        $this->cause_repository = $causeRepositoryInterface;
    }

    public function invoke(array $cause)
    {

        $this->cause_repository->destroyCause($cause);

        return;
    }
}