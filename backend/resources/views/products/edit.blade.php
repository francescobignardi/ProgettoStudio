<div>
    <h2>Edit product form</h2>

    <form action="/products/{{ $product->id }}" method="post">
        @method('PUT')
        @csrf
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="{{ $product->name }}">
        <br>
        <br>

        <label for="price">Price:</label>
        <input type="text" id="price" name="price" value="{{ $product->price }}">
        <br>
        <br>

        <label for="stock">Quantity:</label>
        <input type="text" id="stock" name="stock" value="{{ $product->stock }}">
        <br>
        <br>

        <button type="submit">Save</button>
    </form>
</div>
