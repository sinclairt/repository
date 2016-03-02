<?php

namespace Sinclair\Repository\Traits;

trait EloquentRepository
{
	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getById($id)
	{
		return $this->model->find($id);
	}

	/**
	 * @param array $columns
	 *
	 * @param null $orderBy
	 * @param string $direction
	 *
	 * @return mixed
	 */
	public function getAll($columns = [ '*' ], $orderBy = null, $direction = 'asc')
	{
		$query = $this->model;
		$query = $orderBy == null ? $query->latest()
										  ->get($columns) : $query->orderBy($orderBy, $direction)
																  ->get($columns);

		return $query;
	}

	/**
	 * @param int $rows
	 *
	 * @param null $orderBy
	 * @param string $direction
	 *
	 * @return mixed
	 */
	public function getAllPaginate($rows = 15, $orderBy = null, $direction = 'asc')
	{
		$query = $this->model;

		return $orderBy == null ? $query->latest()
										->paginate($rows) : $query->orderBy($orderBy, $direction)
																  ->paginate($rows);
	}

	/**
	 * @param $attributes
	 *
	 * @return null
	 */
	public function add($attributes)
	{
		return $this->save($attributes);
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function getByName($name)
	{
		return $this->model->where('name', $name)
						   ->first();
	}

	/**
	 * @param $attributes
	 * @param $model
	 *
	 * @return null
	 */
	public function update($attributes, $model)
	{
		return $this->save($attributes, $model);
	}

	/**
	 * @param $model
	 */
	public function destroy($model)
	{
		$model->delete();
	}

	/**
	 * @param $attributes
	 * @param null $model
	 *
	 * @return null
	 */
	public function save($attributes, $model = null)
	{
		$model = is_null($model) ? new $this->model : $model;

		$attributes = $this->onlyFillable($attributes, $model);

		$model->fill($attributes)
			  ->save();

		return $model;
	}

	/**
	 * @param $attributes
	 *
	 * @return mixed
	 */
	public function firstOrCreate($attributes)
	{
		$attributes = $this->onlyFillable($attributes, $this->model);

		return $this->model->firstOrCreate($attributes);
	}

	public function getArrayForSelect($value = 'name', $key = 'id')
	{
		return $this->getAll()
					->lists($value, $key)
					->toArray();
	}

	/**
	 * @param $attributes
	 * @param $model
	 */
	public function onlyFillable($attributes, $model)
	{
		foreach ($attributes as $key => $value)
		{
			if (! $model->isFillable($key) || str_contains($key, '_confirmation'))
			{
				unset($attributes[ $key ]);
			}
		}

		return $attributes;
	}

    public function search($search)
    {
        $query = $this->model;

        $query = $query->where(function ($q) use ($search)
        {
            $fields = array_diff($this->model->getFillable(), $this->model->getHidden());

            $first = true;

            foreach ($fields as $field)
            {
                $q = $first ? $q->where($field, 'like', '%' . $search . '%') : $q->orWhere($field, 'like', '%' . $search . '%');

                $first = false;
            }
        });

        return $query->paginate();
    }
}