<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class MailNotificationController extends Controller
{
    /**
     * Send a test email to the authenticated user.
     */
    public function sendTestEmail(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();

            // You can replace this with your actual mail class
            Mail::raw(
                "Hello {$user->name},\n\nThis is a test email from your application.\n\nBest regards,\nThe Team",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Test Email Notification');
                }
            );

            return redirect()->back()
                ->with('success', 'Test email sent successfully to ' . $user->email);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    /**
     * Send a welcome email to a specific user.
     */
    public function sendWelcomeEmail(User $user): RedirectResponse
    {
        try {
            Mail::raw(
                "Welcome {$user->name}!\n\nYour account has been created successfully.\n\nLogin Details:\nEmail: {$user->email}\n\nPlease change your password after first login.\n\nBest regards,\nThe Team",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Welcome to Our Platform');
                }
            );

            return redirect()->back()
                ->with('success', 'Welcome email sent successfully to ' . $user->name);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send welcome email: ' . $e->getMessage());
        }
    }

    /**
     * Send password reset notification.
     */
    public function sendPasswordResetNotification(User $user): RedirectResponse
    {
        try {
            Mail::raw(
                "Hello {$user->name},\n\nYour password has been reset successfully.\n\nIf you did not request this change, please contact our support team immediately.\n\nBest regards,\nThe Team",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('Password Reset Notification');
                }
            );

            return redirect()->back()
                ->with('success', 'Password reset notification sent to ' . $user->name);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send password reset notification: ' . $e->getMessage());
        }
    }

    /**
     * Send account status change notification.
     */
    public function sendAccountStatusNotification(User $user, string $status): RedirectResponse
    {
        try {
            $statusMessage = $status === 'activated'
                ? "Your account has been activated and you can now log in to the platform."
                : "Your account has been deactivated. Please contact support if you have any questions.";

            Mail::raw(
                "Hello {$user->name},\n\n{$statusMessage}\n\nBest regards,\nThe Team",
                function ($message) use ($user, $status) {
                    $message->to($user->email)
                        ->subject('Account Status Update - ' . ucfirst($status));
                }
            );

            return redirect()->back()
                ->with('success', 'Account status notification sent to ' . $user->name);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send account status notification: ' . $e->getMessage());
        }
    }
}
