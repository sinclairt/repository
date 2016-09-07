<?php

namespace Sinclair\Repository\Traits;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EloquentRepository
 * @package Sinclair\Repository\Traits
 */
trait EloquentRepository
{
    /**
     * @param $id
     *
     * @return Model
     */
    public function getById( $id )
    {
        return $this->getModel()
                    ->find($id);
    }

    /**
     * @param array $columns
     * @param null $orderBy
     * @param string $direction
     *
     * @return Collection
     */
    public function getAll( $columns = [ '*' ], $orderBy = null, $direction = 'asc' )
    {
        return $this->applySort($orderBy, $direction)
                    ->get($columns);
    }

    /**
     * @param int $rows
     * @param null $orderBy
     * @param string $direction
     * @param array $columns
     * @param string $pageName
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated( $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' )
    {
        return $this->getAllPaginate($rows, $orderBy, $direction, $columns, $pageName);
    }

    /**
     * @param int $rows
     * @param null $orderBy
     * @param string $direction
     * @param array $columns
     * @param $pageName
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginate( $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' )
    {
        return $this->applySort($orderBy, $direction)
                    ->paginate($rows, $columns, $pageName);
    }

    /**
     * @param array $attributes
     *
     * @return Model
     */
    public function add( $attributes )
    {
        return $this->save($attributes);
    }

    /**
     * @param $name
     *
     * @return Model
     */
    public function getByName( $name )
    {
        return $this->getModel()->where('name', $name)
                           ->first();
    }

    /**
     * @param array $attributes
     * @param Model $model
     *
     * @return Model
     */
    public function update( $attributes, $model )
    {
        return $this->save($attributes, $model);
    }

    /**
     * @param $model
     *
     * @return bool
     */
    public function destroy( $model )
    {
        return $model->delete();
    }

    /**
     * @param array $attributes
     * @param null $model
     *
     * @return Model
     */
    public function save( $attributes, $model = null )
    {
        $model = is_null($model) ? new $this->model : $model;

        $attributes = $this->onlyFillable($attributes, $model);

        $model->fill($attributes)
              ->save();

        return $model;
    }

    /**
     * @param array $attributes
     *
     * @return Model
     */
    public function firstOrCreate( $attributes )
    {
        $attributes = $this->onlyFillable($attributes, $this->model);

        return $this->getModel()->firstOrCreate($attributes);
    }

    /**
     * @param string $value
     * @param string $key
     *
     * @param string $callback
     *
     * @return array
     */
    public function getArrayForSelect( $value = 'name', $key = 'id', $callback = 'ucwords' )
    {
        $data = $this->getAll()
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
     * @param array $attributes
     * @param Model $model
     *
     * @return array
     */
    public function onlyFillable( $attributes, $model )
    {
        return array_filter($attributes, [ $model, 'isFillable' ], ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param $search
     *
     * @return mixed
     */
    public function search( $search )
    {
        $query = $this->getModel()->where(function ( $q ) use ( $search )
        {
            $fields = array_diff($this->getModel()->getFillable(), $this->getModel()->getHidden());

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
     * @param array $ids
     *
     * @return mixed
     */
    public function deleteByIds( array $ids )
    {
        return $this->getModel()->whereIn('id', $ids)
                           ->delete();
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
    public function getDateBetween( Carbon $from = null, Carbon $to = null, $ts = 'created_at', $columns = [ '*' ], $orderBy = null, $direction = 'asc' )
    {
        $from = is_null($from) ? Carbon::now()
                                       ->subDay() : $from;

        $to = is_null($to) ? Carbon::now() : $to;

        return $this->applySort($orderBy, $direction)
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
    public function getDateBetweenPaginated( Carbon $from = null, Carbon $to = null, $ts = 'created_at', $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' )
    {
        $from = is_null($from) ? Carbon::now()
                                       ->subDay() : $from;

        $to = is_null($to) ? Carbon::now() : $to;

        return $this->applySort($orderBy, $direction)
                    ->whereBetween($ts, [ $from->toDateTimeString(), $to->toDateTimeString() ])
                    ->paginate($rows, $columns, $pageName);
    }
}