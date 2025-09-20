<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample products for printing services
        $products = [
            // Printing Services
            [
                'name' => 'Flyers Printing',
                'description' => 'Professional flyer design and printing service',
                'category' => 'Marketing Materials',
                'image_path' => null,
                'price' => 1500.00,
                'active' => true,
                'notes' => 'Includes design consultation and up to 3 revisions'
            ],
            [
                'name' => 'Calling Cards Printing',
                'description' => 'Custom business card design and printing',
                'category' => 'Marketing Materials',
                'image_path' => null,
                'price' => 1200.00,
                'active' => true,
                'notes' => '100 cards per order, premium paper options available'
            ],
            [
                'name' => 'Brochures Printing',
                'description' => 'Professional brochure design and printing service',
                'category' => 'Marketing Materials',
                'image_path' => null,
                'price' => 2500.00,
                'active' => true,
                'notes' => 'Tri-fold or bi-fold options available'
            ],
            [
                'name' => 'Posters Printing',
                'description' => 'Custom poster design and printing service',
                'category' => 'Marketing Materials',
                'image_path' => null,
                'price' => 1800.00,
                'active' => true,
                'notes' => 'Available in multiple sizes'
            ],
            [
                'name' => 'Banner Design & Printing',
                'description' => 'Large format banner design and printing',
                'category' => 'Large Format Printing',
                'image_path' => null,
                'price' => 3500.00,
                'active' => true,
                'notes' => 'Indoor and outdoor options available'
            ],
            [
                'name' => 'Tarpaulin Printing',
                'description' => 'Custom tarpaulin design and printing service',
                'category' => 'Large Format Printing',
                'image_path' => null,
                'price' => 120.00,
                'active' => true,
                'notes' => 'Price per square meter, weather-resistant material'
            ],
            [
                'name' => 'ID Cards Printing',
                'description' => 'Professional ID card design and printing service',
                'category' => 'Event Materials',
                'image_path' => null,
                'price' => 250.00,
                'active' => true,
                'notes' => 'Price per card, includes PVC card and printing'
            ],
            [
                'name' => 'Certificates Printing',
                'description' => 'Custom certificate design and printing',
                'category' => 'Academic Documents',
                'image_path' => null,
                'price' => 350.00,
                'active' => true,
                'notes' => 'Premium paper options available'
            ],
            [
                'name' => 'Letterheads Printing',
                'description' => 'Professional letterhead design and printing',
                'category' => 'Business Documents',
                'image_path' => null,
                'price' => 1500.00,
                'active' => true,
                'notes' => 'Includes 100 printed letterheads'
            ],
            [
                'name' => 'Envelopes Printing',
                'description' => 'Custom envelope design and printing service',
                'category' => 'Business Documents',
                'image_path' => null,
                'price' => 1200.00,
                'active' => true,
                'notes' => 'Includes 100 printed envelopes'
            ],
            [
                'name' => 'Menus Printing',
                'description' => 'Restaurant menu design and printing service',
                'category' => 'Marketing Materials',
                'image_path' => null,
                'price' => 2000.00,
                'active' => true,
                'notes' => 'Lamination options available'
            ],
            [
                'name' => 'Event Tickets Printing',
                'description' => 'Custom event ticket design and printing',
                'category' => 'Event Materials',
                'image_path' => null,
                'price' => 1500.00,
                'active' => true,
                'notes' => 'Includes 100 tickets with security features'
            ],
            [
                'name' => 'Stickers Printing',
                'description' => 'Custom sticker design and printing service',
                'category' => 'Marketing Materials',
                'image_path' => null,
                'price' => 1200.00,
                'active' => true,
                'notes' => 'Various shapes and sizes available'
            ],
            [
                'name' => 'School Forms Printing',
                'description' => 'Professional school forms printing',
                'category' => 'Academic Documents',
                'image_path' => null,
                'price' => 5000.00,
                'active' => true,
                'notes' => 'Multiple binding options available'
            ],
            [
                'name' => 'Business Forms Printing',
                'description' => 'Custom business forms printing service',
                'category' => 'Business Documents',
                'image_path' => null,
                'price' => 3500.00,
                'active' => true,
                'notes' => 'Various business form options available'
            ],
            [
                'name' => 'Receipts Printing',
                'description' => 'Custom receipt printing',
                'category' => 'Business Documents',
                'image_path' => null,
                'price' => 4500.00,
                'active' => true,
                'notes' => 'Various receipt formats available'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}