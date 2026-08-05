<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PurchaseOrdersController extends Controller
{
    public function index()
    {
        $purchase_orders = PurchaseOrder::orderBy('order_date', 'desc')->get();
        return view('purchase_orders.index', [
            'purchase_orders' => $purchase_orders
        ]);
    }

    public function show(int $id)
    {
        $purchase_order = PurchaseOrder::findOrFail($id);
        return view('purchase_orders.show', [
            'purchase_order' => $purchase_order
        ]);
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        return view('purchase_orders.create', [
            'suppliers' => $suppliers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'notes' => 'string|max:255|nullable',
        ]);
        PurchaseOrder::create([
            'supplier_id' => $request->supplier_id,
            'order_date' => now(),
            'notes' => $request->notes,
        ]);
        return Redirect::to('/purchase_orders');
    }
}
