<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    public function countAll(): int
    {
        return (int) Category::count();
    }

    public function countActive(): int
    {
        return (int) Category::where('status', 'active')->count();
    }

    public function countInactive(): int
    {
        return (int) Category::where('status', 'inactive')->count();
    }
}