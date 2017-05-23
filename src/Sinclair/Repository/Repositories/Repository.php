<?php

namespace Sinclair\Repository\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Sinclair\Repository\Contracts\Repository as RepositoryInterface;
use Sinclair\Repository\Traits\EloquentRepository;
use Sinclair\Repository\Traits\EloquentSoftDeleteRepository;
use Sinclair\Repository\Traits\Filterable;

/**
 * Class Repository
 *
 * @package Sinclair\Repository\Repositories
 *
 * @property Model $model
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
     * @return Model| Builder
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $query
     * @param string|null                                   $orderBy
     * @param string                                        $direction
     *
     * @return void
     */
    public function sort(&$query, $orderBy, $direction)
    {
        if ( ! is_null($orderBy)) {
            $relationshipOrderBy = explode('.', $orderBy);

            $query = sizeof($relationshipOrderBy) > 1 ?
                $query->select($this->getModel()
                                    ->getTable() . '.*')
                      ->join($this->getRelatedTable($relationshipOrderBy), $this->getRelatedForeignKey($relationshipOrderBy), '=', 'related.id')
                      ->orderBy($this->getRelatedSortColumn($relationshipOrderBy), $direction) :
                $query->orderBy($orderBy, $direction);
        }
    }

    /**
     * @param $orderBy
     * @param $direction
     *
     * @return Builder| \Illuminate\Database\Eloquent\Builder
     */
    private function applySort($orderBy = null, $direction = null)
    {
        $query = $this->model->newQuery();

        if ( ! is_null($orderBy)) {
            $this->sort($query, $orderBy, $direction);
        }

        return $query;
    }

    /**
     * @param $relationshipOrderBy
     *
     * @return mixed
     */
    private function getRelation($relationshipOrderBy)
    {
        return snake_case(array_first($relationshipOrderBy));
    }

    /**
     * @param $relationshipOrderBy
     *
     * @return mixed
     */
    private function getRelatedTable($relationshipOrderBy)
    {
        return str_plural($this->getRelation($relationshipOrderBy)) . ' as related';
    }

    /**
     * @param $relationshipOrderBy
     *
     * @return string
     */
    private function getRelatedForeignKey($relationshipOrderBy)
    {
        return $this->getModel()
                    ->getTable() . '.' . str_singular($this->getRelation($relationshipOrderBy)) . '_id';
    }

    /**
     * @param $relationshipOrderBy
     *
     * @return string
     */
    private function getRelatedSortColumn($relationshipOrderBy)
    {
        return 'related.' . array_last($relationshipOrderBy);
    }
}