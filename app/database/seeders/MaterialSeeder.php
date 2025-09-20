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
            // Products (Printing Services)
            [
                'name' => 'Flyers Printing',
                'description' => 'Custom flyer printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 5.00,
                'supplier' => 'In-house',
                'notes' => 'Various sizes available'
            ],
            [
                'name' => 'Receipts Printing',
                'description' => 'Custom receipt printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 2.50,
                'supplier' => 'In-house',
                'notes' => 'Standard receipt format'
            ],
            [
                'name' => 'Business Forms Printing',
                'description' => 'Custom business form printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 8.00,
                'supplier' => 'In-house',
                'notes' => 'Customizable templates available'
            ],
            [
                'name' => 'School Forms Printing',
                'description' => 'Custom school form printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 7.50,
                'supplier' => 'In-house',
                'notes' => 'Standard school form formats available'
            ],
            [
                'name' => 'Brochures Printing',
                'description' => 'Custom brochure printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 12.00,
                'supplier' => 'In-house',
                'notes' => 'Tri-fold and bi-fold options'
            ],
            [
                'name' => 'Posters Printing',
                'description' => 'Custom poster printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 25.00,
                'supplier' => 'In-house',
                'notes' => 'Various sizes available'
            ],
            [
                'name' => 'Calling Cards Printing',
                'description' => 'Custom business card printing service',
                'category' => 'Printing Services',
                'unit' => 'box',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 250.00,
                'supplier' => 'In-house',
                'notes' => '100 cards per box'
            ],
            [
                'name' => 'Certificates Printing',
                'description' => 'Custom certificate printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 35.00,
                'supplier' => 'In-house',
                'notes' => 'Premium paper options available'
            ],
            [
                'name' => 'Event Tickets Printing',
                'description' => 'Custom event ticket printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 3.50,
                'supplier' => 'In-house',
                'notes' => 'Customizable with security features'
            ],
            [
                'name' => 'Stickers Printing',
                'description' => 'Custom sticker printing service',
                'category' => 'Printing Services',
                'unit' => 'sheet',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 15.00,
                'supplier' => 'In-house',
                'notes' => 'Various sizes and shapes available'
            ],
            [
                'name' => 'ID Cards Printing',
                'description' => 'Custom ID card printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 75.00,
                'supplier' => 'In-house',
                'notes' => 'With or without lamination'
            ],
            [
                'name' => 'Tarpaulin Printing',
                'description' => 'Custom tarpaulin printing service',
                'category' => 'Printing Services',
                'unit' => 'sqm',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 120.00,
                'supplier' => 'In-house',
                'notes' => 'Weather-resistant outdoor material'
            ],
            [
                'name' => 'Menus Printing',
                'description' => 'Custom menu printing for restaurants',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 45.00,
                'supplier' => 'In-house',
                'notes' => 'Lamination options available'
            ],
            [
                'name' => 'Envelopes Printing',
                'description' => 'Custom envelope printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 8.00,
                'supplier' => 'In-house',
                'notes' => 'Various sizes available'
            ],
            [
                'name' => 'Letterheads Printing',
                'description' => 'Custom letterhead printing service',
                'category' => 'Printing Services',
                'unit' => 'piece',
                'quantity' => 0,
                'reorder_level' => 0,
                'unit_price' => 7.00,
                'supplier' => 'In-house',
                'notes' => 'Premium paper options available'
            ],
            
            // Materials (Raw Items in Stock)
            [
                'name' => 'A4 Bond Paper',
                'description' => 'Standard A4 size bond paper',
                'category' => 'Raw Materials',
                'unit' => 'ream',
                'quantity' => 50,
                'reorder_level' => 10,
                'unit_price' => 250.00,
                'supplier' => 'Paper Supplies Inc.',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'Legal Size Bond Paper',
                'description' => 'Legal size bond paper',
                'category' => 'Raw Materials',
                'unit' => 'ream',
                'quantity' => 30,
                'reorder_level' => 8,
                'unit_price' => 280.00,
                'supplier' => 'Paper Supplies Inc.',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'Short Bond Paper',
                'description' => 'Short size bond paper',
                'category' => 'Raw Materials',
                'unit' => 'ream',
                'quantity' => 25,
                'reorder_level' => 5,
                'unit_price' => 220.00,
                'supplier' => 'Paper Supplies Inc.',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'NCR Carbonless Paper',
                'description' => 'No carbon required paper for multi-copy forms',
                'category' => 'Raw Materials',
                'unit' => 'pack',
                'quantity' => 15,
                'reorder_level' => 3,
                'unit_price' => 350.00,
                'supplier' => 'Form Materials Co.',
                'notes' => '100 sheets per pack'
            ],
            [
                'name' => 'Glossy Paper',
                'description' => 'High-quality glossy photo paper',
                'category' => 'Raw Materials',
                'unit' => 'pack',
                'quantity' => 20,
                'reorder_level' => 5,
                'unit_price' => 450.00,
                'supplier' => 'Premium Paper Ltd.',
                'notes' => '50 sheets per pack'
            ],
            [
                'name' => 'Cardstock / Bristol Board',
                'description' => 'Thick cardstock paper for cards and certificates',
                'category' => 'Raw Materials',
                'unit' => 'pack',
                'quantity' => 25,
                'reorder_level' => 5,
                'unit_price' => 320.00,
                'supplier' => 'Premium Paper Ltd.',
                'notes' => '100 sheets per pack'
            ],
            [
                'name' => 'Sticker Paper',
                'description' => 'Adhesive paper for stickers and labels',
                'category' => 'Raw Materials',
                'unit' => 'pack',
                'quantity' => 15,
                'reorder_level' => 3,
                'unit_price' => 380.00,
                'supplier' => 'Adhesive Materials Inc.',
                'notes' => '50 sheets per pack'
            ],
            [
                'name' => 'PVC Sheets',
                'description' => 'PVC material for ID cards',
                'category' => 'Raw Materials',
                'unit' => 'pack',
                'quantity' => 10,
                'reorder_level' => 2,
                'unit_price' => 500.00,
                'supplier' => 'ID Materials Co.',
                'notes' => '100 sheets per pack'
            ],
            [
                'name' => 'Tarpaulin Roll',
                'description' => 'Heavy-duty tarpaulin material',
                'category' => 'Raw Materials',
                'unit' => 'meter',
                'quantity' => 50,
                'reorder_level' => 10,
                'unit_price' => 85.00,
                'supplier' => 'Banner Materials Ltd.',
                'notes' => 'Various widths available'
            ],
            [
                'name' => 'Envelopes',
                'description' => 'Standard business envelopes',
                'category' => 'Raw Materials',
                'unit' => 'box',
                'quantity' => 20,
                'reorder_level' => 5,
                'unit_price' => 350.00,
                'supplier' => 'Office Supplies Co.',
                'notes' => '100 envelopes per box'
            ],
            [
                'name' => 'Letterhead Paper',
                'description' => 'Premium paper for letterheads',
                'category' => 'Raw Materials',
                'unit' => 'ream',
                'quantity' => 15,
                'reorder_level' => 3,
                'unit_price' => 450.00,
                'supplier' => 'Premium Paper Ltd.',
                'notes' => '500 sheets per ream'
            ],
            [
                'name' => 'Black Ink',
                'description' => 'Black printing ink',
                'category' => 'Raw Materials',
                'unit' => 'ml',
                'quantity' => 5000,
                'reorder_level' => 1000,
                'unit_price' => 0.50,
                'supplier' => 'Ink Supplies Inc.',
                'notes' => 'For digital printers'
            ],
            [
                'name' => 'Colored Ink',
                'description' => 'Colored printing ink (CMYK)',
                'category' => 'Raw Materials',
                'unit' => 'ml',
                'quantity' => 3000,
                'reorder_level' => 800,
                'unit_price' => 0.75,
                'supplier' => 'Ink Supplies Inc.',
                'notes' => 'For digital printers'
            ],
            [
                'name' => 'Lamination Film',
                'description' => 'Clear lamination film',
                'category' => 'Raw Materials',
                'unit' => 'roll',
                'quantity' => 10,
                'reorder_level' => 2,
                'unit_price' => 850.00,
                'supplier' => 'Finishing Materials Co.',
                'notes' => '100m per roll'
            ],
            [
                'name' => 'Staple Wires / Binding Materials',
                'description' => 'Materials for binding documents',
                'category' => 'Raw Materials',
                'unit' => 'box',
                'quantity' => 25,
                'reorder_level' => 5,
                'unit_price' => 120.00,
                'supplier' => 'Office Supplies Co.',
                'notes' => 'Various binding options available'
            ]
        ];

        foreach ($materials as $material) {
            Material::create($material);
        }
    }
}