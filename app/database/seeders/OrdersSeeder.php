<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Anchor to Nov 24 for testing weekly projections
        $anchor = Carbon::parse('2025-11-24');
        $weeks = 8; // seed last 8 weeks including current
        $ordersPerWeek = 3;

        $products = Product::query()->inRandomOrder()->take(12)->get();
        if ($products->isEmpty()) {
            // Create a few fallback products if none exist
            $products = collect();
            for ($i = 1; $i <= 5; $i++) {
                $products->push(Product::create([
                    'name' => 'Seed Product ' . $i,
                    'description' => null,
                    'category' => 'Seed Category',
                    'price' => rand(100, 1000) / 1.0,
                    'active' => true,
                    'notes' => null,
                ]));
            }
        }

        $deliveryStatuses = ['pending', 'preparing', 'out_for_delivery', 'delivered'];

        for ($w = 0; $w < $weeks; $w++) {
            $weekStart = $anchor->copy()->startOfWeek()->subWeeks($w);
            for ($o = 0; $o < $ordersPerWeek; $o++) {
                $createdAt = $weekStart->copy()->addDays(rand(0, 6))->setTime(rand(9, 17), rand(0, 59), 0);

                $order = new Order([
                    'order_number' => 'SEED-' . $createdAt->format('Ymd') . '-' . strtoupper(Str::random(5)),
                    'customer_name' => 'Seed Customer ' . strtoupper(Str::random(3)),
                    'customer_email' => null,
                    'total' => 0,
                    'downpayment' => 0,
                    'status' => 'completed',
                    'delivery_status' => $deliveryStatuses[rand(0, count($deliveryStatuses) - 1)],
                    'user_id' => null,
                    'user_address_id' => null,
                    'attachment_path' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                $order->save();

                // Add 1-4 items to the order
                $lineItemsCount = rand(1, 4);
                $items = $products->shuffle()->take($lineItemsCount);
                $total = 0.0;
                foreach ($items as $product) {
                    $qty = rand(1, 5);
                    $price = (float) ($product->price ?? rand(100, 1000));
                    $lineTotal = $qty * $price;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'qty' => $qty,
                        'price' => $price,
                        'line_total' => $lineTotal,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $total += $lineTotal;
                }

                $order->total = $total;
                $order->save();
            }
        }
    }
}