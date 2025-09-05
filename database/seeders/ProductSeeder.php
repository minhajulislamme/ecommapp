<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories and subcategories
        $electronics = Category::where('slug', 'electronics')->first();
        $clothing = Category::where('slug', 'clothing')->first();
        $books = Category::where('slug', 'books')->first();
        $sports = Category::where('slug', 'sports-outdoors')->first();

        if (!$electronics || !$clothing || !$books || !$sports) {
            $this->command->warn('Categories not found. Please run CategorySeeder first.');
            return;
        }

        $smartphones = SubCategory::where('slug', 'smartphones')->first();
        $laptops = SubCategory::where('slug', 'laptops')->first();
        $mensClothing = SubCategory::where('slug', 'mens-clothing')->first();
        $fiction = SubCategory::where('slug', 'fiction')->first();

        // Electronics Products
        Product::create([
            'category_id' => $electronics->id,
            'sub_category_id' => $smartphones->id ?? null,
            'name' => 'iPhone 15 Pro',
            'slug' => 'iphone-15-pro',
            'description' => 'Latest iPhone with A17 Pro chip, titanium design, and advanced camera system.',
            'price' => 999.00,
            'sale_price' => 899.00,
            'sku' => 'PRD-IPH15PRO',
            'stock_quantity' => 50,
            'is_active' => true,
            'is_featured' => true,
        ]);

        Product::create([
            'category_id' => $electronics->id,
            'sub_category_id' => $smartphones->id ?? null,
            'name' => 'Samsung Galaxy S24',
            'slug' => 'samsung-galaxy-s24',
            'description' => 'Flagship Android phone with AI features and exceptional camera quality.',
            'price' => 799.00,
            'sku' => 'PRD-SGS24',
            'stock_quantity' => 75,
            'is_active' => true,
            'is_featured' => false,
        ]);

        Product::create([
            'category_id' => $electronics->id,
            'sub_category_id' => $laptops->id ?? null,
            'name' => 'MacBook Air M3',
            'slug' => 'macbook-air-m3',
            'description' => 'Ultra-thin laptop with M3 chip, 13-inch display, and all-day battery life.',
            'price' => 1199.00,
            'sku' => 'PRD-MBAM3',
            'stock_quantity' => 30,
            'is_active' => true,
            'is_featured' => true,
        ]);

        Product::create([
            'category_id' => $electronics->id,
            'sub_category_id' => $laptops->id ?? null,
            'name' => 'Dell XPS 13',
            'slug' => 'dell-xps-13',
            'description' => 'Premium Windows ultrabook with Intel Core i7 and stunning display.',
            'price' => 1099.00,
            'sale_price' => 999.00,
            'sku' => 'PRD-DXPS13',
            'stock_quantity' => 25,
            'is_active' => true,
            'is_featured' => false,
        ]);

        // Clothing Products
        Product::create([
            'category_id' => $clothing->id,
            'sub_category_id' => $mensClothing->id ?? null,
            'name' => 'Classic Denim Jacket',
            'slug' => 'classic-denim-jacket',
            'description' => 'Timeless denim jacket perfect for casual wear. Available in multiple sizes.',
            'price' => 79.99,
            'sale_price' => 59.99,
            'sku' => 'PRD-CDJ001',
            'stock_quantity' => 100,
            'is_active' => true,
            'is_featured' => false,
        ]);

        Product::create([
            'category_id' => $clothing->id,
            'sub_category_id' => $mensClothing->id ?? null,
            'name' => 'Cotton T-Shirt Pack',
            'slug' => 'cotton-t-shirt-pack',
            'description' => 'Set of 3 premium cotton t-shirts in basic colors. Comfortable and durable.',
            'price' => 39.99,
            'sku' => 'PRD-CTS3PK',
            'stock_quantity' => 200,
            'is_active' => true,
            'is_featured' => true,
        ]);

        // Books
        Product::create([
            'category_id' => $books->id,
            'sub_category_id' => $fiction->id ?? null,
            'name' => 'The Digital Revolution',
            'slug' => 'the-digital-revolution',
            'description' => 'A comprehensive guide to understanding the impact of technology on modern society.',
            'price' => 24.99,
            'sale_price' => 19.99,
            'sku' => 'PRD-TDR001',
            'stock_quantity' => 150,
            'is_active' => true,
            'is_featured' => false,
        ]);

        Product::create([
            'category_id' => $books->id,
            'sub_category_id' => $fiction->id ?? null,
            'name' => 'Programming Fundamentals',
            'slug' => 'programming-fundamentals',
            'description' => 'Learn the basics of programming with real-world examples and exercises.',
            'price' => 34.99,
            'sku' => 'PRD-PROGFUND',
            'stock_quantity' => 80,
            'is_active' => true,
            'is_featured' => true,
        ]);

        // Sports & Outdoors
        Product::create([
            'category_id' => $sports->id,
            'name' => 'Yoga Mat Premium',
            'slug' => 'yoga-mat-premium',
            'description' => 'High-quality non-slip yoga mat perfect for all types of yoga practice.',
            'price' => 49.99,
            'sale_price' => 39.99,
            'sku' => 'PRD-YMP001',
            'stock_quantity' => 120,
            'is_active' => true,
            'is_featured' => false,
        ]);

        Product::create([
            'category_id' => $sports->id,
            'name' => 'Resistance Bands Set',
            'slug' => 'resistance-bands-set',
            'description' => 'Complete set of resistance bands for home workouts. Includes multiple resistance levels.',
            'price' => 29.99,
            'sku' => 'PRD-RBS001',
            'stock_quantity' => 90,
            'is_active' => true,
            'is_featured' => true,
        ]);

        $this->command->info('Products created successfully!');
    }
}
