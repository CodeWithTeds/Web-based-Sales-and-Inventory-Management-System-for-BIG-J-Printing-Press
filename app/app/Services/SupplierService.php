<?php

namespace App\Services;

use App\Repositories\SupplierRepositoryInterface;

class SupplierService
{
    public function __construct(
        protected SupplierRepositoryInterface $suppliers,
    ) {}

    public function getIndexData(): array
    {
        return [
            'items' => $this->suppliers->paginate(),
            'metrics' => [
                'total_suppliers' => $this->suppliers->countAll(),
                'active_suppliers' => $this->suppliers->countActive(),
                'inactive_suppliers' => $this->suppliers->countInactive(),
            ],
        ];
    }
}