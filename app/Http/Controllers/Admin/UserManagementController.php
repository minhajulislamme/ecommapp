<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    /**
     * Display users list (only regular users for admin).
     */
    public function index(Request $request): Response
    {
        $query = User::where('role', 'user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('admin/UserManagement', [
            'users' => $users,
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show user details.
     */
    public function show(User $user): Response
    {
        // Admin can only view regular users
        if (!$user->isUser()) {
            abort(403, 'Access denied.');
        }

        return Inertia::render('admin/UserDetails', [
            'user' => $user,
        ]);
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Admin can only manage regular users
        if (!$user->isUser()) {
            abort(403, 'Access denied.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('message', "User {$status} successfully.");
    }
}
