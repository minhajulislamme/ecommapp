<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';

interface Product {
    id: number;
    name: string;
    sku: string;
}

interface Props {
    product: Product;
}

const props = defineProps<Props>();
const { toast } = useToast();

// Variation form data
const variations = ref([
    {
        display_name: '',
        attribute_values: '',
        price: 0,
        sale_price: '',
        stock_quantity: 0,
        sku: '',
        is_active: true,
    }
]);

const form = useForm({
    variations: variations.value,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Product Management', href: '/admin/products' },
    { title: props.product.name, href: `/admin/products/${props.product.id}/edit` },
    { title: 'Variations', href: `/admin/products/${props.product.id}/variations` },
    { title: 'Bulk Create', href: `/admin/products/${props.product.id}/variations/bulk-create` },
];

// Add variation
const addVariation = () => {
    variations.value.push({
        display_name: '',
        attribute_values: '',
        price: 0,
        sale_price: '',
        stock_quantity: 0,
        sku: '',
        is_active: true,
    });
    form.variations = variations.value;
};

// Remove variation
const removeVariation = (index: number) => {
    if (variations.value.length > 1) {
        variations.value.splice(index, 1);
        form.variations = variations.value;
    }
};

// Auto-generate variation SKU
const generateVariationSku = (index: number) => {
    const variation = variations.value[index];
    if (variation.display_name && props.product.sku) {
        const variationSku = variation.display_name
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        variation.sku = `${props.product.sku}-${variationSku}`;
        form.variations = variations.value;
    }
};

// Submit form
const submit = () => {
    form.post(`/admin/products/${props.product.id}/variations/bulk`, {
        onSuccess: () => {
            // Toast will be handled by global flash message handler
        },
        onError: () => {
            toast.error('Failed to create variations. Please check the form and try again.', 'Error');
        },
    });
};

// Load common variation patterns
const loadCommonPatterns = (pattern: string) => {
    variations.value = [];
    
    switch (pattern) {
        case 'sizes':
            const sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
            sizes.forEach((size, index) => {
                variations.value.push({
                    display_name: `Size ${size}`,
                    attribute_values: `Size: ${size}`,
                    price: 0,
                    sale_price: '',
                    stock_quantity: 0,
                    sku: `${props.product.sku}-${size.toLowerCase()}`,
                    is_active: true,
                });
            });
            break;
            
        case 'colors':
            const colors = ['Red', 'Blue', 'Black', 'White', 'Green'];
            colors.forEach((color, index) => {
                variations.value.push({
                    display_name: `${color}`,
                    attribute_values: `Color: ${color}`,
                    price: 0,
                    sale_price: '',
                    stock_quantity: 0,
                    sku: `${props.product.sku}-${color.toLowerCase()}`,
                    is_active: true,
                });
            });
            break;
            
        case 'memory':
            const memory = ['16GB', '32GB', '64GB', '128GB', '256GB'];
            memory.forEach((mem, index) => {
                variations.value.push({
                    display_name: `${mem}`,
                    attribute_values: `Storage: ${mem}`,
                    price: 0,
                    sale_price: '',
                    stock_quantity: 0,
                    sku: `${props.product.sku}-${mem.toLowerCase()}`,
                    is_active: true,
                });
            });
            break;
    }
    
    if (variations.value.length === 0) {
        variations.value.push({
            display_name: '',
            attribute_values: '',
            price: 0,
            sale_price: '',
            stock_quantity: 0,
            sku: '',
            is_active: true,
        });
    }
    
    form.variations = variations.value;
};
</script>

<template>
    <Head title="Bulk Create Variations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Bulk Create Variations</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Create multiple variations for: <span class="font-medium">{{ product.name }}</span>
                    </p>
                </div>
                <Link
                    :href="`/admin/products/${product.id}/variations`"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Back to Variations
                </Link>
            </div>

            <!-- Quick Templates -->
            <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-white">Quick Templates</h3>
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="loadCommonPatterns('sizes')"
                        class="rounded-lg bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400"
                    >
                        Clothing Sizes
                    </button>
                    <button
                        @click="loadCommonPatterns('colors')"
                        class="rounded-lg bg-green-100 px-3 py-1.5 text-sm font-medium text-green-700 hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400"
                    >
                        Colors
                    </button>
                    <button
                        @click="loadCommonPatterns('memory')"
                        class="rounded-lg bg-purple-100 px-3 py-1.5 text-sm font-medium text-purple-700 hover:bg-purple-200 dark:bg-purple-900/20 dark:text-purple-400"
                    >
                        Storage/Memory
                    </button>
                    <button
                        @click="addVariation"
                        class="rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-900/20 dark:text-gray-400"
                    >
                        Add Custom
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Click a template to quickly generate common variation patterns, or add custom variations manually.
                </p>
            </div>

            <!-- Form -->
            <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Variations Header -->
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                            Product Variations ({{ variations.length }})
                        </h3>
                        <button
                            type="button"
                            @click="addVariation"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
                        >
                            <Plus class="h-4 w-4" />
                            Add Variation
                        </button>
                    </div>

                    <!-- Variations List -->
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
                                        placeholder="0.00"
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
                                        placeholder="0.00"
                                    />
                                </div>

                                <!-- Stock -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Stock Quantity *
                                    </label>
                                    <input
                                        v-model.number="variation.stock_quantity"
                                        type="number"
                                        min="0"
                                        required
                                        class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        placeholder="0"
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

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <Link
                            :href="`/admin/products/${product.id}/variations`"
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
                            {{ form.processing ? 'Creating...' : `Create ${variations.length} Variations` }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
