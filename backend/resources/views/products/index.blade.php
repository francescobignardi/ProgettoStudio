<div>
    <h1>These are our products:</h1>

    @forelse($products as $product)
        <div>Product: {{ $product->name }} | Price: {{ $product->price }} | Quantity: {{ $product->stock }}
            <a href="/products/{{ $product->id }}">Details</a>
            <a href="/products/{{ $product->id }}/edit">Edit</a>
            <form action="/products/{{ $product->id }}" method="post">
                @method('DELETE')
                @csrf
                <button type="submit">Delete</button>
            </form>
        </div>
    @empty
        <p>No products available.</p>
    @endforelse
    <a href="/products/create">Create a new product</a>
</div>
