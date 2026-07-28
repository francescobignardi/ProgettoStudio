<div>
    <h1>All suppliers:</h1>

    @forelse($suppliers as $supplier)
        <div>Supplier: {{ $supplier->name }} | Address: {{ $supplier->address }} | Phone: {{ $supplier->phone }}
            <a href="/suppliers/{{ $supplier->id }}">Details</a>
        </div>
    @empty
        <p>No suppliers available.</p>
    @endforelse
    <a href="/suppliers/create">Create a new supplier</a>
</div>
