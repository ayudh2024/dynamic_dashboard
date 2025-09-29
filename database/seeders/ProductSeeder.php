<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productNames = [
            'iPhone 15 Pro', 'Samsung Galaxy S24', 'MacBook Pro M3', 'Dell XPS 13', 'iPad Air',
            'Sony WH-1000XM5', 'Nintendo Switch', 'PlayStation 5', 'Xbox Series X', 'Apple Watch',
        ];

        $brands = ['Apple', 'Samsung', 'Google', 'Microsoft'];
        
        // Generate 5000 products
        $products = [];
        
        for ($i = 0; $i < 5000; $i++) {
            $baseName = $productNames[array_rand($productNames)];
            $brand = $brands[array_rand($brands)];
            
            // Sometimes add brand to product name
            $productName = rand(0, 1) ? $brand . ' ' . $baseName : $baseName;
            
            // Generate random sales date for the entire year 2025
            $startDate = Carbon::create(2025, 1, 1);
            $endDate = Carbon::create(2025, 12, 31);
            $salesDate = $startDate->copy()->addDays(rand(0, $startDate->diffInDays($endDate)));
            
            // Generate realistic amount based on product type
            $baseAmount = rand(10, 2000);
            $amount = round($baseAmount + (rand(0, 99) / 100), 2);
            
            $products[] = [
                'name' => $productName,
                'sales_date' => $salesDate->format('Y-m-d'),
                'amount' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // Insert in batches of 1000 for better performance
            if (count($products) >= 1000) {
                Product::insert($products);
                $products = [];
            }
        }
        
        // Insert remaining products
        if (!empty($products)) {
            Product::insert($products);
        }
        
        $this->command->info('Successfully seeded 5000 products!');
    }
}
