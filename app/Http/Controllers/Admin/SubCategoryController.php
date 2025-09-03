<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SubCategoryController extends Controller
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
        // Get all sub-categories for client-side filtering
        $subCategories = SubCategory::with('category')
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = Category::active()->orderBy('name')->get();

        // Create a paginated structure for consistency
        $paginatedSubCategories = [
            'data' => $subCategories,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $subCategories->count(),
            'total' => $subCategories->count(),
        ];

        return Inertia::render('admin/subcategory/SubCategoryManagement', [
            'subCategories' => $paginatedSubCategories,
            'categories' => $categories,
            'filters' => $request->only('search', 'category_id', 'status'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $categories = Category::active()->orderBy('name')->get();

        return Inertia::render('admin/subcategory/CreateSubCategory', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_active' => ['boolean'],
        ]);
        $subCategoryData = [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'is_active' => $validated['is_active'] ?? true,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $subCategoryData['image'] = $this->imageUploadService->uploadImage(
                $request->file('image'),
                'subcategories'
            );
        }

        $subCategory = SubCategory::create($subCategoryData);
        $subCategory->load('category');

        return redirect()->route('admin.subcategories.index')
            ->with('success', "Sub-category '{$subCategory->name}' has been created successfully.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubCategory $subcategory): Response
    {
        $categories = Category::active()->orderBy('name')->get();

        return Inertia::render('admin/subcategory/EditSubCategory', [
            'subCategory' => $subcategory,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubCategory $subcategory): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'is_active' => ['boolean'],
            'remove_image' => ['boolean'],
        ]);

        $subCategoryData = [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'is_active' => $validated['is_active'] ?? $subcategory->is_active,
        ];

        // Handle image removal
        if ($request->input('remove_image')) {
            if ($subcategory->image) {
                $this->imageUploadService->deleteImage('subcategories', $subcategory->image);
            }
            $subCategoryData['image'] = null;
        }
        // Handle image upload (only if not removing)
        elseif ($request->hasFile('image')) {
            // Delete old image if exists
            if ($subcategory->image) {
                $this->imageUploadService->deleteImage('subcategories', $subcategory->image);
            }

            $subCategoryData['image'] = $this->imageUploadService->uploadImage(
                $request->file('image'),
                'subcategories'
            );
        }

        $subcategory->update($subCategoryData);

        return redirect()->route('admin.subcategories.index')
            ->with('success', "Sub-category '{$subcategory->name}' has been updated successfully.");
    }

    /**
     * Toggle sub-category status.
     */
    public function toggleStatus(SubCategory $subcategory): RedirectResponse
    {
        $subcategory->update(['is_active' => !$subcategory->is_active]);

        $status = $subcategory->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Sub-category '{$subcategory->name}' has been {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubCategory $subcategory): RedirectResponse
    {
        // Delete image if exists
        if ($subcategory->image) {
            $this->imageUploadService->deleteImage('subcategories', $subcategory->image);
        }

        $subCategoryName = $subcategory->name;
        $subcategory->delete();

        return redirect()->route('admin.subcategories.index')
            ->with('success', "Sub-category '{$subCategoryName}' has been deleted successfully.");
    }

    /**
     * Get sub-categories by category ID (API endpoint)
     */
    public function getByCategory($categoryId)
    {
        $subCategories = SubCategory::where('category_id', $categoryId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json($subCategories);
    }
}
