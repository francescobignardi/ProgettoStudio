<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('stock', '>', 0)->orderBy('price', 'desc')->orderBy('name', 'asc')->get();
        return view('products', [
            'products' => $products
        ]);
    }

    public function show(int $id)
    {
        $product = Product::findOrFail($id);
        return view('product', [
            'product' => $product
        ]);
    }

    public function create()
    {
        return view('create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
        ]);
        Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);
        return Redirect::to('/products');
    }

    public function edit(int $id)
    {
        $product = Product::findOrFail($id);
        return view('edit', [
            'product' => $product
        ]);
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
        ]);
        $product = Product::findOrFail($id);
        $product->update([
            'name' => $request['name'],
            'price' => $request['price'],
            'stock' => $request['stock'],
        ]);
        return Redirect::to('/products');
    }

    public function destroy(int $id)
    {
        Product::destroy($id);
        return Redirect::to('/products');
    }
}
