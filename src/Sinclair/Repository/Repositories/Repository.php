<?php

namespace Sinclair\Repository\Repositories;

use Sinclair\Repository\Contracts\Repository as RepositoryInterface;
use Sinclair\Repository\Traits\EloquentRepository;
use Sinclair\Repository\Traits\EloquentSoftDeleteRepository;
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