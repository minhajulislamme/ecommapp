<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    /**
     * Display a listing of all categories.
     */
    public function index(): Response
    {
        $categories = Category::with(['subCategories' => function ($query) {
            $query->where('is_active', true)->orderBy('name');
        }])
            ->where('is_active', true)
            ->withCount([
                'subCategories' => function ($query) {
                    $query->where('is_active', true);
                },
                'products' => function ($query) {
                    $query->where('is_active', true);
                }
            ])
            ->orderBy('name')
            ->get();

        return Inertia::render('Public/Categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Display products for a specific category.
     */
    public function show(Category $category): Response
    {
        $products = $category->products()
            ->with(['category', 'subCategory'])
            ->where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $subCategories = $category->subCategories()
            ->where('is_active', true)
            ->withCount([
                'products' => function ($query) {
                    $query->where('is_active', true);
                }
            ])
            ->orderBy('name')
            ->get();

        return Inertia::render('Public/CategoryProducts', [
            'category' => $category,
            'products' => $products,
            'subCategories' => $subCategories,
        ]);
    }
}
