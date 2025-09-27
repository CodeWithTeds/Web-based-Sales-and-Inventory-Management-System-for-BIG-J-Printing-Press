<?php

namespace App\Repositories;

use App\Models\User;

class DriverRepository extends BaseRepository implements DriverRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function countAll(): int
    {
        return $this->model->where('role', 'driver')->count();
    }

    /**
     * Override paginate to only list drivers
     */
    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->model->where('role', 'driver')->paginate($perPage, $columns);
    }

    /**
     * Override find to only get drivers
     */
    public function find(int $id, array $columns = ['*'])
    {
        return $this->model->where('role', 'driver')->findOrFail($id, $columns);
    }

    /**
     * Override create to ensure role is set to driver
     */
    public function create(array $data)
    {
        $data['role'] = 'driver';
        return parent::create($data);
    }
}