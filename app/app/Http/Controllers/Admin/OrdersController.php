<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['user', 'userAddress', 'items.product', 'payments']);

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
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

        $deliveryStatuses = $this->deliveryStatuses();

        return view('admin.orders.show', [
            'order' => $order,
            'address' => $address,
            'addressNames' => $addressNames,
            'deliveryStatuses' => $deliveryStatuses,
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