<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('code', 'asc')->get();
        return view('suppliers.index', [
            'suppliers' => $suppliers
        ]);
    }

    public function show(int $id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('suppliers.show', [
            'supplier' => $supplier
        ]);
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|integer|unique:suppliers',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ]);
        Supplier::create([
            'code' => $request->code,
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
        ]);
        return Redirect::to('/suppliers');
    }
}
