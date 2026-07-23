<div>
    <h2>Form di creazione prodotto</h2>

    <form action="/products" method="post">
        @csrf
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name">
        <br>
        <br>

        <label for="price">Prezzo:</label>
        <input type="text" id="price" name="price">
        <br>
        <br>

        <label for="stock">Quantità:</label>
        <input type="text" id="stock" name="stock">
        <br>
        <br>

        <button type="submit">Salva</button>
    </form>
</div>
