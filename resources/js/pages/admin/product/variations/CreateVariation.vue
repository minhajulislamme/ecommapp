<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, Save, Plus } from 'lucide-vue-next';
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

const form = useForm({
    display_name: '',
    attribute_values: '',
    price: 0,
    sale_price: '',
    stock_quantity: 0,
    sku: '',
    is_active: true,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Product Management', href: '/admin/products' },
    { title: props.product.name, href: `/admin/products/${props.product.id}/edit` },
    { title: 'Variations', href: `/admin/products/${props.product.id}/variations` },
    { title: 'Create Variation', href: `/admin/products/${props.product.id}/variations/create` },
];

// Submit form
const submit = () => {
    form.post(`/admin/products/${props.product.id}/variations`, {
        onSuccess: () => {
            // Toast will be handled by global flash message handler
        },
        onError: () => {
            toast.error('Failed to create variation. Please check the form and try again.', 'Error');
        },
    });
};

// Auto-generate SKU
const generateSku = () => {
    if (form.display_name && props.product.sku) {
        const baseSkus = form.display_name
            .toLowerCase()
            .replace(/[^a-z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        form.sku = `${props.product.sku}-${baseSkus}`;
    }
};
</script>

<template>
    <Head title="Create Variation" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Variation</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Add a new variation for: <span class="font-medium">{{ product.name }}</span>
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

            <!-- Form -->
            <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Display Name -->
                        <div>
                            <label for="display_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Display Name *
                            </label>
                            <input
                                id="display_name"
                                v-model="form.display_name"
                                type="text"
                                required
                                @input="generateSku"
                                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                placeholder="e.g., Red - Large"
                            />
                            <div v-if="form.errors.display_name" class="mt-1 text-sm text-red-600">{{ form.errors.display_name }}</div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                A user-friendly name for this variation
                            </p>
                        </div>

                        <!-- SKU -->
                        <div>
                            <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                SKU
                            </label>
                            <div class="mt-1 flex gap-2">
                                <input
                                    id="sku"
                                    v-model="form.sku"
                                    type="text"
                                    class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    placeholder="Leave empty for auto-generation"
                                />
                                <button
                                    type="button"
                                    @click="generateSku"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    Generate
                                </button>
                            </div>
                            <div v-if="form.errors.sku" class="mt-1 text-sm text-red-600">{{ form.errors.sku }}</div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Unique identifier for inventory tracking
                            </p>
                        </div>
                    </div>

                    <!-- Attributes -->
                    <div>
                        <label for="attribute_values" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Attribute Values *
                        </label>
                        <textarea
                            id="attribute_values"
                            v-model="form.attribute_values"
                            rows="3"
                            required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                            placeholder="e.g., Color: Red, Size: Large, Material: Cotton"
                        />
                        <div v-if="form.errors.attribute_values" class="mt-1 text-sm text-red-600">{{ form.errors.attribute_values }}</div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Describe the specific attributes that make this variation unique (e.g., Color: Red, Size: XL)
                        </p>
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

                    <!-- Status -->
                    <div>
                        <div class="flex items-center">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                            />
                            <label for="is_active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Active (variation will be available to customers)
                            </label>
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
                            {{ form.processing ? 'Creating...' : 'Create Variation' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
