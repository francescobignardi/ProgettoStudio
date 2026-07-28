<div>
    <h2>Create supplier form</h2>

    <form action="/suppliers" method="post">
        @csrf
        <label for="code">Code:</label>
        <input type="text" id="code" name="code">
        <br>
        <br>

        <label for="name">Name:</label>
        <input type="text" id="name" name="name">
        <br>
        <br>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address">
        <br>
        <br>

        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone">
        <br>
        <br>

        <button type="submit">Save</button>
    </form>
</div>
