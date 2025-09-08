<?php

namespace Database\Seeders;

use App\Models\ProductAttribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = [
            [
                'name' => 'Color',
                'slug' => 'color',
                'description' => 'Product color variations',
                'type' => 'color',
                'options' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow', 'Purple', 'Orange'],
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Size',
                'slug' => 'size',
                'description' => 'Product size variations',
                'type' => 'text',
                'options' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Material',
                'slug' => 'material',
                'description' => 'Material composition of the product',
                'type' => 'text',
                'options' => ['Cotton', 'Polyester', 'Silk', 'Wool', 'Leather', 'Plastic', 'Metal', 'Wood'],
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Weight',
                'slug' => 'weight',
                'description' => 'Product weight in grams',
                'type' => 'number',
                'options' => null,
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Dimensions',
                'slug' => 'dimensions',
                'description' => 'Product dimensions (Length x Width x Height)',
                'type' => 'text',
                'options' => null,
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Brand',
                'slug' => 'brand',
                'description' => 'Product brand or manufacturer',
                'type' => 'text',
                'options' => ['Nike', 'Adidas', 'Apple', 'Samsung', 'Sony', 'LG', 'Canon', 'Nikon'],
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Capacity',
                'slug' => 'capacity',
                'description' => 'Storage or volume capacity',
                'type' => 'text',
                'options' => ['32GB', '64GB', '128GB', '256GB', '512GB', '1TB', '500ml', '1L', '2L'],
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Rating',
                'slug' => 'rating',
                'description' => 'Product rating out of 5',
                'type' => 'number',
                'options' => null,
                'is_required' => false,
                'is_active' => true,
            ],
        ];

        foreach ($attributes as $attribute) {
            ProductAttribute::create($attribute);
        }
    }
}
