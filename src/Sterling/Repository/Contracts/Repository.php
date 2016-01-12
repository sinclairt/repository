<?php namespace Sterling\Repository\Contracts;

interface Repository
{
    /**
     * @param $id
     *
     * @return mixed
     */
    public function getById($id);

    /**
     * @param array $columns
     *
     * @param null $orderBy
     * @param string $direction
     *
     * @return mixed
     */
    public function getAll($columns = [ '*' ], $orderBy = null, $direction = 'asc');

    /**
     * @param int $rows
     *
     * @param null $orderBy
     * @param string $direction
     *
     * @return mixed
     */
    public function getAllPaginate($rows = 15, $orderBy = null, $direction = 'asc');

    /**
     * @param $attributes
     *
     * @return null
     */
    public function add($attributes);

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getByName($name);

    /**
     * @param $attributes
     * @param $model
     *
     * @return null
     */
    public function update($attributes, $model);

    /**
     * @param $model
     */
    public function destroy($model);

    /**
     * @param $attributes
     * @param null $model
     *
     * @return null
     */
    public function save($attributes, $model = null);

    /**
     * @param $attributes
     *
     * @return mixed
     */
    public function firstOrCreate($attributes);

    /**
     * @param string $value
     * @param string $key
     *
     * @return mixed
     */
    public function getArrayForSelect($value = 'name', $key = 'id');

    /**
     * @param $attributes
     * @param $model
     */
    public function onlyFillable($attributes, $model);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getByIdWithTrashed($id);

    /**
     * @param int $rows
     * @param null $orderBy
     * @param string $direction
     *
     * @return mixed
     */
    public function getAllPaginatedWithTrashed($rows = 15, $orderBy = null, $direction = 'asc');

    /**
     * @param $model
     *
     * @return mixed
     */
    public function restore($model);

    /**
     * @param $search
     *
     * @return mixed
     */
    public function search($search);

    /**
     * @param $search
     *
     * @return mixed
     */
    public function searchWithTrashed($search);
}