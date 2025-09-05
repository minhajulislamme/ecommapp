<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): Response
    {
        $stats = [
            'total_users' => User::where('role', 'user')->count(),
            'active_users' => User::where('role', 'user')->where('is_active', true)->count(),
            'inactive_users' => User::where('role', 'user')->where('is_active', false)->count(),
            'total_products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
            'featured_products' => Product::where('is_featured', true)->count(),
            'total_categories' => Category::count(),
            'low_stock_products' => Product::where('stock_quantity', '<', 10)->count(),
        ];

        $recent_users = User::where('role', 'user')->latest()->take(5)->get();

        return Inertia::render('admin/Dashboard', [
            'stats' => $stats,
            'recent_users' => $recent_users,
        ]);
    }
}
