<?php

namespace Sinclair\Repository\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EloquentSoftDeleteRepository
 * @package Sinclair\Repository\Traits
 */
trait EloquentSoftDeleteRepository
{
    /**
     * @param int $id
     *
     * @return Model
     */
    public function getByIdWithTrashed( $id )
    {
        return $this->model->withTrashed()
                           ->find($id);
    }

    /**
     * @param array $columns
     * @param null $orderBy
     * @param string $direction
     *
     * @return Collection
     */
    public function getAllWithTrashed( $columns = [ '*' ], $orderBy = null, $direction = 'asc' )
    {
        $query = $this->model->withTrashed();

        $query = $orderBy == null ? $query->latest() : $this->sort($query, $orderBy, $direction);

        return $query->get($columns);
    }

    /**
     * @param int $rows
     * @param null $orderBy
     * @param string $direction
     *
     * @param array $columns
     * @param string $pageName
     *
     * @return LengthAwarePaginator
     */
    public function getAllPaginatedWithTrashed( $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' )
    {
        $query = $this->model->withTrashed();

        $query = $orderBy == null ? $query->latest() : $this->sort($query, $orderBy, $direction);

        return $query->paginate($rows, $columns, $pageName);
    }

    /**
     * @param $model
     *
     * @return Model
     */
    public function restore( $model )
    {
        $model->restore();

        return $model;
    }

    /**
     * @param $search
     *
     * @return LengthAwarePaginator
     */
    public function searchWithTrashed( $search )
    {
        $query = $this->model->withTrashed();

        $query = $query->where(function ( $q ) use ( $search )
        {
            $fields = array_diff($this->model->getFillable(), $this->model->getHidden());

            $first = true;

            foreach ( $fields as $field )
            {
                $q = $first ? $q->where($field, 'like', '%' . $search . '%') : $q->orWhere($field, 'like', '%' . $search . '%');

                $first = false;
            }
        });

        return $query->paginate();
    }

    /**
     * @param string $value
     * @param string $key
     *
     * @return array
     */
    public function getArrayForSelectWithTrashed( $value = 'name', $key = 'id' )
    {
        return $this->getAllWithTrashed([ '*' ], $value)
                    ->lists($value, $key)
                    ->toArray();
    }
}