<?php

namespace Sinclair\Repository\Repositories;

use Sinclair\Repository\Contracts\Repository as RepositoryInterface;
use Sinclair\Repository\Traits\EloquentRepository;
use Sinclair\Repository\Traits\EloquentSoftDeleteRepository;
use Illuminate\Database\Eloquent\Model;
use Sinclair\Repository\Traits\Filterable;

/**
 * Class Repository
 * @package Sinclair\Repository\Repositories
 *
 * @property $model
 */
abstract class Repository implements RepositoryInterface
{
    use EloquentRepository, EloquentSoftDeleteRepository, Filterable;

    /**
     * @param Model $model
     *
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $query
     * @param string|null $orderBy
     * @param string $direction
     *
     * @return mixed
     */
    public function sort( &$query, $orderBy, $direction )
    {
        if ( !is_null($orderBy) )
        {
            $relationshipOrderBy = explode('.', $orderBy);
            if ( sizeof($relationshipOrderBy) > 1 )
            {
                $query = $query->select($this->model->getTable() . '.*')
                               ->join($this->getRelatedTable($relationshipOrderBy), $this->getRelatedForeignKey($relationshipOrderBy), '=', 'related.id')
                               ->orderBy($this->getRelatedSortColumn($relationshipOrderBy), $direction);
            }
            else
            {
                $query = $query->orderBy($orderBy, $direction);
            }
        }

        return $query;
    }

    /**
     * @param $relationshipOrderBy
     *
     * @return mixed
     */
    private function getRelation( $relationshipOrderBy )
    {
        return snake_case(array_first($relationshipOrderBy));
    }

    /**
     * @param $relationshipOrderBy
     *
     * @return mixed
     */
    private function getRelatedTable( $relationshipOrderBy )
    {
        return str_plural($this->getRelation($relationshipOrderBy)) . ' as related';
    }

    /**
     * @param $relationshipOrderBy
     *
     * @return string
     */
    private function getRelatedForeignKey( $relationshipOrderBy )
    {
        return $this->model->getTable() . '.' . str_singular($this->getRelation($relationshipOrderBy)) . '_id';
    }

    /**
     * @param $relationshipOrderBy
     *
     * @return string
     */
    private function getRelatedSortColumn( $relationshipOrderBy )
    {
        return 'related.' . array_last($relationshipOrderBy);
    }
}