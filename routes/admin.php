<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\MailNotificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Demo page for toast notifications
    Route::get('/notification-demo', function () {
        return Inertia::render('admin/NotificationDemo');
    })->name('notification-demo');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');

        // Mail notification routes
        Route::post('/{user}/send-welcome-email', [MailNotificationController::class, 'sendWelcomeEmail'])->name('send-welcome-email');
        Route::post('/{user}/send-password-reset-notification', [MailNotificationController::class, 'sendPasswordResetNotification'])->name('send-password-reset-notification');
        Route::post('/{user}/send-status-notification/{status}', [MailNotificationController::class, 'sendAccountStatusNotification'])->name('send-status-notification');
    });

    // Category management routes
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Sub-category management routes
    Route::prefix('subcategories')->name('subcategories.')->group(function () {
        Route::get('/', [SubCategoryController::class, 'index'])->name('index');
        Route::get('/create', [SubCategoryController::class, 'create'])->name('create');
        Route::post('/', [SubCategoryController::class, 'store'])->name('store');
        Route::get('/{subcategory}/edit', [SubCategoryController::class, 'edit'])->name('edit');
        Route::put('/{subcategory}', [SubCategoryController::class, 'update'])->name('update');
        Route::patch('/{subcategory}/toggle-status', [SubCategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{subcategory}', [SubCategoryController::class, 'destroy'])->name('destroy');

        // API route for getting subcategories by category
        Route::get('/by-category/{categoryId}', [SubCategoryController::class, 'getByCategory'])->name('by-category');
    });

    // General mail routes
    Route::post('/send-test-email', [MailNotificationController::class, 'sendTestEmail'])->name('send-test-email');
});
