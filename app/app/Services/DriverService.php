<?php

namespace App\Services;

use App\Repositories\DriverRepositoryInterface;

class DriverService
{
    public function __construct(
        protected DriverRepositoryInterface $drivers,
    ) {}

    public function getIndexData(): array
    {
        return [
            'items' => $this->drivers->paginate(),
            'metrics' => [
                'total_drivers' => $this->drivers->countAll(),
            ],
        ];
    }
}