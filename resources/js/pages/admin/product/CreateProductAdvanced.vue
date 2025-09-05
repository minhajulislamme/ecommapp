<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { ArrowLeft, Save, Upload, X, Plus, Trash2 } from 'lucide-vue-next';
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

interface ProductVariation {
    id?: number;
    display_name: string;
    attribute_values: string;
    price: number;
    sale_price: string | number;
    stock_quantity: number;
    sku: string;
    is_active: boolean;
}

interface ProductVariationSubmit {
    id?: number;
    display_name: string;
    attribute_values: Record<string, string>;
    price: number;
    sale_price: string | number;
    stock_quantity: number;
    sku: string;
    is_active: boolean;
}

interface FormData {
    category_id: string | number;
    sub_category_id: string | number;
    name: string;
    description: string;
    price: number;
    sale_price: string | number;
    stock_quantity: number;
    image: File | null;
    gallery_images: File[];
    is_active: boolean;
    is_featured: boolean;
    product_type: string;
    variations: ProductVariation[] | ProductVariationSubmit[];
}

interface Props {
    categories: Category[];
    product?: any;
    subCategories?: SubCategory[];
    isEdit?: boolean;
}

const props = defineProps<Props>();
const { toast } = useToast();

const subCategories = ref<SubCategory[]>(props.subCategories || []);
const imagePreview = ref<string | null>(null);
const galleryPreviews = ref<string[]>([]);
const productType = ref<'simple' | 'variation'>(props.product?.product_type || 'simple');

// Variation form data
const variations = ref<ProductVariation[]>(
    props.product?.formatted_variations?.map((variation: any) => ({
        ...variation,
        attribute_values: typeof variation.attribute_values === 'object' 
            ? stringifyAttributeValues(variation.attribute_values)
            : (variation.attribute_values || '')
    })) || [
        {
            display_name: '',
            attribute_values: '',
            price: 0,
            sale_price: '',
            stock_quantity: 0,
            sku: '',
            is_active: true,
        }
    ]
);

const form = useForm<FormData>({
    category_id: props.product?.category_id || '',
    sub_category_id: props.product?.sub_category_id || '',
    name: props.product?.name || '',
    description: props.product?.description || '',
    price: props.product?.price || 0,
    sale_price: props.product?.sale_price || '',
    stock_quantity: props.product?.stock_quantity || 0,
    image: null as File | null,
    gallery_images: [] as File[],
    is_active: props.product?.is_active ?? true,
    is_featured: props.product?.is_featured ?? false,
    product_type: props.product?.product_type || 'simple',
    variations: variations.value,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Product Management', href: '/admin/products' },
    { title: props.isEdit ? 'Edit Product' : 'Create Product', href: props.isEdit ? '' : '/admin/products/create' },
];

// Watch product type changes
watch(productType, (newType: 'simple' | 'variation') => {
    form.product_type = newType;
    if (newType === 'simple') {
        variations.value = [];
        // Remove variations from form for simple products
        delete (form as any).variations;
    } else {
        const newVariation: ProductVariation = {
            display_name: '',
            attribute_values: '',
            price: 0,
            sale_price: '',
            stock_quantity: 0,
            sku: '',
            is_active: true,
        };
        variations.value = [newVariation];
        form.variations = [newVariation];
    }
});

// Watch variations changes to update form
watch(variations, (newVariations: ProductVariation[]) => {
    form.variations = newVariations;
}, { deep: true });

// Watch category change to load subcategories
watch(() => form.category_id, async (newCategoryId) => {
    form.sub_category_id = '';
    subCategories.value = [];
    
    if (newCategoryId) {
        try {
            const response = await fetch(`/admin/products/subcategories/${newCategoryId}`);
            if (response.ok) {
                subCategories.value = await response.json();
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
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

// Handle gallery images selection
const handleGalleryImagesChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = Array.from(target.files || []);
    
    if (files.length > 0) {
        // Validate total gallery images (max 5)
        if (form.gallery_images.length + files.length > 5) {
            toast.error('Maximum 5 gallery images allowed.', 'Error');
            target.value = '';
            return;
        }
        
        // Validate each file
        for (const file of files) {
            if (file.size > 2 * 1024 * 1024) {
                toast.error('Each image must be less than 2MB.', 'Error');
                target.value = '';
                return;
            }
            
            if (!['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(file.type)) {
                toast.error('Please select valid image files (JPEG, PNG, JPG, GIF).', 'Error');
                target.value = '';
                return;
            }
        }
        
        // Add files to form data
        form.gallery_images.push(...files);
        
        // Create previews
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                galleryPreviews.value.push(e.target?.result as string);
            };
            reader.readAsDataURL(file);
        });
    }
};

// Remove image
const removeImage = () => {
    form.image = null;
    imagePreview.value = null;
    
    // Reset file input
    const fileInput = document.getElementById('image') as HTMLInputElement;
    if (fileInput) {
        fileInput.value = '';
    }
};

// Remove gallery image
const removeGalleryImage = (index: number) => {
    form.gallery_images.splice(index, 1);
    galleryPreviews.value.splice(index, 1);
};

// Variation management
const addVariation = () => {
    const newVariation: ProductVariation = {
        display_name: '',
        attribute_values: '',
        price: 0,
        sale_price: '',
        stock_quantity: 0,
        sku: '',
        is_active: true,
    };
    variations.value.push(newVariation);
    form.variations = variations.value;
};

const removeVariation = (index: number) => {
    if (variations.value.length > 1) {
        variations.value.splice(index, 1);
        form.variations = variations.value;
    }
};

// Auto-generate variation SKU
const generateVariationSku = (index: number) => {
    const variation: ProductVariation = variations.value[index];
    if (variation.display_name && form.name) {
        const baseSkus = form.name
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        const variationSku = variation.display_name
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        variation.sku = `${baseSkus}-${variationSku}`;
        form.variations = variations.value;
    }
};

// Helper function to convert attribute string to object
const parseAttributeValues = (attributeString: string): Record<string, string> => {
    const result: Record<string, string> = {};
    if (!attributeString.trim()) return result;
    
    const pairs = attributeString.split(',');
    pairs.forEach(pair => {
        const [key, value] = pair.split(':').map(s => s.trim());
        if (key && value) {
            result[key.toLowerCase()] = value;
        }
    });
    return result;
};

// Helper function to convert attribute object to string
const stringifyAttributeValues = (attributeObj: Record<string, string>): string => {
    return Object.entries(attributeObj)
        .map(([key, value]) => `${key}: ${value}`)
        .join(', ');
};

// Submit form
const submit = () => {
    // Log what we're about to submit for debugging
    const debugData = {
        product_type: productType.value,
        category_id: form.category_id,
        name: form.name,
        ...(productType.value === 'simple' ? {
            price: form.price,
            sale_price: form.sale_price,
            stock_quantity: form.stock_quantity,
        } : {
            variations_count: variations.value.length,
            variations: variations.value,
        })
    };
    console.log('Submitting product data:', debugData);
    
    // Use Inertia form but create clean data structure
    const submitForm = useForm({
        category_id: form.category_id,
        sub_category_id: form.sub_category_id,
        name: form.name,
        description: form.description,
        image: form.image,
        gallery_images: form.gallery_images,
        is_active: form.is_active,
        is_featured: form.is_featured,
        product_type: productType.value,
        // Conditionally add fields based on product type
        ...(productType.value === 'simple' ? {
            price: form.price,
            sale_price: form.sale_price,
            stock_quantity: form.stock_quantity,
        } : {
            variations: variations.value.map((variation: ProductVariation) => ({
                ...variation,
                attribute_values: parseAttributeValues(variation.attribute_values)
            }))
        })
    });
    
    if (props.isEdit && props.product) {
        submitForm.put(`/admin/products/${props.product.id}`, {
            onSuccess: () => {
                // Toast will be handled by global flash message handler
            },
            onError: (errors) => {
                console.error('Update errors:', errors);
                // Copy errors to main form for display
                Object.assign(form.errors, submitForm.errors);
            },
        });
    } else {
        submitForm.post('/admin/products', {
            onSuccess: () => {
                // Toast will be handled by global flash message handler
            },
            onError: (errors) => {
                console.error('Create errors:', errors);
                // Copy errors to main form for display
                Object.assign(form.errors, submitForm.errors);
            },
        });
    }
};
</script>

<template>
    <Head :title="props.isEdit ? 'Edit Product' : 'Create Product'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ props.isEdit ? 'Edit Product' : 'Create Product' }}</h1>
                    <p class="text-gray-600 dark:text-gray-400">{{ props.isEdit ? 'Update product information' : 'Add a new product to your inventory' }}</p>
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
                    <!-- Product Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                            Product Type *
                        </label>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="relative">
                                <input
                                    id="simple"
                                    v-model="productType"
                                    type="radio"
                                    value="simple"
                                    class="peer sr-only"
                                />
                                <label
                                    for="simple"
                                    class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-gray-200 bg-white p-4 text-gray-700 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:peer-checked:border-blue-400 dark:peer-checked:bg-blue-900/20 dark:peer-checked:text-blue-400"
                                >
                                    <div class="text-center">
                                        <div class="text-lg font-semibold">Simple Product</div>
                                        <div class="mt-1 text-sm">Single product without variations</div>
                                    </div>
                                </label>
                            </div>
                            <div class="relative">
                                <input
                                    id="variation"
                                    v-model="productType"
                                    type="radio"
                                    value="variation"
                                    class="peer sr-only"
                                />
                                <label
                                    for="variation"
                                    class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-gray-200 bg-white p-4 text-gray-700 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:peer-checked:border-blue-400 dark:peer-checked:bg-blue-900/20 dark:peer-checked:text-blue-400"
                                >
                                    <div class="text-center">
                                        <div class="text-lg font-semibold">Variable Product</div>
                                        <div class="mt-1 text-sm">Product with variations (size, color, etc.)</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

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
                                value="Auto-generated"
                                class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-400"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">SKU will be generated automatically</p>
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

                    <!-- Simple Product Pricing (only for simple products) -->
                    <div v-if="productType === 'simple'" class="grid gap-6 md:grid-cols-3">
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

                    <!-- Variations Section (only for variable products) -->
                    <div v-if="productType === 'variation'" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Product Variations</h3>
                            <button
                                type="button"
                                @click="addVariation"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
                            >
                                <Plus class="h-4 w-4" />
                                Add Variation
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div 
                                v-for="(variation, index) in variations" 
                                :key="index"
                                class="border border-gray-200 rounded-lg p-4 dark:border-gray-600"
                            >
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-md font-medium text-gray-900 dark:text-white">
                                        Variation {{ index + 1 }}
                                    </h4>
                                    <button
                                        v-if="variations.length > 1"
                                        type="button"
                                        @click="removeVariation(index)"
                                        class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400"
                                    >
                                        <Trash2 class="h-3 w-3" />
                                        Remove
                                    </button>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                    <!-- Display Name -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Display Name *
                                        </label>
                                        <input
                                            v-model="variation.display_name"
                                            type="text"
                                            required
                                            @input="generateVariationSku(index)"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            placeholder="e.g., Red - Large"
                                        />
                                    </div>

                                    <!-- SKU -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            SKU
                                        </label>
                                        <div class="mt-1 flex gap-2">
                                            <input
                                                v-model="variation.sku"
                                                type="text"
                                                class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                placeholder="Auto-generated"
                                            />
                                            <button
                                                type="button"
                                                @click="generateVariationSku(index)"
                                                class="rounded-lg border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                            >
                                                Gen
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Price *
                                        </label>
                                        <input
                                            v-model.number="variation.price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            required
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        />
                                    </div>

                                    <!-- Sale Price -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Sale Price
                                        </label>
                                        <input
                                            v-model.number="variation.sale_price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        />
                                    </div>

                                    <!-- Stock -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Stock *
                                        </label>
                                        <input
                                            v-model.number="variation.stock_quantity"
                                            type="number"
                                            min="0"
                                            required
                                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        />
                                    </div>

                                    <!-- Active Status -->
                                    <div class="flex items-center pt-6">
                                        <input
                                            v-model="variation.is_active"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                        <label class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                            Active
                                        </label>
                                    </div>
                                </div>

                                <!-- Attributes -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Attribute Values *
                                    </label>
                                    <textarea
                                        v-model="variation.attribute_values"
                                        rows="2"
                                        required
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="e.g., Color: Red, Size: Large"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Main Product Image
                        </label>
                        <div class="mt-1">
                            <!-- Image Preview -->
                            <div v-if="imagePreview" class="mb-4">
                                <div class="relative inline-block">
                                    <img
                                        :src="imagePreview"
                                        alt="Preview"
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
                                    Choose Main Image
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

                    <!-- Gallery Images Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Gallery Images
                        </label>
                        <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">Upload up to 5 additional images for the product gallery</p>
                        <div class="mt-1">
                            <!-- Gallery Previews -->
                            <div v-if="galleryPreviews.length > 0" class="mb-4">
                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5">
                                    <div 
                                        v-for="(preview, index) in galleryPreviews" 
                                        :key="index"
                                        class="relative"
                                    >
                                        <img
                                            :src="preview"
                                            alt="Gallery Preview"
                                            class="h-20 w-20 rounded-lg object-cover"
                                        />
                                        <button
                                            type="button"
                                            @click="removeGalleryImage(index)"
                                            class="absolute -right-1 -top-1 rounded-full bg-red-500 p-1 text-white hover:bg-red-600"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- File Input -->
                            <div class="flex items-center gap-4">
                                <label
                                    for="gallery_images"
                                    class="cursor-pointer inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <Upload class="h-4 w-4" />
                                    Add Gallery Images
                                </label>
                                <input
                                    id="gallery_images"
                                    type="file"
                                    accept="image/*"
                                    multiple
                                    class="hidden"
                                    @change="handleGalleryImagesChange"
                                />
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    Select multiple images ({{ form.gallery_images.length }}/5)
                                </span>
                            </div>
                            <div v-if="form.errors.gallery_images" class="mt-1 text-sm text-red-600">{{ form.errors.gallery_images }}</div>
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
                            {{ form.processing 
                                ? (props.isEdit ? 'Updating...' : 'Creating...') 
                                : (props.isEdit ? 'Update Product' : 'Create Product') 
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
