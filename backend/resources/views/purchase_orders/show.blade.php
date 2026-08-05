<div>
    <h1>Single order page</h1>
    <p>Id: {{ $purchase_order->id }}</p>
    <p>Supplier: {{ $purchase_order->supplier->name }}</p>
    <p>Date: {{ $purchase_order->order_date }}</p>
    <p>Status: {{ $purchase_order->status }}</p>
</div>
