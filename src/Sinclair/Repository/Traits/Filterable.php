<?php

namespace Sinclair\Repository\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Filterable
 * @package Sinclair\Repository\Traits
 */
trait Filterable
{
    /**
     * @var Builder
     */
    public $query;

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
    public function filter( Request $request, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $search = true )
    {
        $this->setQuery($request->input('trashed') == 1 ? $this->getModel()
                                                               ->withTrashed() : $this->getModel());

        $this->applyFilters($request, $search);

        $query = $this->query;

        $this->sort($query, $orderBy, $direction);

        return $query->get($columns);
    }

    /**
     * @param Request $request
     * @param int $rows
     * @param string|null $orderBy
     * @param string $direction
     * @param array $columns
     * @param string $pageName
     *
     * @param bool $search
     *
     * @return LengthAwarePaginator
     */
    public function filterPaginated( Request $request, $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $pageName = 'page', $search = true )
    {
        $this->setQuery($request->input('trashed') == 1 ? $this->getModel()
                                                               ->withTrashed() : $this->getModel());

        $this->applyFilters($request, $search);

        $query = $this->query;

        $this->sort($query, $orderBy, $direction);

        return $query->paginate($rows, $columns, $pageName);
    }

    /**
     * @param string $name
     * @param array $arguments
     */
    public function __call( $name, $arguments )
    {
        if ( starts_with($name, 'filter') )
        {
            $request = head($arguments);

            $field = snake_case(substr_replace($name, '', 0, 6));

            if ( $this->filterIsset($request, $field) )
                $this->setQuery($this->getQuery()
                                     ->$name($request->input($field), $request->input('trashed', 0) == 1, array_pop($arguments)));
        }
    }

    /**
     * @param Request $request
     * @param string $field
     *
     * @return bool
     */
    private function filterIsset( Request $request, $field )
    {
        return in_array($field, $this->getModelFilters()) && !is_null($request->input($field, null)) && $request->input($field, '') != '';
    }

    /**
     * @param mixed $query
     *
     * @return Filterable
     */
    public function setQuery( $query )
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param Request $request
     * @param $search
     */
    protected function applyFilters( Request $request, $search )
    {
        foreach ( $request->except('_token') as $key => $value )
            $this->{'filter' . studly_case($key)}($request, $search);
    }

    /**
     * @param Request $request
     *
     * @param bool $search
     *
     * @return $this
     */
    protected function filterSearch( Request $request, $search = true )
    {
        if ( !$this->filterIsset($request, 'search') || !$search )
            return $this;

        $columns = $this->getSearchableTableColumns();

        foreach ( explode(' ', $request->input('search')) as $value )
            $this->setQuery($this->getQuery()
                                 ->where(function ( $q ) use ( $value, $columns )
                                 {
                                     foreach ( $columns as $column )
                                         $q->orWhere($column, 'like', '%' . $value . '%');
                                 }));

        return $this;
    }

    /**
     * @return array
     */
    private function getModelFilters()
    {
        return property_exists($this->getModel(), 'filters') ? $this->getModel()->filters : [ 'search' ];
    }

    /**
     * @return mixed
     */
    private function getTableColumns()
    {
        return $this->getModel()
                    ->getConnection()
                    ->getSchemaBuilder()
                    ->getColumnListing($this->getModel()
                                            ->getTable());
    }

    /**
     * @return array
     */
    private function getSearchableTableColumns()
    {
        return array_diff($this->getTableColumns(), $this->getModel()
                                                         ->getDates(), $this->getModel()
                                                                            ->getKeyName());
    }
}