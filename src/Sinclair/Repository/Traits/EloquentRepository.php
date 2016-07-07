<?php

namespace Sinclair\Repository\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
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
        return $this->model->find($id);
    }

    /**
     * @param array $columns
     *
     * @param null $orderBy
     * @param string $direction
     *
     * @return Collection
     */
    public function getAll( $columns = [ '*' ], $orderBy = null, $direction = 'asc' )
    {
        $query = $orderBy == null ? $this->model->latest() : $this->sort($this->model, $orderBy, $direction);

        return $query->get($columns);
    }

    /**
     * @param int $rows
     *
     * @param null $orderBy
     * @param string $direction
     *
     * @param array $columns
     * @param $pageName
     *
     * @return LengthAwarePaginator
     */
    public function getAllPaginate( $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' )
    {
        $query = $orderBy == null ? $this->model->latest() : $this->sort($this->model, $orderBy, $direction);

        return $query->paginate($rows, $columns, $pageName);
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
        return $this->model->where('name', $name)
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

        return $this->model->firstOrCreate($attributes);
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
        foreach ( $attributes as $key => $value )
        {
            if ( !$model->isFillable($key) || str_contains($key, '_confirmation') )
            {
                unset( $attributes[ $key ] );
            }
        }

        return $attributes;
    }

    /**
     * @param $search
     *
     * @return mixed
     */
    public function search( $search )
    {
        $query = $this->model;

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
     * @param array $ids
     *
     * @return mixed
     */
    public function deleteByIds( array $ids )
    {
        return $this->model->whereIn('id', $ids)
                           ->delete();
    }
}