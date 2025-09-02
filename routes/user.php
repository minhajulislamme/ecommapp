<?php

use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\MailNotificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Demo page for toast notifications
    Route::get('/notification-demo', function () {
        return Inertia::render('admin/NotificationDemo');
    })->name('notification-demo');

    // Mail notification routes for users
    Route::post('/send-test-email', [MailNotificationController::class, 'sendTestEmail'])->name('send-test-email');
});
