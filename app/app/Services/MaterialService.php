<?php

namespace App\Services;

use App\Repositories\MaterialRepositoryInterface;
use App\Models\Material;

class MaterialService
{
    public function __construct(
        protected MaterialRepositoryInterface $materials
    ) {}

    public function indexData(): array
    {
        // Compute metrics for materials dashboard cards
        $metrics = [
            'total_materials' => (int) Material::count(),
            'low_stock' => (int) Material::whereColumn('quantity', '<=', 'reorder_level')->count(),
            'in_stock' => (int) Material::whereColumn('quantity', '>', 'reorder_level')->count(),
            'total_value' => (float) (Material::selectRaw('SUM(quantity * unit_price) as total')->value('total') ?? 0),
            'avg_unit_price' => (float) (Material::avg('unit_price') ?? 0),
        ];

        return [
            'items' => $this->materials->paginate(),
            'categories' => $this->materials->getUniqueCategories(),
            'metrics' => $metrics,
        ];
    }

    public function byCategoryData(?string $category): array
    {
        $categories = $this->materials->getUniqueCategories();
        if ($category) {
            $items = $this->materials->getByCategory($category);
        } else {
            $items = $this->materials->all();
        }
        return compact('items', 'categories', 'category');
    }

    public function stockInFormData(int $id)
    {
        return $this->materials->find($id);
    }

    public function stockIn(int $id, float $quantity, ?string $notes = null): void
    {
        $this->materials->stockIn($id, $quantity, $notes);
    }

    public function requestFormData(): array
    {
        return [
            'materials' => $this->materials->all()
        ];
    }

    public function submitRequest(int $materialId): string
    {
        $material = $this->materials->find($materialId);
        return 'Material request for ' . $material->name . ' submitted successfully';
    }

    // New: data for low stock report
    public function lowStockData(): array
    {
        return [
            'items' => $this->materials->getLowStockMaterials(),
        ];
    }
}