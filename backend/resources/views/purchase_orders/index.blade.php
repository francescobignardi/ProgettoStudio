<div>
    <h1>All orders:</h1>

    @forelse($purchase_orders as $order)
        <div>Order n.: {{ $order->id }} | Supplier: {{ $order->supplier->name }} | Date: {{ $order->order_date }} | Notes: {{ $order->notes }} | Status: {{ $order->status }}
            <a href="/purchase_orders/{{ $order->id }}">Details</a>
        </div>
    @empty
    <p>No orders available.</p>
    @endforelse
    <a href="/purchase_orders/create">Create a new order</a>
</div>
