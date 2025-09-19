<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample materials for printing supplies
        $materials = [
            [
                'name' => 'Bond Paper A4',
                'description' => 'Standard A4 size bond paper for printing',
                'category' => 'Paper',
                'unit' => 'ream',
                'quantity' => 50,
                'reorder_level' => 10,
                'unit_price' => 250.00,
                'supplier' => 'Office Depot',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'Bond Paper Legal',
                'description' => 'Legal size bond paper for printing',
                'category' => 'Paper',
                'unit' => 'ream',
                'quantity' => 30,
                'reorder_level' => 8,
                'unit_price' => 300.00,
                'supplier' => 'Office Depot',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'Black Ink Cartridge',
                'description' => 'Standard black ink cartridge for HP printers',
                'category' => 'Ink',
                'unit' => 'piece',
                'quantity' => 15,
                'reorder_level' => 5,
                'unit_price' => 800.00,
                'supplier' => 'HP Supplies',
                'notes' => 'Compatible with HP LaserJet Pro series'
            ],
            [
                'name' => 'Colored Ink Set',
                'description' => 'Set of colored ink cartridges (Cyan, Magenta, Yellow)',
                'category' => 'Ink',
                'unit' => 'set',
                'quantity' => 10,
                'reorder_level' => 3,
                'unit_price' => 1500.00,
                'supplier' => 'HP Supplies',
                'notes' => 'Compatible with HP OfficeJet series'
            ],
            [
                'name' => 'Glossy Photo Paper',
                'description' => 'High-quality glossy paper for photo printing',
                'category' => 'Paper',
                'unit' => 'pack',
                'quantity' => 20,
                'reorder_level' => 5,
                'unit_price' => 350.00,
                'supplier' => 'Kodak',
                'notes' => '50 sheets per pack, 200gsm'
            ],
            [
                'name' => 'Toner Cartridge',
                'description' => 'Black toner cartridge for laser printers',
                'category' => 'Toner',
                'unit' => 'piece',
                'quantity' => 8,
                'reorder_level' => 2,
                'unit_price' => 2500.00,
                'supplier' => 'Canon',
                'notes' => 'Compatible with Canon ImageRunner series'
            ],
            [
                'name' => 'Staples',
                'description' => 'Standard staples for office staplers',
                'category' => 'Office Supplies',
                'unit' => 'box',
                'quantity' => 25,
                'reorder_level' => 5,
                'unit_price' => 50.00,
                'supplier' => 'Office Warehouse',
                'notes' => '5000 staples per box'
            ],
            [
                'name' => 'Binding Combs',
                'description' => 'Plastic binding combs for document binding',
                'category' => 'Binding Supplies',
                'unit' => 'pack',
                'quantity' => 15,
                'reorder_level' => 3,
                'unit_price' => 200.00,
                'supplier' => 'Binding Specialists',
                'notes' => '100 pieces per pack, 12mm'
            ],
        ];

        foreach ($materials as $material) {
            Material::create($material);
        }
    }
}