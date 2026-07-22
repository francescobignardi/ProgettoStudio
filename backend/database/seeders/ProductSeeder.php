<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::create([
            'name' => 'Tastiera',
            'price' => 89.90,
            'stock' => 12,
        ]);
        Product::create([
            'name' => 'Mouse',
            'price' => 29.90,
            'stock' => 20,
        ]);
        Product::create([
            'name' => 'Monitor',
            'price' => 119.90,
            'stock' => 0,
        ]);
        Product::create([
            'name' => 'Case',
            'price' => 499.90,
            'stock' => 10,
        ]);
    }
}
