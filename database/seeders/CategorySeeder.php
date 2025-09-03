<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main categories
        $electronics = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'description' => 'Electronic devices and accessories',
            'is_active' => true,
        ]);

        $clothing = Category::create([
            'name' => 'Clothing',
            'slug' => 'clothing',
            'description' => 'Fashion and apparel for all',
            'is_active' => true,
        ]);

        $books = Category::create([
            'name' => 'Books',
            'slug' => 'books',
            'description' => 'Books and educational materials',
            'is_active' => true,
        ]);

        $sports = Category::create([
            'name' => 'Sports & Outdoors',
            'slug' => 'sports-outdoors',
            'description' => 'Sports equipment and outdoor gear',
            'is_active' => true,
        ]);

        // Create sub-categories for Electronics
        SubCategory::create([
            'category_id' => $electronics->id,
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'description' => 'Mobile phones and accessories',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $electronics->id,
            'name' => 'Laptops',
            'slug' => 'laptops',
            'description' => 'Laptops and computer accessories',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $electronics->id,
            'name' => 'Headphones',
            'slug' => 'headphones',
            'description' => 'Audio devices and headphones',
            'is_active' => true,
        ]);

        // Create sub-categories for Clothing
        SubCategory::create([
            'category_id' => $clothing->id,
            'name' => 'Men\'s Clothing',
            'slug' => 'mens-clothing',
            'description' => 'Clothing for men',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $clothing->id,
            'name' => 'Women\'s Clothing',
            'slug' => 'womens-clothing',
            'description' => 'Clothing for women',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $clothing->id,
            'name' => 'Shoes',
            'slug' => 'shoes',
            'description' => 'Footwear for all occasions',
            'is_active' => true,
        ]);

        // Create sub-categories for Books
        SubCategory::create([
            'category_id' => $books->id,
            'name' => 'Fiction',
            'slug' => 'fiction',
            'description' => 'Fiction books and novels',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $books->id,
            'name' => 'Non-Fiction',
            'slug' => 'non-fiction',
            'description' => 'Non-fiction and educational books',
            'is_active' => true,
        ]);

        // Create sub-categories for Sports
        SubCategory::create([
            'category_id' => $sports->id,
            'name' => 'Fitness Equipment',
            'slug' => 'fitness-equipment',
            'description' => 'Home and gym fitness equipment',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $sports->id,
            'name' => 'Outdoor Gear',
            'slug' => 'outdoor-gear',
            'description' => 'Camping and outdoor adventure gear',
            'is_active' => true,
        ]);
    }
}
