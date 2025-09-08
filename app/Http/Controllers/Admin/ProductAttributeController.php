<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductAttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Get all product attributes for client-side filtering
        $attributes = ProductAttribute::orderBy('created_at', 'desc')->get();

        // Create a paginated structure for consistency
        $paginatedAttributes = [
            'data' => $attributes,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $attributes->count(),
            'total' => $attributes->count(),
        ];

        return Inertia::render('admin/product-attribute/ProductAttributeManagement', [
            'attributes' => $paginatedAttributes,
            'filters' => $request->only('search', 'type', 'status'),
            'typeOptions' => ProductAttribute::getTypeOptions(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/product-attribute/CreateProductAttribute', [
            'typeOptions' => ProductAttribute::getTypeOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'in:text,number,color'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string', 'max:255'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $attributeData = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'type' => $validated['type'],
            'options' => !empty($validated['options']) ? array_filter($validated['options']) : null,
            'is_required' => $validated['is_required'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ];

        $attribute = ProductAttribute::create($attributeData);

        return redirect()->route('admin.product-attributes.index')
            ->with('success', "Product attribute '{$attribute->name}' has been created successfully.");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductAttribute $productAttribute): Response
    {
        return Inertia::render('admin/product-attribute/EditProductAttribute', [
            'attribute' => $productAttribute,
            'typeOptions' => ProductAttribute::getTypeOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductAttribute $productAttribute): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'in:text,number,color'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string', 'max:255'],
            'is_required' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $attributeData = [
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
            'type' => $validated['type'],
            'options' => !empty($validated['options']) ? array_filter($validated['options']) : null,
            'is_required' => $validated['is_required'] ?? $productAttribute->is_required,
            'is_active' => $validated['is_active'] ?? $productAttribute->is_active,
        ];

        $productAttribute->update($attributeData);

        return redirect()->route('admin.product-attributes.index')
            ->with('success', "Product attribute '{$productAttribute->name}' has been updated successfully.");
    }

    /**
     * Toggle attribute status.
     */
    public function toggleStatus(ProductAttribute $productAttribute): RedirectResponse
    {
        $productAttribute->update(['is_active' => !$productAttribute->is_active]);

        $status = $productAttribute->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Product attribute '{$productAttribute->name}' has been {$status} successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductAttribute $productAttribute): RedirectResponse
    {
        $attributeName = $productAttribute->name;
        $productAttribute->delete();

        return redirect()->route('admin.product-attributes.index')
            ->with('success', "Product attribute '{$attributeName}' has been deleted successfully.");
    }
}
