<?php

namespace App\Repositories;

use App\Models\Supplier;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    public function countAll(): int
    {
        return $this->model->count();
    }

    public function countActive(): int
    {
        return $this->model->where('status', 'active')->count();
    }

    public function countInactive(): int
    {
        return $this->model->where('status', '!=', 'active')->count();
    }
}