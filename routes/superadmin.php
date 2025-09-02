<?php

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\UserManagementController;
use App\Http\Controllers\MailNotificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
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
        Route::delete('/{user}/remove-image', [UserManagementController::class, 'removeImage'])->name('remove-image');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        Route::delete('/', [UserManagementController::class, 'bulkDestroy'])->name('bulk-destroy');

        // Mail notification routes
        Route::post('/{user}/send-welcome-email', [MailNotificationController::class, 'sendWelcomeEmail'])->name('send-welcome-email');
        Route::post('/{user}/send-password-reset-notification', [MailNotificationController::class, 'sendPasswordResetNotification'])->name('send-password-reset-notification');
        Route::post('/{user}/send-status-notification/{status}', [MailNotificationController::class, 'sendAccountStatusNotification'])->name('send-status-notification');
    });

    // General mail routes
    Route::post('/send-test-email', [MailNotificationController::class, 'sendTestEmail'])->name('send-test-email');
});
