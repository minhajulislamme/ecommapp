<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Get all categories for client-side filtering
        $categories = Category::withCount('subCategories')
            ->orderBy('created_at', 'desc')
            ->get();

        // Create a paginated structure for consistency
        $paginatedCategories = [
            'data' => $categories,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $categories->count(),
            'total' => $categories->count(),
        ];

        return Inertia::render('admin/category/CategoryManagement', [
            'categories' => $paginatedCategories,
            'filters' => $request->only('search', 'status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/category/CreateCategory');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_active' => ['boolean'],
        ]);

        $categoryData = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $categoryData['image'] = $this->imageUploadService->uploadImage(
                $request->file('image'),
                'categories'
            );
        }

        $category = Category::create($categoryData);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' has been created successfully.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): Response
    {
        return Inertia::render('admin/category/EditCategory', [
            'category' => $category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_active' => ['boolean'],
            'remove_image' => ['boolean'],
        ]);

        $categoryData = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'is_active' => $validated['is_active'] ?? $category->is_active,
        ];

        // Handle image removal
        if ($request->input('remove_image')) {
            if ($category->image) {
                $this->imageUploadService->deleteImage('categories', $category->image);
            }
            $categoryData['image'] = null;
        }
        // Handle image upload (only if not removing)
        elseif ($request->hasFile('image')) {
            // Delete old image if exists
            if ($category->image) {
                $this->imageUploadService->deleteImage('categories', $category->image);
            }

            $categoryData['image'] = $this->imageUploadService->uploadImage(
                $request->file('image'),
                'categories'
            );
        }

        $category->update($categoryData);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$category->name}' has been updated successfully.");
    }

    /**
     * Toggle category status.
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Category '{$category->name}' has been {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Get category name before deletion
        $categoryName = $category->name;

        // Get all sub-categories with images that will be deleted
        $subCategoriesWithImages = $category->subCategories()
            ->whereNotNull('image')
            ->get();

        // Delete sub-category images first
        foreach ($subCategoriesWithImages as $subCategory) {
            $this->imageUploadService->deleteImage('subcategories', $subCategory->image);
        }

        // Delete category image if exists
        if ($category->image) {
            $this->imageUploadService->deleteImage('categories', $category->image);
        }

        // Delete the category (this will cascade delete sub-categories due to foreign key constraint)
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Category '{$categoryName}' and all its sub-categories have been deleted successfully.");
    }
}
