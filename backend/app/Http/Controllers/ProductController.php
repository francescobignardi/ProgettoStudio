<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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
}
