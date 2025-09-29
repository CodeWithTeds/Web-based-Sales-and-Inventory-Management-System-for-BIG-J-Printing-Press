<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function index()
    {
        $orders = Order::where('delivery_status', '!=', 'delivered')
            ->where('delivery_status', '!=', 'cancelled')
            ->with(['user', 'userAddress'])
            ->latest()
            ->get();

        return view('driver.orders.index', [
            'orders' => $orders,
            'deliveryStatuses' => $this->deliveryStatuses(),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'userAddress', 'items', 'payments']);

        $address = $order->userAddress;
        $addressNames = [
            'region_name' => null,
            'province_name' => null,
            'city_name' => null,
            'barangay_name' => null,
        ];

        if ($address) {
            if ($address->region_code) {
                $addressNames['region_name'] = DB::table('regions')->where('code', $address->region_code)->value('name');
            }
            if ($address->province_code) {
                $addressNames['province_name'] = DB::table('provinces')->where('code', $address->province_code)->value('name');
            }
            if ($address->city_code) {
                $addressNames['city_name'] = DB::table('cities')->where('code', $address->city_code)->value('name');
            }
            if ($address->barangay_code) {
                $addressNames['barangay_name'] = DB::table('barangays')->where('code', $address->barangay_code)->value('name');
            }
        }

        return view('driver.orders.show', [
            'order' => $order,
            'address' => $address,
            'addressNames' => $addressNames,
            'deliveryStatuses' => $this->deliveryStatuses(),
        ]);
    }

    public function updateDeliveryStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'delivery_status' => ['required', 'string', 'in:' . implode(',', $this->deliveryStatuses())],
        ]);

        $order->delivery_status = $validated['delivery_status'];
        $order->save();

        return back()->with('status', 'Delivery status updated.');
    }

    private function deliveryStatuses(): array
    {
        return [
            'pending',
            'preparing',
            'out_for_delivery',
            'delivered',
            'cancelled',
        ];
    }
}