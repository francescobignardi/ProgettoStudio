<div>
    <h2>Create product form</h2>

    <form action="/products" method="post">
        @csrf
        <label for="name">Name:</label>
        <input type="text" id="name" name="name">
        <br>
        <br>

        <label for="price">Price:</label>
        <input type="text" id="price" name="price">
        <br>
        <br>

        <label for="stock">Quantity:</label>
        <input type="text" id="stock" name="stock">
        <br>
        <br>

        <button type="submit">Save</button>
    </form>
</div>
