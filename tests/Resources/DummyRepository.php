<?php

use Sinclair\Repository\Repositories\Repository;

class DummyRepository extends Repository implements \Sinclair\Repository\Contracts\Repository
{
    public function __construct( DummyModel $model )
    {
        $this->model = $model;
    }
}