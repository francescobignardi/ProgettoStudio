<div>
    <h2>Create order form</h2>

    <form action="/purchase_orders" method="post">
        @csrf
        <label for="supplier">Supplier:</label>
        <select id="supplier" name="supplier_id">
            <option value="">-- Please choose a supplier --</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
        </select>
        <br>
        <br>

        <label for="notes">Notes:</label>
        <input type="text" id="notes" name="notes">
        <br>
        <br>

        <button type="submit">Save</button>
    </form>
</div>
