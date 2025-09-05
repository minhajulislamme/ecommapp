<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import { ArrowLeft, Save, Upload, X } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

interface Category {
    id: number;
    name: string;
}

interface SubCategory {
    id: number;
    name: string;
    category_id: number;
}

interface Product {
    id: number;
    category_id: number;
    sub_category_id: number | null;
    name: string;
    description: string | null;
    price: number;
    sale_price: number | null;
    stock_quantity: number;
    image: string | null;
    is_active: boolean;
    is_featured: boolean;
    category?: Category;
    subCategory?: SubCategory;
}

interface Props {
    product: Product;
    categories: Category[];
    subCategories: SubCategory[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const subCategories = ref<SubCategory[]>(props.subCategories || []);
const imagePreview = ref<string | null>(null);
const currentImage = ref<string | null>(props.product.image);

const form = useForm({
    category_id: props.product.category_id,
    sub_category_id: props.product.sub_category_id || '',
    name: props.product.name,
    description: props.product.description || '',
    price: props.product.price,
    sale_price: props.product.sale_price || '',
    stock_quantity: props.product.stock_quantity,
    image: null as File | null,
    is_active: props.product.is_active,
    is_featured: props.product.is_featured,
    remove_image: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Product Management', href: '/admin/products' },
    { title: 'Edit Product', href: `/admin/products/${props.product.id}/edit` },
];

// Set initial image preview if product has an image
onMounted(() => {
    if (currentImage.value) {
        imagePreview.value = `/upload/products/${currentImage.value}`;
    }
});

// Watch category change to load subcategories
watch(() => form.category_id, async (newCategoryId) => {
    form.sub_category_id = '';
    subCategories.value = [];
    
    if (newCategoryId) {
        try {
            const response = await fetch(`/admin/products/subcategories/${newCategoryId}`);
            if (response.ok) {
                subCategories.value = await response.json();
                
                // If the product has a subcategory and it belongs to the new category, restore it
                if (props.product.sub_category_id && props.product.category_id == newCategoryId) {
                    const existingSubCat = subCategories.value.find(sub => sub.id === props.product.sub_category_id);
                    if (existingSubCat) {
                        form.sub_category_id = props.product.sub_category_id;
                    }
                }
            }
        } catch (error) {
            console.error('Failed to fetch subcategories:', error);
        }
    }
});

// Handle image selection
const handleImageChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file) {
        form.image = file;
        form.remove_image = false; // Reset remove flag when new image is selected
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

// Remove image
const removeImage = () => {
    form.image = null;
    form.remove_image = true;
    imagePreview.value = null;
    currentImage.value = null;
    
    // Reset file input
    const fileInput = document.getElementById('image') as HTMLInputElement;
    if (fileInput) {
        fileInput.value = '';
    }
};

// Submit form
const submit = () => {
    form.put(`/admin/products/${props.product.id}`, {
        onSuccess: () => {
            // Toast will be handled by global flash message handler
        },
        onError: () => {
            toast.error('Failed to update product. Please check the form and try again.', 'Error');
        },
    });
};
</script>

<template>
    <Head title="Edit Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Product</h1>
                    <p class="text-gray-600 dark:text-gray-400">Update product information</p>
                </div>
                <Link
                    href="/admin/products"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Products
                </Link>
            </div>

            <!-- Form -->
            <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Product Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Product Name *
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                placeholder="Enter product name"
                            />
                            <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
                        </div>

                        <!-- SKU -->
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                SKU
                            </label>
                            <input
                                id="sku"
                                type="text"
                                disabled
                                :value="product.sku || 'Auto-generated'"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-400"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">SKU cannot be changed after creation</p>
                        </div>
                    </div>

                    <!-- Category Selection -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Category -->
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category *
                            </label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Select a category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</div>
                        </div>

                        <!-- Sub-Category -->
                        <div>
                            <label for="sub_category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Sub-Category
                            </label>
                            <select
                                id="sub_category_id"
                                v-model="form.sub_category_id"
                                :disabled="!subCategories.length"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:disabled:bg-gray-600"
                            >
                                <option value="">Select a sub-category (optional)</option>
                                <option v-for="subCategory in subCategories" :key="subCategory.id" :value="subCategory.id">
                                    {{ subCategory.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.sub_category_id" class="mt-1 text-sm text-red-600">{{ form.errors.sub_category_id }}</div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="4"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="Enter product description"
                        />
                        <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</div>
                    </div>

                    <!-- Pricing -->
                    <div class="grid gap-6 md:grid-cols-3">
                        <!-- Regular Price -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Regular Price * ($)
                            </label>
                            <input
                                id="price"
                                v-model.number="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="0.00"
                            />
                            <div v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</div>
                        </div>

                        <!-- Sale Price -->
                        <div>
                            <label for="sale_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Sale Price ($)
                            </label>
                            <input
                                id="sale_price"
                                v-model.number="form.sale_price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="0.00"
                            />
                            <div v-if="form.errors.sale_price" class="mt-1 text-sm text-red-600">{{ form.errors.sale_price }}</div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave empty if no sale</p>
                        </div>

                        <!-- Stock Quantity -->
                        <div>
                            <label for="stock_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Stock Quantity *
                            </label>
                            <input
                                id="stock_quantity"
                                v-model.number="form.stock_quantity"
                                type="number"
                                min="0"
                                required
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="0"
                            />
                            <div v-if="form.errors.stock_quantity" class="mt-1 text-sm text-red-600">{{ form.errors.stock_quantity }}</div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Product Image
                        </label>
                        <div class="mt-1">
                            <!-- Current/Preview Image -->
                            <div v-if="imagePreview || currentImage" class="mb-4">
                                <div class="relative inline-block">
                                    <img
                                        :src="imagePreview || `/upload/products/${currentImage}`"
                                        alt="Product Image"
                                        class="h-32 w-32 rounded-lg object-cover"
                                    />
                                    <button
                                        type="button"
                                        @click="removeImage"
                                        class="absolute -right-2 -top-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- File Input -->
                            <div class="flex items-center gap-4">
                                <label
                                    for="image"
                                    class="cursor-pointer inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <Upload class="h-4 w-4" />
                                    {{ currentImage || imagePreview ? 'Change Image' : 'Choose Image' }}
                                </label>
                                <input
                                    id="image"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleImageChange"
                                />
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    PNG, JPG, GIF up to 2MB
                                </span>
                            </div>
                            <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</div>
                        </div>
                    </div>

                    <!-- Status Options -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Active Status -->
                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Active (product will be visible to customers)
                            </label>
                        </div>

                        <!-- Featured Status -->
                        <div class="flex items-center">
                            <input
                                id="is_featured"
                                v-model="form.is_featured"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                            />
                            <label for="is_featured" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Featured (product will appear in featured section)
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <Link
                            href="/admin/products"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            <Save class="h-4 w-4" />
                            {{ form.processing ? 'Updating...' : 'Update Product' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
