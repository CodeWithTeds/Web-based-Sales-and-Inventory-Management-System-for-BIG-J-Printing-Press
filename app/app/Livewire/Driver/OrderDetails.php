<?php

namespace App\Livewire\Driver;

use Livewire\Component;
use App\Models\Order;

class OrderDetails extends Component
{
    public $order;
    public $deliveryStatus;
    public $orderItems = [];
    
    public function mount($orderId)
    {
        $this->order = Order::with(['user', 'orderItems.product', 'userAddress'])->findOrFail($orderId);
        $this->deliveryStatus = $this->order->delivery_status;
        
        $this->orderItems = $this->order->orderItems->map(function ($item) {
            return [
                'product_name' => $item->product->name,
                'quantity' => $item->qty,
                'price' => $item->price,
                'subtotal' => $item->qty * $item->price
            ];
        });
    }
    
    public function updateDeliveryStatus()
    {
        $this->order->update(['delivery_status' => $this->deliveryStatus]);
        
        session()->flash('message', 'Delivery status updated successfully!');
        
        return redirect()->route('driver.orders.index');
    }
    
    public function getStatusColor($status)
    {
        $colors = [
            'pending' => '#F59E0B',
            'preparing' => '#3B82F6',
            'out_for_delivery' => '#8B5CF6',
            'delivered' => '#10B981',
            'cancelled' => '#EF4444'
        ];
        
        return $colors[$status] ?? '#6B7280';
    }
    
    public function render()
    {
        return view('livewire.driver.order-details');
    }
}