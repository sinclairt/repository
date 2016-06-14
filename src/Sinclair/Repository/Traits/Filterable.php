<?php

namespace Sinclair\Repository\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Filterable
 * @package Sinclair\Repository\Traits
 */
trait Filterable
{

    /**
     * @var
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
        $this->setQuery($request->get('trashed') == 1 ? $this->getModel()
                                                             ->withTrashed() : $this->getModel());

        foreach ( $request->except('_token') as $key => $value )
            $this->{'filter' . studly_case($key)}($request, $search);

        return $this->sort($this->getQuery(), $orderBy, $direction)
                    ->get($columns);
    }

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
    public function filterPaginated( Request $request, $rows = 15, $orderBy = null, $direction = 'asc', $columns = [ '*' ], $paginationName = 'page', $search = true )
    {
        $this->setQuery($request->get('trashed') == 1 ? $this->getModel()
                                                             ->withTrashed() : $this->getModel());

        foreach ( $request->except('_token') as $key => $value )
            $this->{'filter' . studly_case($key)}($request, $search);

        return $this->sort($this->getQuery(), $orderBy, $direction)
                    ->paginate($rows, $columns, $paginationName);
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
                                     ->$name($request->get($field), $request->get('trashed', 0) == 1, array_pop($arguments)));
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
        return in_array($field, $this->getModelFilters()) && !is_null($request->get($field, null)) && $request->get($field, '') != '';
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
     *
     * @param bool $search
     *
     * @return $this
     */
    private function filterSearch( Request $request, $search = true )
    {
        if ( !$this->filterIsset($request, 'search') || !$search )
            return $this;

        $columns = $this->getSearchableTableColumns();

        foreach ( explode(' ', $request->get('search')) as $value )
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