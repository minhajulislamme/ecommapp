<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the homepage with featured products and categories.
     */
    public function index(): Response
    {
        $featuredProducts = Product::with(['category', 'subCategory'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest()
            ->take(8)
            ->get();

        $categories = Category::with(['subCategories' => function ($query) {
            $query->where('is_active', true)->take(5);
        }])
            ->where('is_active', true)
            ->withCount('subCategories')
            ->orderBy('name')
            ->take(6)
            ->get();

        $latestProducts = Product::with(['category', 'subCategory'])
            ->where('is_active', true)
            ->latest()
            ->take(12)
            ->get();

        return Inertia::render('Public/Home', [
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'latestProducts' => $latestProducts,
        ]);
    }
}
