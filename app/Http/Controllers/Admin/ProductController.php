<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Category;
use App\Models\SubCategory;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Get all products for client-side filtering
        $products = Product::with(['category', 'subCategory', 'variations'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($product) {
                $product->has_variations = $product->variations->count() > 0;
                $product->variations_count = $product->variations->count();
                $product->total_stock = $product->hasVariations() ? $product->total_stock : $product->stock_quantity;
                $product->price_range = $product->hasVariations() ? $product->price_range : null;
                return $product;
            });

        $categories = Category::active()->orderBy('name')->get();

        // Create a paginated structure for consistency
        $paginatedProducts = [
            'data' => $products,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $products->count(),
            'total' => $products->count(),
        ];

        return Inertia::render('admin/product/ProductManagement', [
            'products' => $paginatedProducts,
            'categories' => $categories,
            'filters' => $request->only('search', 'category_id', 'status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $categories = Category::active()->orderBy('name')->get();

        return Inertia::render('admin/product/CreateProductAdvanced', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'product_type' => ['required', 'in:simple,variation'],
            // Simple product fields
            'price' => ['required_if:product_type,simple', 'nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required_if:product_type,simple', 'nullable', 'integer', 'min:0'],
            // Image fields
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'gallery_images' => ['nullable', 'array', 'max:5'],
            'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            // Status fields
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            // Variation fields
            'variations' => ['required_if:product_type,variation', 'nullable', 'array', 'min:1'],
            'variations.*.display_name' => ['required_if:product_type,variation', 'string', 'max:255'],
            'variations.*.attribute_values' => ['required_if:product_type,variation', 'array'],
            'variations.*.price' => ['required_if:product_type,variation', 'numeric', 'min:0'],
            'variations.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variations.*.stock_quantity' => ['required_if:product_type,variation', 'integer', 'min:0'],
            'variations.*.sku' => ['nullable', 'string', 'max:255'],
            'variations.*.is_active' => ['boolean'],
        ]);

        try {
            // Debug: Log the incoming request data
            Log::info('Product creation request:', [
                'product_type' => $validated['product_type'],
                'variations' => $validated['variations'] ?? null,
                'variations_count' => isset($validated['variations']) ? count($validated['variations']) : 0,
            ]);

            $productData = [
                'category_id' => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'],
                'name' => $validated['name'],
                'slug' => $this->generateUniqueSlug($validated['name']),
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? true,
                'is_featured' => $validated['is_featured'] ?? false,
            ];

            // Set default pricing for simple products or placeholder for variable products
            if ($validated['product_type'] === 'simple') {
                $productData['price'] = $validated['price'];
                $productData['sale_price'] = $validated['sale_price'];
                $productData['stock_quantity'] = $validated['stock_quantity'];
            } else {
                // For variable products, set default values that will be overridden by variations
                $productData['price'] = 0;
                $productData['sale_price'] = null;
                $productData['stock_quantity'] = 0;
            }

            // Handle image uploads
            $imageResults = $this->imageUploadService->uploadProductImages(
                $request->file('image'),
                $request->file('gallery_images', [])
            );

            $productData['image'] = $imageResults['main_image'];
            $productData['images'] = $imageResults['gallery_images'];

            $product = Product::create($productData);

            // Create variations if this is a variable product
            if ($validated['product_type'] === 'variation' && !empty($validated['variations'])) {
                foreach ($validated['variations'] as $variationData) {
                    // Generate SKU if not provided
                    if (empty($variationData['sku'])) {
                        $baseSku = Str::slug($validated['name']);
                        $variationSku = Str::slug($variationData['display_name']);
                        $variationData['sku'] = $baseSku . '-' . $variationSku;
                    }

                    // Ensure unique SKU
                    $originalSku = $variationData['sku'];
                    $counter = 1;
                    while (ProductVariation::where('sku', $variationData['sku'])->exists()) {
                        $variationData['sku'] = $originalSku . '-' . $counter;
                        $counter++;
                    }

                    // Ensure all required fields are present
                    $variationCreateData = [
                        'product_id' => $product->id,
                        'display_name' => $variationData['display_name'] ?? '',
                        'attribute_values' => $variationData['attribute_values'] ?? '',
                        'price' => $variationData['price'] ?? 0,
                        'sale_price' => !empty($variationData['sale_price']) ? $variationData['sale_price'] : null,
                        'stock_quantity' => $variationData['stock_quantity'] ?? 0,
                        'sku' => $variationData['sku'],
                        'is_active' => $variationData['is_active'] ?? true,
                    ];

                    ProductVariation::create($variationCreateData);
                }
            }

            $message = $validated['product_type'] === 'variation'
                ? "Product '{$product->name}' with " . count($validated['variations'] ?? []) . " variations has been created successfully."
                : "Product '{$product->name}' has been created successfully.";

            return redirect()->route('admin.products.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Response
    {
        $categories = Category::active()->orderBy('name')->get();
        $subCategories = SubCategory::where('category_id', $product->category_id)
            ->active()
            ->orderBy('name')
            ->get();

        // Load product with its variations and determine product type
        $product->load(['category', 'subCategory', 'variations']);

        // Add product type and formatted variations
        $productData = $product->toArray();
        $productData['product_type'] = $product->variations->count() > 0 ? 'variation' : 'simple';
        $productData['formatted_variations'] = $product->variations->map(function ($variation) {
            return [
                'id' => $variation->id,
                'display_name' => $variation->display_name,
                'attribute_values' => $variation->attribute_values,
                'price' => $variation->price,
                'sale_price' => $variation->sale_price,
                'stock_quantity' => $variation->stock_quantity,
                'sku' => $variation->sku,
                'is_active' => $variation->is_active,
                'is_deleted' => false,
            ];
        })->toArray();

        return Inertia::render('admin/product/CreateProductAdvanced', [
            'product' => $productData,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'category_id' => ['required', 'exists:categories,id'],
                'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'product_type' => ['required', 'in:simple,variation'],
                // Simple product fields
                'price' => ['required_if:product_type,simple', 'nullable', 'numeric', 'min:0'],
                'sale_price' => ['nullable', 'numeric', 'min:0'],
                'stock_quantity' => ['required_if:product_type,simple', 'nullable', 'integer', 'min:0'],
                // Image fields
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'gallery_images' => ['nullable', 'array', 'max:5'],
                'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                // Status fields
                'is_active' => ['boolean'],
                'is_featured' => ['boolean'],
                'remove_image' => ['boolean'],
                'remove_gallery_images' => ['nullable', 'array'],
                'remove_gallery_images.*' => ['string'],
                // Variation fields
                'variations' => ['required_if:product_type,variation', 'nullable', 'array', 'min:1'],
                'variations.*.id' => ['nullable', 'integer'],
                'variations.*.display_name' => ['required_if:product_type,variation', 'string', 'max:255'],
                'variations.*.attribute_values' => ['required_if:product_type,variation', 'array'],
                'variations.*.price' => ['required_if:product_type,variation', 'numeric', 'min:0'],
                'variations.*.sale_price' => ['nullable', 'numeric', 'min:0'],
                'variations.*.stock_quantity' => ['required_if:product_type,variation', 'integer', 'min:0'],
                'variations.*.sku' => ['nullable', 'string', 'max:255'],
                'variations.*.is_active' => ['boolean'],
                'variations.*.is_deleted' => ['boolean'], // For marking variations for deletion
            ]);

            $productData = [
                'category_id' => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? $product->is_active,
                'is_featured' => $validated['is_featured'] ?? $product->is_featured,
            ];

            // Set pricing based on product type
            if ($validated['product_type'] === 'simple') {
                $productData['price'] = $validated['price'];
                $productData['sale_price'] = $validated['sale_price'];
                $productData['stock_quantity'] = $validated['stock_quantity'];
            } else {
                // For variable products, calculate min/max prices from variations
                $variations = $validated['variations'] ?? [];
                $prices = array_column($variations, 'price');
                $productData['price'] = count($prices) > 0 ? min($prices) : 0;
                $productData['sale_price'] = null;
                $productData['stock_quantity'] = array_sum(array_column($variations, 'stock_quantity'));
            }

            // Handle main image removal
            if ($request->input('remove_image')) {
                if ($product->image) {
                    $this->imageUploadService->deleteImage('products', $product->image);
                }
                $productData['image'] = null;
            }
            // Handle main image upload (only if not removing)
            elseif ($request->hasFile('image')) {
                try {
                    $productData['image'] = $this->imageUploadService->uploadImage(
                        $request->file('image'),
                        'products',
                        $product->image
                    );
                } catch (\Exception $e) {
                    Log::error('Main image upload failed: ' . $e->getMessage());
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['image' => 'Failed to upload main image: ' . $e->getMessage()]);
                }
            }

            // Handle gallery images removal
            $currentGalleryImages = $product->images ?? [];
            $imagesToRemove = $request->input('remove_gallery_images', []);

            if (!empty($imagesToRemove)) {
                // Delete files from storage
                $this->imageUploadService->deleteGalleryImages($imagesToRemove, 'products');

                // Update gallery array
                $currentGalleryImages = array_values(array_filter($currentGalleryImages, function ($image) use ($imagesToRemove) {
                    return !in_array($image, $imagesToRemove);
                }));
            }

            // Handle new gallery images upload
            if ($request->hasFile('gallery_images')) {
                try {
                    $newGalleryImages = $this->imageUploadService->uploadGalleryImages(
                        $request->file('gallery_images'),
                        'products'
                    );

                    // Merge with existing gallery images
                    $currentGalleryImages = array_merge($currentGalleryImages, $newGalleryImages);
                } catch (\Exception $e) {
                    Log::error('Gallery images upload failed: ' . $e->getMessage());
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['gallery_images' => 'Failed to upload gallery images: ' . $e->getMessage()]);
                }
            }

            $productData['images'] = $currentGalleryImages;

            // Update the product
            $product->update($productData);

            // Handle variations if product type is variation
            if ($validated['product_type'] === 'variation' && !empty($validated['variations'])) {
                $existingVariationIds = [];

                foreach ($validated['variations'] as $variationData) {
                    // Skip deleted variations
                    if (isset($variationData['is_deleted']) && $variationData['is_deleted']) {
                        if (isset($variationData['id'])) {
                            ProductVariation::find($variationData['id'])?->delete();
                        }
                        continue;
                    }

                    // Generate SKU if not provided
                    if (empty($variationData['sku'])) {
                        $baseSku = Str::slug($validated['name']);
                        $variationSku = Str::slug($variationData['display_name']);
                        $variationData['sku'] = $baseSku . '-' . $variationSku . '-' . strtoupper(Str::random(4));
                    }

                    // Prepare variation data
                    $variationUpdateData = [
                        'product_id' => $product->id,
                        'display_name' => $variationData['display_name'],
                        'attribute_values' => $variationData['attribute_values'],
                        'price' => $variationData['price'],
                        'sale_price' => !empty($variationData['sale_price']) ? $variationData['sale_price'] : null,
                        'stock_quantity' => $variationData['stock_quantity'],
                        'sku' => $variationData['sku'],
                        'is_active' => $variationData['is_active'] ?? true,
                    ];

                    if (isset($variationData['id']) && $variationData['id']) {
                        // Update existing variation
                        $variation = ProductVariation::find($variationData['id']);
                        if ($variation) {
                            $variation->update($variationUpdateData);
                            $existingVariationIds[] = $variation->id;
                        }
                    } else {
                        // Create new variation
                        $variation = ProductVariation::create($variationUpdateData);
                        $existingVariationIds[] = $variation->id;
                    }
                }

                // Delete variations that are no longer in the submitted data
                $product->variations()->whereNotIn('id', $existingVariationIds)->delete();
            } else {
                // If switching to simple product, delete all variations
                $product->variations()->delete();
            }

            $message = $validated['product_type'] === 'variation'
                ? "Product '{$product->name}' with variations has been updated successfully."
                : "Product '{$product->name}' has been updated successfully.";

            return redirect()->route('admin.products.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle product status.
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Product '{$product->name}' has been {$status} successfully.");
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Product $product): RedirectResponse
    {
        $product->update(['is_featured' => !$product->is_featured]);

        $status = $product->is_featured ? 'marked as featured' : 'unmarked as featured';

        return redirect()->back()
            ->with('success', "Product '{$product->name}' has been {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Delete all product images (main + gallery)
        $this->imageUploadService->deleteProductImages(
            $product->image,
            $product->images ?? []
        );

        $productName = $product->name;
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$productName}' has been deleted successfully.");
    }

    /**
     * Get subcategories by category for AJAX requests
     */
    public function getSubcategoriesByCategory(Request $request, $categoryId)
    {
        $subCategories = SubCategory::where('category_id', $categoryId)
            ->active()
            ->orderBy('name')
            ->get();

        return response()->json($subCategories);
    }

    /**
     * Generate a unique slug for the product
     */
    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
