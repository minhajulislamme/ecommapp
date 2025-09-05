<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
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
        $products = Product::with(['category', 'subCategory'])
            ->orderBy('created_at', 'desc')
            ->get();

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

        return Inertia::render('admin/product/CreateProduct', [
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
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $productData = [
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'price' => $validated['price'],
            'sale_price' => $validated['sale_price'],
            'stock_quantity' => $validated['stock_quantity'],
            'is_active' => $validated['is_active'] ?? true,
            'is_featured' => $validated['is_featured'] ?? false,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $productData['image'] = $this->imageUploadService->uploadImage(
                $request->file('image'),
                'products'
            );
        }

        $product = Product::create($productData);

        return redirect()->route('admin.products.index')
            ->with('success', "Product '{$product->name}' has been created successfully.");
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

        return Inertia::render('admin/product/EditProduct', [
            'product' => $product->load(['category', 'subCategory']),
            'categories' => $categories,
            'subCategories' => $subCategories,
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
                'price' => ['required', 'numeric', 'min:0'],
                'sale_price' => ['nullable', 'numeric', 'min:0.01'],
                'stock_quantity' => ['required', 'integer', 'min:0'],
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'is_active' => ['boolean'],
                'is_featured' => ['boolean'],
                'remove_image' => ['boolean'],
            ]);

            $productData = [
                'category_id' => $validated['category_id'],
                'sub_category_id' => $validated['sub_category_id'],
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'price' => $validated['price'],
                'sale_price' => $validated['sale_price'],
                'stock_quantity' => $validated['stock_quantity'],
                'is_active' => $validated['is_active'] ?? $product->is_active,
                'is_featured' => $validated['is_featured'] ?? $product->is_featured,
            ];

            // Handle image removal
            if ($request->input('remove_image')) {
                if ($product->image) {
                    $this->imageUploadService->deleteImage('products', $product->image);
                }
                $productData['image'] = null;
            }
            // Handle image upload (only if not removing)
            elseif ($request->hasFile('image')) {
                try {
                    // Delete old image if exists
                    if ($product->image) {
                        $this->imageUploadService->deleteImage('products', $product->image);
                    }

                    $productData['image'] = $this->imageUploadService->uploadImage(
                        $request->file('image'),
                        'products'
                    );
                } catch (\Exception $e) {
                    Log::error('Image upload failed: ' . $e->getMessage());
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['image' => 'Failed to upload image: ' . $e->getMessage()]);
                }
            }

            $product->update($productData);

            return redirect()->route('admin.products.index')
                ->with('success', "Product '{$product->name}' has been updated successfully.");
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
        // Delete image if exists
        if ($product->image) {
            $this->imageUploadService->deleteImage('products', $product->image);
        }

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
}
