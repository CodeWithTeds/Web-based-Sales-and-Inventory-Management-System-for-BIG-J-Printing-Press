<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all resources
     *
     * @param array $columns
     * @return mixed
     */
    public function all(array $columns = ['*'])
    {
        return $this->model->all($columns);
    }

    /**
     * Get paginated resources
     *
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Create new resource
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update resource
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id)
    {
        $record = $this->find($id);
        return $record->update($data);
    }

    /**
     * Delete resource
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        return $this->model->destroy($id);
    }

    /**
     * Find resource by id
     *
     * @param int $id
     * @param array $columns
     * @return mixed
     */
    public function find(int $id, array $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Find resource by specific column
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @return mixed
     */
    public function findBy(string $field, $value, array $columns = ['*'])
    {
        return $this->model->where($field, $value)->first($columns);
    }
}