<?php

namespace App\Repositories;

interface DriverRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Count all drivers.
     */
    public function countAll(): int;
}