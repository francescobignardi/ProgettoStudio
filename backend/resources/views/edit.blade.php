<div>
    <h2>Form di modifica prodotto</h2>

    <form action="/products/{{ $product->id }}" method="post">
        @method('PUT')
        @csrf
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name" value="{{ $product->name }}">
        <br>
        <br>

        <label for="price">Prezzo:</label>
        <input type="text" id="price" name="price" value="{{ $product->price }}">
        <br>
        <br>

        <label for="stock">Quantità:</label>
        <input type="text" id="stock" name="stock" value="{{ $product->stock }}">
        <br>
        <br>

        <button type="submit">Salva</button>
    </form>
</div>
