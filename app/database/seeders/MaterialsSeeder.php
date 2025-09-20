<?php

namespace Database\Seeders;

use App\Models\Material;
use Illuminate\Database\Seeder;

class MaterialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Materials for printing supplies
        $materials = [
            [
                'name' => 'A4 Bond Paper (ream)',
                'description' => 'Standard A4 size bond paper',
                'category' => 'Paper Supplies',
                'unit' => 'ream',
                'quantity' => 50,
                'reorder_level' => 10,
                'unit_price' => 250.00,
                'supplier' => 'Paper Supplies Inc.',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'A3 Bond Paper (ream)',
                'description' => 'A3 size bond paper',
                'category' => 'Paper Supplies',
                'unit' => 'ream',
                'quantity' => 30,
                'reorder_level' => 8,
                'unit_price' => 350.00,
                'supplier' => 'Paper Supplies Inc.',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'Short Bond Paper (ream)',
                'description' => 'Short size bond paper',
                'category' => 'Paper Supplies',
                'unit' => 'ream',
                'quantity' => 25,
                'reorder_level' => 5,
                'unit_price' => 220.00,
                'supplier' => 'Paper Supplies Inc.',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'Legal Size Bond Paper (ream)',
                'description' => 'Legal size bond paper',
                'category' => 'Paper Supplies',
                'unit' => 'ream',
                'quantity' => 30,
                'reorder_level' => 8,
                'unit_price' => 280.00,
                'supplier' => 'Paper Supplies Inc.',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'Glossy Paper (pack)',
                'description' => 'High-quality glossy photo paper',
                'category' => 'Specialty Paper',
                'unit' => 'pack',
                'quantity' => 20,
                'reorder_level' => 5,
                'unit_price' => 450.00,
                'supplier' => 'Premium Paper Ltd.',
                'notes' => '50 sheets per pack'
            ],
            [
                'name' => 'Sticker Paper (pack)',
                'description' => 'Adhesive paper for stickers and labels',
                'category' => 'Specialty Paper',
                'unit' => 'pack',
                'quantity' => 15,
                'reorder_level' => 3,
                'unit_price' => 380.00,
                'supplier' => 'Adhesive Materials Inc.',
                'notes' => '50 sheets per pack'
            ],
            [
                'name' => 'Cardstock / Bristol Board (pack)',
                'description' => 'Thick cardstock paper for cards and certificates',
                'category' => 'Specialty Paper',
                'unit' => 'pack',
                'quantity' => 25,
                'reorder_level' => 5,
                'unit_price' => 320.00,
                'supplier' => 'Premium Paper Ltd.',
                'notes' => '100 sheets per pack'
            ],
            [
                'name' => 'Special Paper (pack)',
                'description' => 'Premium specialty paper for special printing needs',
                'category' => 'Specialty Paper',
                'unit' => 'pack',
                'quantity' => 15,
                'reorder_level' => 3,
                'unit_price' => 500.00,
                'supplier' => 'Premium Paper Ltd.',
                'notes' => '25 sheets per pack'
            ],
            [
                'name' => 'NCR Carbonless Paper (pack)',
                'description' => 'No carbon required paper for multi-copy forms',
                'category' => 'Specialty Paper',
                'unit' => 'pack',
                'quantity' => 15,
                'reorder_level' => 3,
                'unit_price' => 350.00,
                'supplier' => 'Form Materials Co.',
                'notes' => '100 sheets per pack'
            ],
            [
                'name' => 'Letterhead Paper (ream)',
                'description' => 'Premium paper for letterheads',
                'category' => 'Business Supplies',
                'unit' => 'ream',
                'quantity' => 15,
                'reorder_level' => 3,
                'unit_price' => 450.00,
                'supplier' => 'Premium Paper Ltd.',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'Envelopes (box)',
                'description' => 'Standard business envelopes',
                'category' => 'Business Supplies',
                'unit' => 'box',
                'quantity' => 20,
                'reorder_level' => 5,
                'unit_price' => 350.00,
                'supplier' => 'Office Supplies Co.',
                'notes' => '100 envelopes per box'
            ],
            [
                'name' => 'PVC Sheets (pack)',
                'description' => 'PVC material for ID cards',
                'category' => 'ID Materials',
                'unit' => 'pack',
                'quantity' => 10,
                'reorder_level' => 2,
                'unit_price' => 500.00,
                'supplier' => 'ID Materials Co.',
                'notes' => '100 sheets per pack'
            ],
            [
                'name' => 'Tarpaulin Roll (meter)',
                'description' => 'Heavy-duty tarpaulin material',
                'category' => 'Large Format Materials',
                'unit' => 'meter',
                'quantity' => 50,
                'reorder_level' => 10,
                'unit_price' => 85.00,
                'supplier' => 'Banner Materials Ltd.',
                'notes' => 'Various widths available'
            ],
            [
                'name' => 'Colored Ink (ml)',
                'description' => 'Colored printing ink (CMYK)',
                'category' => 'Printing Supplies',
                'unit' => 'ml',
                'quantity' => 3000,
                'reorder_level' => 800,
                'unit_price' => 0.75,
                'supplier' => 'Ink Supplies Inc.',
                'notes' => 'For digital printers'
            ],
            [
                'name' => 'Black Ink (ml)',
                'description' => 'Black printing ink',
                'category' => 'Printing Supplies',
                'unit' => 'ml',
                'quantity' => 5000,
                'reorder_level' => 1000,
                'unit_price' => 0.50,
                'supplier' => 'Ink Supplies Inc.',
                'notes' => 'For digital printers'
            ],
            [
                'name' => 'Lamination Film (roll)',
                'description' => 'Clear lamination film',
                'category' => 'Finishing Materials',
                'unit' => 'roll',
                'quantity' => 10,
                'reorder_level' => 2,
                'unit_price' => 850.00,
                'supplier' => 'Finishing Materials Co.',
                'notes' => '100m per roll'
            ]
        ];

        foreach ($materials as $material) {
            Material::create($material);
        }
    }
}