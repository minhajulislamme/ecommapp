<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products with their variations.
     */
    public function index()
    {
        $products = Product::with(['variations' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true)
            ->paginate(12);

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'base_price' => $product->price,
                    'base_sale_price' => $product->sale_price,
                    'has_variations' => $product->hasVariations(),
                    'price_range' => $product->price_range,
                    'min_price' => $product->min_price,
                    'max_price' => $product->max_price,
                    'total_stock' => $product->total_stock,
                    'image' => $product->image,
                    'is_featured' => $product->is_featured,
                    'variations' => $product->variations->map(function ($variation) {
                        return [
                            'id' => $variation->id,
                            'name' => $variation->name,
                            'value' => $variation->value,
                            'display_name' => $variation->display_name,
                            'price' => $variation->price,
                            'sale_price' => $variation->sale_price,
                            'effective_price' => $variation->effective_price,
                            'is_on_sale' => $variation->is_on_sale,
                            'stock_quantity' => $variation->stock_quantity,
                            'sku' => $variation->sku,
                        ];
                    }),
                ];
            }),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Display the specified product with its variations.
     */
    public function show($slug)
    {
        $product = Product::with(['variations' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'base_price' => $product->price,
                'base_sale_price' => $product->sale_price,
                'effective_price' => $product->effective_price,
                'has_variations' => $product->hasVariations(),
                'price_range' => $product->price_range,
                'min_price' => $product->min_price,
                'max_price' => $product->max_price,
                'total_stock' => $product->total_stock,
                'sku' => $product->sku,
                'image' => $product->image,
                'images' => $product->images,
                'all_images' => $product->all_images,
                'is_featured' => $product->is_featured,
                'is_on_sale' => $product->is_on_sale,
                'category' => $product->category,
                'sub_category' => $product->subCategory,
                'variations' => $product->variations->groupBy('name')->map(function ($variations, $variationName) {
                    return [
                        'name' => $variationName,
                        'options' => $variations->map(function ($variation) {
                            return [
                                'id' => $variation->id,
                                'value' => $variation->value,
                                'price' => $variation->price,
                                'sale_price' => $variation->sale_price,
                                'effective_price' => $variation->effective_price,
                                'is_on_sale' => $variation->is_on_sale,
                                'stock_quantity' => $variation->stock_quantity,
                                'sku' => $variation->sku,
                                'is_available' => $variation->stock_quantity > 0,
                            ];
                        })->values()
                    ];
                })->values()
            ]
        ]);
    }

    /**
     * Get variation details by ID
     */
    public function getVariation($id)
    {
        $variation = ProductVariation::with('product')->findOrFail($id);

        return response()->json([
            'variation' => [
                'id' => $variation->id,
                'product_id' => $variation->product_id,
                'name' => $variation->name,
                'value' => $variation->value,
                'display_name' => $variation->display_name,
                'price' => $variation->price,
                'sale_price' => $variation->sale_price,
                'effective_price' => $variation->effective_price,
                'is_on_sale' => $variation->is_on_sale,
                'stock_quantity' => $variation->stock_quantity,
                'sku' => $variation->sku,
                'is_available' => $variation->stock_quantity > 0,
                'product' => [
                    'id' => $variation->product->id,
                    'name' => $variation->product->name,
                    'slug' => $variation->product->slug,
                    'image' => $variation->product->image,
                ]
            ]
        ]);
    }
}
