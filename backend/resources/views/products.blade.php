<div>
    <h1>These are our products:</h1>

    @forelse($products as $product)
        <p>Prodotto: {{ $product->name }} | Prezzo: {{ $product->price }} | Quantità: {{ $product->stock }}
            <a href="/products/{{ $product->id }}">Dettaglio</a>
            <a href="/products/{{ $product->id }}/edit">Modifica</a>
        </p>
    @empty
        <p>Nessun prodotto disponibile.</p>
    @endforelse
    <a href="/products/create">Crea un nuovo prodotto</a>
</div>
