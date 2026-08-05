<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PurchaseOrder::create([
            'supplier_id' => Supplier::inRandomOrder()->first()->id,
            'order_date' => now(),
            'notes' => 'Note di prova 1',
        ]);
        PurchaseOrder::create([
            'supplier_id' => Supplier::inRandomOrder()->first()->id,
            'order_date' => now(),
            'notes' => 'Note di prova 2',
        ]);
        PurchaseOrder::create([
            'supplier_id' => Supplier::inRandomOrder()->first()->id,
            'order_date' => now(),
            'notes' => 'Note di prova 3',
        ]);
        PurchaseOrder::create([
            'supplier_id' => Supplier::inRandomOrder()->first()->id,
            'order_date' => now(),
            'notes' => 'Note di prova 4',
        ]);
        PurchaseOrder::create([
            'supplier_id' => Supplier::inRandomOrder()->first()->id,
            'order_date' => now(),
            'notes' => 'Note di prova 5',
        ]);
    }
}
