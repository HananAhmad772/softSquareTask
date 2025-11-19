<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{

    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            \App\Models\Product::create([
                'name' => 'Product ' . $i,
                'description' => 'Description for product ' . $i,
                'price' => rand(10, 1000) / 10, 
                'stock_quantity' => rand(0, 100)
            ]);
        }
    }
}
