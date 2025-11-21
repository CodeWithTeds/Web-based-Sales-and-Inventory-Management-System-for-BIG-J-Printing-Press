<?php

namespace App\Services;

use App\Repositories\CategoryRepositoryInterface;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categories,
    ) {}

    public function getIndexData(): array
    {
        return [
            'items' => $this->categories->paginate(),
            'metrics' => [
                'total_categories' => $this->categories->countAll(),
                'active_categories' => $this->categories->countActive(),
                'inactive_categories' => $this->categories->countInactive(),
            ],
        ];
    }
}