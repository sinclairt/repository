<?php

namespace Sterling\Repository\Traits;

trait EloquentSoftDeleteRepository
{
	public function getByIdWithTrashed($id)
	{
		return $this->model->withTrashed()
						   ->find($id);
	}

	public function getAllPaginatedWithTrashed($rows = 15, $orderBy = null, $direction = 'asc')
	{
		$query = $this->model->withTrashed();

		$query = $orderBy == null ? $query->latest()
										  ->paginate($rows) : $query->orderBy($orderBy, $direction)
																	->paginate($rows);

		return $query;
	}

	public function restore($model)
	{
		$model->restore();

		return $model;
	}

    public function searchWithTrashed($search)
    {
        $query = $this->model->withTrashed();

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