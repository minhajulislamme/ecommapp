<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ProductVariationController extends Controller
{
    /**
     * Display variations for a specific product.
     */
    public function index(Product $product): Response
    {
        $variations = $product->variations()
            ->orderBy('display_name')
            ->get()
            ->map(function ($variation) {
                return [
                    'id' => $variation->id,
                    'product_id' => $variation->product_id,
                    'sku' => $variation->sku,
                    'price' => $variation->price,
                    'sale_price' => $variation->sale_price,
                    'effective_price' => $variation->effective_price,
                    'stock_quantity' => $variation->stock_quantity,
                    'attribute_values' => $variation->attribute_values,
                    'display_name' => $variation->display_name,
                    'is_active' => $variation->is_active,
                ];
            });

        return Inertia::render('admin/product/variations/VariationManagement', [
            'product' => $product->only(['id', 'name', 'sku']),
            'variations' => $variations,
        ]);
    }

    /**
     * Show the form for creating a new variation.
     */
    public function create(Product $product): Response
    {
        return Inertia::render('admin/product/variations/CreateVariation', [
            'product' => $product->only(['id', 'name', 'sku']),
        ]);
    }

    /**
     * Show the form for bulk creating variations.
     */
    public function bulkCreate(Product $product): Response
    {
        return Inertia::render('admin/product/variations/BulkCreateVariations', [
            'product' => $product->only(['id', 'name', 'sku']),
        ]);
    }

    /**
     * Store a newly created variation.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'attribute_values' => ['required', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:product_variations,sku'],
            'is_active' => ['boolean'],
        ]);

        try {
            $variation = ProductVariation::create([
                'product_id' => $product->id,
                'display_name' => $validated['display_name'],
                'attribute_values' => $validated['attribute_values'],
                'price' => $validated['price'],
                'sale_price' => $validated['sale_price'],
                'stock_quantity' => $validated['stock_quantity'],
                'sku' => $validated['sku'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return redirect()->route('admin.products.variations.index', $product)
                ->with('success', "Variation '{$variation->display_name}' has been created successfully.");
        } catch (\Exception $e) {
            Log::error('Variation creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create variation: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing a variation.
     */
    public function edit(Product $product, ProductVariation $variation): Response
    {
        // Ensure the variation belongs to the product
        if ($variation->product_id !== $product->id) {
            abort(404);
        }

        return Inertia::render('admin/product/variations/EditVariation', [
            'product' => $product->only(['id', 'name', 'sku']),
            'variation' => [
                'id' => $variation->id,
                'display_name' => $variation->display_name,
                'attribute_values' => $variation->attribute_values,
                'sku' => $variation->sku,
                'price' => $variation->price,
                'sale_price' => $variation->sale_price,
                'stock_quantity' => $variation->stock_quantity,
                'is_active' => $variation->is_active,
            ],
        ]);
    }

    /**
     * Update the specified variation.
     */
    public function update(Request $request, Product $product, ProductVariation $variation): RedirectResponse
    {
        // Ensure the variation belongs to the product
        if ($variation->product_id !== $product->id) {
            abort(404);
        }

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'attribute_values' => ['required', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:product_variations,sku,' . $variation->id],
            'is_active' => ['boolean'],
        ]);

        try {
            $variation->update([
                'display_name' => $validated['display_name'],
                'attribute_values' => $validated['attribute_values'],
                'price' => $validated['price'],
                'sale_price' => $validated['sale_price'],
                'stock_quantity' => $validated['stock_quantity'],
                'sku' => $validated['sku'],
                'is_active' => $validated['is_active'] ?? $variation->is_active,
            ]);

            return redirect()->route('admin.products.variations.index', $product)
                ->with('success', "Variation '{$variation->display_name}' has been updated successfully.");
        } catch (\Exception $e) {
            Log::error('Variation update failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update variation: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle variation status.
     */
    public function toggleStatus(Product $product, ProductVariation $variation): RedirectResponse
    {
        // Ensure the variation belongs to the product
        if ($variation->product_id !== $product->id) {
            abort(404);
        }

        $variation->update(['is_active' => !$variation->is_active]);

        $status = $variation->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Variation '{$variation->display_name}' has been {$status} successfully.");
    }

    /**
     * Remove the specified variation.
     */
    public function destroy(Product $product, ProductVariation $variation): RedirectResponse
    {
        // Ensure the variation belongs to the product
        if ($variation->product_id !== $product->id) {
            abort(404);
        }

        $variationName = $variation->display_name;
        $variation->delete();

        return redirect()->route('admin.products.variations.index', $product)
            ->with('success', "Variation '{$variationName}' has been deleted successfully.");
    }

    /**
     * Bulk create variations from a form
     */
    public function bulkStore(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'variations' => ['required', 'array', 'min:1'],
            'variations.*.display_name' => ['required', 'string', 'max:255'],
            'variations.*.attribute_values' => ['required', 'string', 'max:500'],
            'variations.*.price' => ['required', 'numeric', 'min:0'],
            'variations.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variations.*.stock_quantity' => ['required', 'integer', 'min:0'],
            'variations.*.sku' => ['nullable', 'string', 'max:255'],
            'variations.*.is_active' => ['boolean'],
        ]);

        try {
            $createdCount = 0;
            foreach ($validated['variations'] as $variationData) {
                // Check for duplicate SKU if provided
                if (!empty($variationData['sku'])) {
                    $existingSku = ProductVariation::where('sku', $variationData['sku'])->exists();
                    if ($existingSku) {
                        continue; // Skip this variation if SKU already exists
                    }
                }

                ProductVariation::create([
                    'product_id' => $product->id,
                    'display_name' => $variationData['display_name'],
                    'attribute_values' => $variationData['attribute_values'],
                    'price' => $variationData['price'],
                    'sale_price' => $variationData['sale_price'],
                    'stock_quantity' => $variationData['stock_quantity'],
                    'sku' => $variationData['sku'],
                    'is_active' => $variationData['is_active'] ?? true,
                ]);

                $createdCount++;
            }

            return redirect()->route('admin.products.variations.index', $product)
                ->with('success', "{$createdCount} variations have been created successfully.");
        } catch (\Exception $e) {
            Log::error('Bulk variation creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create variations: ' . $e->getMessage()]);
        }
    }
}
