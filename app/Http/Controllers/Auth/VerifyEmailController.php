<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false))
                ->with('info', 'Your email was already verified.');
        }

        $request->fulfill();

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Thank you! Your email has been verified successfully.');
    }
}
