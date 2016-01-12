<?php

namespace Sterling\Repository\Repositories;

use Sterling\Repository\Contracts\Repository as RepositoryInterface;
use Sterling\Repository\Traits\EloquentRepository;
use Sterling\Repository\Traits\EloquentSoftDeleteRepository;
use Illuminate\Database\Eloquent\Model;

abstract class Repository implements RepositoryInterface
{
    use EloquentRepository, EloquentSoftDeleteRepository;

    /**
     * @param $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }
}