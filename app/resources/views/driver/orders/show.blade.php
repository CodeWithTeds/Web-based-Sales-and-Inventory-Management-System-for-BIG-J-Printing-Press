<x-layouts.app :title="__('Order Details')">
    @livewire('driver.order-details', ['orderId' => $order->id])
</x-layouts.app>