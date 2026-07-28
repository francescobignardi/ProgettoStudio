<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::create([
            'code' => '1001',
            'name' => 'John Smith',
            'address' => '9 Pine Street, London',
            'phone' => '3334455678'
        ]);

        Supplier::create([
            'code' => '1002',
            'name' => 'Michael Brown',
            'address' => '22 Green Avenue, Manchester',
            'phone' => '3345566778'
        ]);

        Supplier::create([
            'code' => '1003',
            'name' => 'Emily Davis',
            'address' => '15 High Road, Bristol',
            'phone' => '3356677889'
        ]);

        Supplier::create([
            'code' => '1004',
            'name' => 'James Wilson',
            'address' => '48 Church Lane, Leeds',
            'phone' => '3367788990'
        ]);

        Supplier::create([
            'code' => '1005',
            'name' => 'Sarah Taylor',
            'address' => '31 Market Square, York',
            'phone' => '3378899001'
        ]);
    }
}
