<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of products with optional filtering.
     */
    public function index(Request $request): Response
    {
        $query = Product::with(['category', 'subCategory'])
            ->where('is_active', true);

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Apply subcategory filter
        if ($request->filled('subcategory')) {
            $query->where('sub_category_id', $request->subcategory);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%");
            });
        }

        // Apply price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Apply sorting
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'featured':
                $query->orderBy('is_featured', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $products = $query->paginate(12)->withQueryString();

        // Get filter options
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();

        $subCategories = SubCategory::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true);
            }])
            ->having('products_count', '>', 0)
            ->orderBy('name')
            ->get();

        return Inertia::render('Public/Products', [
            'products' => $products,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'filters' => $request->only(['category', 'subcategory', 'search', 'min_price', 'max_price', 'sort']),
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): Response
    {
        // Load relationships
        $product->load(['category', 'subCategory']);

        // Get related products from the same category
        $relatedProducts = Product::with(['category', 'subCategory'])
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderBy('is_featured', 'desc')
            ->take(4)
            ->get();

        // Get other products from the same subcategory if exists
        if ($product->sub_category_id) {
            $similarProducts = Product::with(['category', 'subCategory'])
                ->where('is_active', true)
                ->where('sub_category_id', $product->sub_category_id)
                ->where('id', '!=', $product->id)
                ->take(4)
                ->get();
        } else {
            $similarProducts = collect();
        }

        return Inertia::render('Public/ProductDetail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'similarProducts' => $similarProducts,
        ]);
    }
}
