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
                'description' => 'Product color variants',
                'type' => 'color',
                'options' => [
                    json_encode(['name' => 'Red', 'code' => '#FF0000']),
                    json_encode(['name' => 'Blue', 'code' => '#0000FF']),
                    json_encode(['name' => 'Green', 'code' => '#008000']),
                    json_encode(['name' => 'Black', 'code' => '#000000']),
                    json_encode(['name' => 'White', 'code' => '#FFFFFF']),
                    json_encode(['name' => 'Yellow', 'code' => '#FFFF00']),
                    json_encode(['name' => 'Purple', 'code' => '#800080']),
                    json_encode(['name' => 'Orange', 'code' => '#FFA500']),
                ],
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Size',
                'slug' => 'size',
                'description' => 'Product size options',
                'type' => 'text',
                'options' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL'],
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Material',
                'slug' => 'material',
                'description' => 'Product material composition',
                'type' => 'text',
                'options' => ['Cotton', 'Polyester', 'Wool', 'Silk', 'Leather', 'Denim', 'Linen'],
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
                'name' => 'Brand',
                'slug' => 'brand',
                'description' => 'Product brand name',
                'type' => 'text',
                'options' => ['Nike', 'Adidas', 'Puma', 'Reebok', 'Under Armour', 'New Balance'],
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Pattern',
                'slug' => 'pattern',
                'description' => 'Product pattern or design',
                'type' => 'text',
                'options' => ['Solid', 'Striped', 'Floral', 'Geometric', 'Abstract', 'Polka Dot'],
                'is_required' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Style',
                'slug' => 'style',
                'description' => 'Product style category',
                'type' => 'text',
                'options' => ['Casual', 'Formal', 'Sports', 'Vintage', 'Modern', 'Classic'],
                'is_required' => false,
                'is_active' => true,
            ],
        ];

        foreach ($attributes as $attribute) {
            ProductAttribute::create($attribute);
        }
    }
}
