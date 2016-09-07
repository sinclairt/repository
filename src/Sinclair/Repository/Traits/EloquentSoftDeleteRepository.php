<?php

namespace Sinclair\Repository\Traits;

use Carbon\Carbon;
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

        $orderBy == null ? $query = $query->latest() : $this->sort($query, $orderBy, $direction);

        return $query->get($columns);
    }

    /**
     * @param int $rows
     * @param null $orderBy
     * @param string $direction
     * @param array $columns
     * @param string $pageName
     *
     * @return LengthAwarePaginator
     */
    public function getAllPaginatedWithTrashed( $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' )
    {
        $query = $this->model->withTrashed();

        $orderBy == null ? $query = $query->latest() : $this->sort($query, $orderBy, $direction);

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
     * @param string $callback
     *
     * @return array
     */
    public function getArrayForSelectWithTrashed( $value = 'name', $key = 'id', $callback = 'ucwords' )
    {
        $data = $this->getAllWithTrashed([ '*' ], $value)
                     ->pluck($value, $key)
                     ->toArray();

        if ( !is_null($callback) )
            array_walk($data, function ( &$item ) use ( $callback )
            {
                $item = call_user_func_array($callback, [ $item ]);
            });

        return $data;
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @param string $ts
     * @param array $columns
     * @param null $orderBy
     * @param string $direction
     *
     * @return Collection
     */
    public function getDateBetweenWithTrashed( Carbon $from = null, Carbon $to = null, $ts = 'created_at', $columns = [ '*' ], $orderBy = null, $direction = 'asc' )
    {
        $from = is_null($from) ? Carbon::now()
                                       ->subDay() : $from;

        $to = is_null($to) ? Carbon::now() : $to;

        return $this->applySort($orderBy, $direction)
                    ->withTrashed()
                    ->whereBetween($ts, [ $from->toDateTimeString(), $to->toDateTimeString() ])
                    ->get($columns);
    }

    /**
     * @param Carbon|null $from
     * @param Carbon|null $to
     * @param string $ts
     * @param int $rows
     * @param null $orderBy
     * @param string $direction
     * @param array $columns
     * @param string $pageName
     *
     * @return LengthAwarePaginator
     */
    public function getDateBetweenPaginatedWithTrashed( Carbon $from = null, Carbon $to = null, $ts = 'created_at', $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' )
    {
        $from = is_null($from) ? Carbon::now()
                                       ->subDay() : $from;

        $to = is_null($to) ? Carbon::now() : $to;

        return $this->applySort($orderBy, $direction)
                    ->withTrashed()
                    ->whereBetween($ts, [ $from->toDateTimeString(), $to->toDateTimeString() ])
                    ->paginate($rows, $columns, $pageName);
    }
}