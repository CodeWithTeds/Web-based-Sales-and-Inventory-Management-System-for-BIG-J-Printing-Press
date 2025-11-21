<?php

namespace App\Repositories;

use App\Models\Size;

class SizeRepository extends BaseRepository implements SizeRepositoryInterface
{
    public function __construct(Size $model)
    {
        parent::__construct($model);
    }

    public function countAll(): int
    {
        return (int) $this->model->count();
    }

    public function countActive(): int
    {
        return (int) $this->model->where('status', 'active')->count();
    }

    public function countInactive(): int
    {
        return (int) $this->model->where('status', '!=', 'active')->count();
    }

    public function getByCategoryId(int $categoryId)
    {
        return $this->model->where('category_id', $categoryId)->orderBy('name')->get();
    }
}