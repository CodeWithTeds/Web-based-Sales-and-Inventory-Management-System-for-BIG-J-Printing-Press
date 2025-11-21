<?php

namespace App\Services;

use App\Repositories\SizeRepositoryInterface;
use App\Repositories\CategoryRepositoryInterface;

class SizeService
{
    public function __construct(
        protected SizeRepositoryInterface $sizes,
        protected CategoryRepositoryInterface $categories,
    ) {}

    public function getIndexData(): array
    {
        return [
            'items' => $this->sizes->paginate(),
            'categories' => $this->categories->all(),
            'metrics' => [
                'total_sizes' => $this->sizes->countAll(),
                'active_sizes' => $this->sizes->countActive(),
                'inactive_sizes' => $this->sizes->countInactive(),
            ],
        ];
    }

    public function getCreateData(): array
    {
        return [
            'categories' => $this->categories->all(),
        ];
    }

    public function getEditData(int $id): array
    {
        return [
            'item' => $this->sizes->find($id),
            'categories' => $this->categories->all(),
        ];
    }
}