<div>
    <h1>These are our products:</h1>

    @forelse($products as $product)
        <p>Prodotto: {{ $product->name }} | Prezzo: {{ $product->price }} | Quantità: {{ $product->stock }}</p>
    @empty
        <p>Nessun prodotto disponibile.</p>
    @endforelse
</div>
