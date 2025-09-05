<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\ProductController;
use App\Models\ProductVariation;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/products', [ProductController::class, 'index'])->name('products');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// API routes for product variations
Route::get('/api/variations/{variation}', function (ProductVariation $variation) {
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
        ]
    ]);
})->name('api.variations.show');

// Legacy dashboard route - redirect based on role
Route::get('dashboard', function () {
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

    return match ($user->role) {
        'superadmin' => redirect()->route('superadmin.dashboard'),
        'admin' => redirect()->route('admin.dashboard'),
        'user' => redirect()->route('user.dashboard'),
        default => redirect()->route('user.dashboard'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/superadmin.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/user.php';
