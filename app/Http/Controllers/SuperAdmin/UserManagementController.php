<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

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

        return Inertia::render('superadmin/user/UserManagement', [
            'stats' => $stats,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        return Inertia::render('superadmin/user/CreateUser');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['user', 'admin', 'superadmin'])],
            'is_active' => ['boolean'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'email_verified_at' => now(), // Auto-verify email for admin-created users
        ];

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $imagePath = $this->imageUploadService->uploadUserImage($request->file('profile_image'));
            $userData['profile_image'] = $imagePath;
        }

        $user = User::create($userData);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('superadmin/user/EditUser', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['user', 'admin', 'superadmin'])],
            'is_active' => ['boolean'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'profile_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
            'phone' => $validated['phone'],
            'address' => $validated['address'],
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($user->profile_image) {
                $this->imageUploadService->deleteUserImage($user->profile_image);
            }

            $imagePath = $this->imageUploadService->uploadUserImage($request->file('profile_image'));
            $updateData['profile_image'] = $imagePath;
        }

        $user->update($updateData);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): Response
    {
        return Inertia::render('superadmin/user/ViewUser', [
            'user' => $user,
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'You cannot delete your own account!');
        }

        $userName = $user->name;

        // Delete profile image if exists
        if ($user->profile_image) {
            $this->imageUploadService->deleteUserImage($user->profile_image);
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', "User '{$userName}' has been deleted successfully!");
    }

    /**
     * Remove multiple users from storage.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'integer', 'exists:users,id'],
        ]);

        $userIds = $validated['user_ids'];

        // Prevent deleting yourself
        if (in_array(Auth::id(), $userIds)) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'You cannot delete your own account!');
        }

        // Get users to delete their profile images
        $usersToDelete = User::whereIn('id', $userIds)->get();

        foreach ($usersToDelete as $user) {
            if ($user->profile_image) {
                $this->imageUploadService->deleteUserImage($user->profile_image);
            }
        }

        $deletedCount = $usersToDelete->count();
        User::whereIn('id', $userIds)->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', "{$deletedCount} user(s) have been deleted successfully!");
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        // Prevent deactivating yourself
        if ($user->id === Auth::id()) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'You cannot deactivate your own account!');
        }

        $user->update([
            'is_active' => !$user->is_active
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->route('superadmin.users.index')
            ->with('success', "User '{$user->name}' has been {$status} successfully!");
    }

    /**
     * Remove user profile image
     */
    public function removeImage(User $user): RedirectResponse
    {
        if ($user->profile_image) {
            // Delete the image file
            $this->imageUploadService->deleteUserImage($user->profile_image);

            // Update user record to remove image reference
            $user->update(['profile_image' => null]);

            return redirect()->back()
                ->with('success', 'Profile image removed successfully!');
        }

        return redirect()->back()
            ->with('error', 'No profile image to remove!');
    }
}
