<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{

    public function index(): Response
    {
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_superadmins' => User::where('role', 'superadmin')->count(),
            'total_regular_users' => User::where('role', 'user')->count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];

        // Get all users for client-side filtering
        $users = User::orderBy('created_at', 'desc')->get();

        return Inertia::render('superadmin/UserManagement', [
            'stats' => $stats,
            'users' => $users,
        ]);
    }
}
