<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrdersMap extends Component
{
    public $orders = [];
    public $selectedOrder = null;
    public $selectedStatus = '';
    public $mapCenter = ['lat' => 12.8797, 'lng' => 121.7740]; // Philippines center
    public $mapZoom = 6;

    protected $listeners = ['refreshOrders' => '$refresh'];

    public function mount()
    {
        $this->loadOrders();
    }

    public function loadOrders()
    {
        $this->orders = Order::with(['user', 'userAddress', 'orderItems.product'])
            // Show only active orders on the driver map
            ->whereIn('delivery_status', ['pending', 'preparing', 'out_for_delivery'])
            ->whereHas('userAddress')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $address = optional($order->userAddress);
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->user->name ?? $order->customer_name,
                    'total' => $order->total_amount,
                    // keep both keys for compatibility with existing JS and UI
                    'status' => $order->delivery_status,
                    'delivery_status' => $order->delivery_status,
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude,
                    'exact_address' => $address->exact_address,
                    'created_at' => $order->created_at->format('Y-m-d H:i'),
                    'items_count' => $order->orderItems->count(),
                ];
            });
    }

    public function selectOrder($orderId)
    {
        $this->selectedOrder = Order::with(['user', 'userAddress', 'orderItems.product'])->find($orderId);
        $this->dispatch('orderSelected', id: $orderId);
    }

    public function updateDeliveryStatus($orderId)
    {
        $order = Order::find($orderId);

        if ($order && $this->selectedStatus) {
            $order->update(['delivery_status' => $this->selectedStatus]);

            session()->flash('message', 'Delivery status updated successfully!');

            $this->loadOrders();
            $this->selectOrder($orderId);
        }
    }

    public function getOrdersWithCoordinates()
    {
        return collect($this->orders)->filter(function ($order) {
            return !empty($order['latitude']) && !empty($order['longitude']);
        })->values();
    }

    public function getStatusIcon($status)
    {
        $icons = [
            'pending' => '⏳',
            'preparing' => '👨‍🍳',
            'out_for_delivery' => '🚚',
            'delivered' => '✅',
            'cancelled' => '❌'
        ];

        return $icons[$status] ?? '📦';
    }

    public function render()
    {
        return view('livewire.driver.orders-map', [
            'ordersWithCoordinates' => $this->getOrdersWithCoordinates()
        ]);
    }
}