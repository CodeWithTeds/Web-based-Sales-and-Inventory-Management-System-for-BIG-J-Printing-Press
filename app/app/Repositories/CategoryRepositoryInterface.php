<?php

namespace App\Repositories;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function countAll(): int;
    public function countActive(): int;
    public function countInactive(): int;
}