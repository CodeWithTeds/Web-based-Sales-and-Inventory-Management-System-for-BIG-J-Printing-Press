<?php

namespace App\Repositories;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function countAll(): int;
    public function countActive(): int;
    public function countInactive(): int;
}