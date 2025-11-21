<?php

namespace App\Repositories;

interface SizeRepositoryInterface extends BaseRepositoryInterface
{
    public function countAll(): int;
    public function countActive(): int;
    public function countInactive(): int;

    /**
     * Get sizes by category id
     * @param int $categoryId
     * @return mixed
     */
    public function getByCategoryId(int $categoryId);
}