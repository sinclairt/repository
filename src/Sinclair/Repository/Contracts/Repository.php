<?php

namespace Sinclair\Repository\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Sinclair\Repository\Traits\Filterable;
use Illuminate\Http\Request;

/**
 * Interface Repository
 * @package Sinclair\Repository\Contracts
 */
interface Repository
{
    /**
     * @param $query
     * @param string|null $orderBy
     * @param string $direction
     *
     * @return mixed
     */
    public function sort( &$query, $orderBy, $direction );

    /**
     * @param int $id
     *
     * @return Model
     */
    public function getById( $id );

    /**
     * @param array $columns
     * @param null $orderBy
     * @param string $direction
     *
     * @return Collection
     */
    public function getAll( $columns = [ '*' ], $orderBy = null, $direction = 'asc' );

    /**
     * @param int $rows
     * @param null $orderBy
     * @param string $direction
     * @param array $columns
     * @param string $pageName
     *
     * @return LengthAwarePaginator
     */
    public function getAllPaginated( $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' );

    /**
     * @param int $rows
     * @param null $orderBy
     * @param string $direction
     * @param array $columns
     * @param string $pageName
     *
     * @return LengthAwarePaginator
     */
    public function getAllPaginate( $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' );

    /**
     * @param array $attributes
     *
     * @return Model
     */
    public function add( $attributes );

    /**
     * @param string $name
     *
     * @return Model|null mixed
     */
    public function getByName( $name );

    /**
     * @param array $attributes
     * @param Model $model
     *
     * @return Model
     */
    public function update( $attributes, $model );

    /**
     * @param Model $model
     *
     * @return bool
     */
    public function destroy( $model );

    /**
     * @param array $attributes
     * @param Model|null $model
     *
     * @return Model
     */
    public function save( $attributes, $model = null );

    /**
     * @param array $attributes
     *
     * @return Model mixed
     */
    public function firstOrCreate( $attributes );

    /**
     * @param string $value
     * @param string $key
     * @param string $callback
     *
     * @return array
     */
    public function getArrayForSelect( $value = 'name', $key = 'id', $callback = 'ucwords' );

    /**
     * @param string $value
     * @param string $key
     * @param string $callback
     *
     * @return array
     */
    public function getArrayForSelectWithTrashed( $value = 'name', $key = 'id', $callback = 'ucwords' );

    /**
     * @param array $attributes
     * @param Model $model
     *
     * @return array
     */
    public function onlyFillable( $attributes, $model );

    /**
     * @param int $id
     *
     * @return Model
     */
    public function getByIdWithTrashed( $id );

    /**
     * @param array $columns
     * @param null $orderBy
     * @param string $direction
     *
     * @return Collection
     */
    public function getAllWithTrashed( $columns = [ '*' ], $orderBy = null, $direction = 'asc' );

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
    public function getAllPaginatedWithTrashed( $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' );

    /**
     * @param Model $model
     *
     * @return Model
     */
    public function restore( $model );

    /**
     * @param string $search
     *
     * @return LengthAwarePaginator
     */
    public function search( $search );

    /**
     * @param string $search
     *
     * @return LengthAwarePaginator
     */
    public function searchWithTrashed( $search );

    /**
     * @param Request $request
     * @param string|null $orderBy
     * @param string $direction
     * @param array $columns
     *
     * @param bool $search
     *
     * @return Collection
     */
    public function filter( Request $request, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $search = true );

    /**
     * @param Request $request
     * @param int $rows
     * @param string|null $orderBy
     * @param string $direction
     * @param array $columns
     * @param string $paginationName
     *
     * @param bool $search
     *
     * @return LengthAwarePaginator
     */
    public function filterPaginated( Request $request, $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $paginationName = 'page', $search = true );

    /**
     * @param Builder|\Illuminate\Database\Query\Builder $query
     *
     * @return Filterable
     */
    public function setQuery( $query );

    /**
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function getQuery();

    /**
     * @param Model $model
     *
     * @return $this
     */
    public function setModel( Model $model );

    /**
     * @return Model
     */
    public function getModel();

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
    public function getDateBetween( Carbon $from = null, Carbon $to = null, $ts = 'created_at', $columns = [ '*' ], $orderBy = null, $direction = 'asc' );

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
    public function getDateBetweenPaginated( Carbon $from = null, Carbon $to = null, $ts = 'created_at', $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' );

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
    public function getDateBetweenWithTrashed( Carbon $from = null, Carbon $to = null, $ts = 'created_at', $columns = [ '*' ], $orderBy = null, $direction = 'asc' );

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
    public function getDateBetweenPaginatedWithTrashed( Carbon $from = null, Carbon $to = null, $ts = 'created_at', $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page' );
}