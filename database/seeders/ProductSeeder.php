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
            'AirPods Pro', 'Surface Laptop', 'Canon EOS R5', 'Nikon Z6', 'GoPro Hero 12',
            'Tesla Model 3', 'Toyota Camry', 'Honda Accord', 'BMW X5', 'Mercedes C-Class',
            'Nike Air Max', 'Adidas Ultraboost', 'Jordan Retro', 'Converse Chuck Taylor', 'Vans Old Skool',
            'Rolex Submariner', 'Omega Seamaster', 'Cartier Tank', 'Patek Philippe', 'Audemars Piguet',
            'Louis Vuitton Bag', 'Gucci Belt', 'Hermes Scarf', 'Chanel Perfume', 'Dior Lipstick'
        ];

        $brands = ['Apple', 'Samsung', 'Google', 'Microsoft'];
        
        // Generate 5000 products
        $products = [];
        
        for ($i = 0; $i < 5000; $i++) {
            $baseName = $productNames[array_rand($productNames)];
            $brand = $brands[array_rand($brands)];
            
            // Sometimes add brand to product name
            $productName = rand(0, 1) ? $brand . ' ' . $baseName : $baseName;
            
            // Generate random sales date within the last 2 years
            $startDate = Carbon::now()->subYears(1);
            $endDate = Carbon::now();
            $salesDate = $startDate->copy()->addDays(rand(0, $endDate->diffInDays($startDate)));
            
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
