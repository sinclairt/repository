<?php

namespace Sinclair\Repository\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Filterable
 *
 * @package Sinclair\Repository\Traits
 */
trait Filterable
{
    /**
     * @var Builder
     */
    public $query;

    /**
     * @param Request|array $filters
     * @param string|null   $orderBy
     * @param string        $direction
     * @param array         $columns
     *
     * @param bool          $search
     *
     * @return Collection
     */
    public function filter($filters, $orderBy = null, $direction = 'asc', $columns = ['*'], $search = true)
    {
        return $this->buildFilteredQuery($filters, $orderBy, $direction, $search)->get($columns);
    }

    /**
     * @param Request|array $filters
     * @param int           $rows
     * @param string|null   $orderBy
     * @param string        $direction
     * @param array         $columns
     * @param string        $pageName
     *
     * @param bool          $search
     *
     * @return LengthAwarePaginator
     */
    public function filterPaginated($filters, $rows = 15, $orderBy = null, $direction = 'asc', $columns = ['*'], $pageName = 'page', $search = true)
    {
        return $this->buildFilteredQuery($filters, $orderBy, $direction, $search)->paginate($rows, $columns, $pageName);
    }

    /**
     * @param string $name
     * @param array  $arguments
     */
    public function __call($name, $arguments)
    {
        if (starts_with($name, 'filter'))
        {
            $filters = head($arguments);

            $field = snake_case(substr_replace($name, '', 0, 6));

            if ($this->filterIsset($filters, $field))
                $this->setQuery($this->getQuery()
                                     ->$name(array_get($filters, $field), array_get($filters, 'trashed', 0) == 1, array_pop($arguments)));
        }
    }

    /**
     * @param Request|array $filters
     * @param string        $field
     *
     * @return bool
     */
    private function filterIsset($filters, $field)
    {
        return in_array($field, $this->getModelFilters()) && !is_null(array_get($filters, $field, null)) && array_get($filters, $field, '') != '';
    }

    /**
     * @param mixed $query
     *
     * @return Filterable
     */
    public function setQuery($query)
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
     * @param Request|array $filters
     * @param               $search
     */
    protected function applyFilters($filters, $search)
    {
        foreach (array_except($filters, '_token') as $key => $value)
            $this->{'filter' . studly_case($key)}($filters, $search);
    }

    /**
     * @param Request|array $filters
     *
     * @param bool          $search
     *
     * @return $this
     */
    protected function filterSearch($filters, $search = true)
    {
        if (!$this->filterIsset($filters, 'search') || !$search)
            return $this;

        $columns = $this->getSearchableTableColumns();

        foreach (explode(' ', array_get($filters, 'search', '')) as $value)
            $this->setQuery($this->getQuery()
                                 ->where(function ($q) use ($value, $columns)
                                 {
                                     foreach ($columns as $column)
                                         $q->orWhere($column, 'like', '%' . $value . '%');
                                 }));

        return $this;
    }

    /**
     * @return array
     */
    private function getModelFilters()
    {
        return property_exists($this->getModel(), 'filters') ? $this->getModel()->filters : ['search'];
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

    /**
     * @param $filters
     * @param $orderBy
     * @param $direction
     * @param $search
     *
     * @return Builder
     */
    protected function buildFilteredQuery($filters, $orderBy, $direction, $search)
    {
        if ($filters instanceof Request)
            $filters = $filters->all();

        $this->setQuery(array_get($filters, 'trashed', 0) == 1 ? $this->getModel()
                                                                      ->withTrashed() : $this->getModel());

        $this->applyFilters($filters, $search);

        $query = $this->query;

        $this->sort($query, $orderBy, $direction);

        return $query;
    }
}