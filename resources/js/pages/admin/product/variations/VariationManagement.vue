<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { 
    ArrowLeft, 
    Plus, 
    Edit2, 
    Trash2, 
    Eye, 
    EyeOff, 
    Package, 
    DollarSign,
    Hash,
    AlertTriangle
} from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

interface Product {
    id: number;
    name: string;
    sku: string;
}

interface Variation {
    id: number;
    product_id: number;
    sku: string;
    price: number;
    sale_price: number | null;
    effective_price: number;
    stock_quantity: number;
    attribute_values: string;
    display_name: string;
    is_active: boolean;
}

interface Props {
    product: Product;
    variations: Variation[];
}

const props = defineProps<Props>();
const { toast } = useToast();

const showDeleteDialog = ref(false);
const variationToDelete = ref<Variation | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Product Management', href: '/admin/products' },
    { title: props.product.name, href: `/admin/products/${props.product.id}/edit` },
    { title: 'Variations', href: `/admin/products/${props.product.id}/variations` },
];

// Format currency
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

// Handle variation deletion
const deleteVariation = (variation: Variation) => {
    variationToDelete.value = variation;
    showDeleteDialog.value = true;
};

// Confirm variation deletion
const confirmDelete = () => {
    if (!variationToDelete.value) return;
    
    router.delete(`/admin/products/${props.product.id}/variations/${variationToDelete.value.id}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            variationToDelete.value = null;
            toast.success('Variation deleted successfully!');
        },
        onError: (errors) => {
            console.error('Delete errors:', errors);
            showDeleteDialog.value = false;
            variationToDelete.value = null;
            toast.error('Failed to delete variation');
        },
    });
};

// Cancel variation deletion
const cancelDelete = () => {
    showDeleteDialog.value = false;
    variationToDelete.value = null;
};

// Toggle variation status
const toggleStatus = (variation: Variation) => {
    router.patch(`/admin/products/${props.product.id}/variations/${variation.id}/toggle-status`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            const status = variation.is_active ? 'deactivated' : 'activated';
            toast.success(`Variation ${status} successfully!`);
        },
        onError: () => {
            toast.error('Failed to toggle variation status');
        },
    });
};

// Get total stock
const totalStock = props.variations.reduce((sum, variation) => sum + variation.stock_quantity, 0);

// Get price range
const priceRange = () => {
    if (props.variations.length === 0) return null;
    
    const prices = props.variations.map(v => v.effective_price);
    const minPrice = Math.min(...prices);
    const maxPrice = Math.max(...prices);
    
    if (minPrice === maxPrice) {
        return formatPrice(minPrice);
    }
    return `${formatPrice(minPrice)} - ${formatPrice(maxPrice)}`;
};
</script>

<template>
    <Head :title="`${product.name} - Variations`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Product Variations</h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Manage variations for: <span class="font-medium">{{ product.name }}</span>
                    </p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="'/admin/products/' + product.id + '/edit'"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back to Product
                    </Link>
                    <Link
                        :href="'/admin/products/' + product.id + '/variations/bulk-create'"
                        class="inline-flex items-center gap-2 rounded-lg border border-blue-600 bg-white px-4 py-2 text-sm font-medium text-blue-600 shadow-sm hover:bg-blue-50 dark:border-blue-400 dark:bg-gray-800 dark:text-blue-400 dark:hover:bg-blue-900/20"
                    >
                        <Plus class="h-4 w-4" />
                        Bulk Create
                    </Link>
                    <Link
                        :href="'/admin/products/' + product.id + '/variations/create'"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                    >
                        <Plus class="h-4 w-4" />
                        Add Variation
                    </Link>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/20">
                            <Package class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Variations</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ variations.length }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/20">
                            <Hash class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total Stock</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ totalStock }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/20">
                            <DollarSign class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Price Range</p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ priceRange() || 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/20">
                            <Eye class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Active Variations</p>
                            <p class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ variations.filter(v => v.is_active).length }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Variations Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                <div v-if="variations.length === 0" class="p-8 text-center">
                    <Package class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No variations found</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        This product doesn't have any variations yet.
                    </p>
                    <Link
                        :href="`/admin/products/${product.id}/variations/create`"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                    >
                        <Plus class="h-4 w-4" />
                        Add First Variation
                    </Link>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Variation
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    SKU
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Price
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Stock
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-for="variation in variations" :key="variation.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <!-- Variation Info -->
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ variation.display_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ variation.attribute_values }}
                                        </div>
                                    </div>
                                </td>

                                <!-- SKU -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ variation.sku || 'N/A' }}
                                    </div>
                                </td>

                                <!-- Price -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatPrice(variation.effective_price) }}
                                    </div>
                                    <div v-if="variation.sale_price" class="text-sm text-gray-500 line-through dark:text-gray-400">
                                        {{ formatPrice(variation.price) }}
                                    </div>
                                </td>

                                <!-- Stock -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ variation.stock_quantity }}
                                    </div>
                                    <div v-if="variation.stock_quantity <= 5" class="text-xs text-red-600 dark:text-red-400">
                                        Low stock
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleStatus(variation)"
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                                        :class="variation.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'"
                                    >
                                        <component :is="variation.is_active ? Eye : EyeOff" class="h-3 w-3" />
                                        {{ variation.is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="`/admin/products/${product.id}/variations/${variation.id}/edit`"
                                            class="inline-flex items-center gap-1 rounded-lg bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                        >
                                            <Edit2 class="h-3 w-3" />
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteVariation(variation)"
                                            class="inline-flex items-center gap-1 rounded-lg bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                                        >
                                            <Trash2 class="h-3 w-3" />
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-red-600">
                        <AlertTriangle class="h-5 w-5" />
                        Confirm Delete
                    </DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete the variation "<strong>{{ variationToDelete?.display_name }}</strong>"? 
                        <br><br>
                        <span class="text-red-600 font-medium">
                            This action cannot be undone and will permanently remove this variation.
                        </span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="cancelDelete">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Delete Variation
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
