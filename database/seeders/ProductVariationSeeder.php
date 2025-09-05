<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Seeder;

class ProductVariationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing products
        $products = Product::take(3)->get();

        foreach ($products as $product) {
            // Add size variations for the first product
            if ($product->id === 1) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => 'Size - Small',
                    'attribute_values' => ['size' => 'Small'],
                    'price' => $product->price - 5.00,
                    'sale_price' => $product->sale_price ? $product->sale_price - 5.00 : null,
                    'stock_quantity' => 10,
                ]);

                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => 'Size - Medium',
                    'attribute_values' => ['size' => 'Medium'],
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'stock_quantity' => 15,
                ]);

                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => 'Size - Large',
                    'attribute_values' => ['size' => 'Large'],
                    'price' => $product->price + 5.00,
                    'sale_price' => $product->sale_price ? $product->sale_price + 5.00 : null,
                    'stock_quantity' => 8,
                ]);
            }

            // Add color variations for the second product
            if ($product->id === 2) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => 'Color - Red',
                    'attribute_values' => ['color' => 'Red'],
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'stock_quantity' => 12,
                ]);

                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => 'Color - Blue',
                    'attribute_values' => ['color' => 'Blue'],
                    'price' => $product->price + 2.00,
                    'sale_price' => $product->sale_price ? $product->sale_price + 2.00 : null,
                    'stock_quantity' => 7,
                ]);

                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => 'Color - Green',
                    'attribute_values' => ['color' => 'Green'],
                    'price' => $product->price + 1.00,
                    'sale_price' => $product->sale_price ? $product->sale_price + 1.00 : null,
                    'stock_quantity' => 20,
                ]);
            }

            // Add material variations for the third product
            if ($product->id === 3) {
                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => 'Material - Cotton',
                    'attribute_values' => ['material' => 'Cotton'],
                    'price' => $product->price,
                    'sale_price' => $product->sale_price,
                    'stock_quantity' => 25,
                ]);

                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => 'Material - Polyester',
                    'attribute_values' => ['material' => 'Polyester'],
                    'price' => $product->price - 3.00,
                    'sale_price' => $product->sale_price ? $product->sale_price - 3.00 : null,
                    'stock_quantity' => 18,
                ]);

                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => 'Material - Silk',
                    'attribute_values' => ['material' => 'Silk'],
                    'price' => $product->price + 15.00,
                    'sale_price' => $product->sale_price ? $product->sale_price + 15.00 : null,
                    'stock_quantity' => 5,
                ]);
            }
        }
    }
}
