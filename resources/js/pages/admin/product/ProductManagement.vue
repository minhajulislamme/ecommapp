<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Search, Plus, Edit, Trash2, ToggleLeft, ToggleRight, Star, Package, AlertTriangle, Settings } from 'lucide-vue-next';
import StatusBadge from '@/components/StatusBadge.vue';
import { useToast } from '@/composables/useToast';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    sale_price: number;
    sku: string;
    stock_quantity: number;
    image: string;
    images: string[] | null;
    is_active: boolean;
    is_featured: boolean;
    created_at: string;
    has_variations: boolean;
    variations_count: number;
    total_stock: number;
    price_range: string | null;
    category: {
        id: number;
        name: string;
    };
    sub_category: {
        id: number;
        name: string;
    } | null;
}

interface Category {
    id: number;
    name: string;
}

interface Props {
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    categories: Category[];
    filters: {
        search?: string;
        category_id?: string;
        status?: string;
    };
}

const props = defineProps<Props>();
const page = usePage();
const { toast } = useToast();

const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category_id || '');
const selectedStatus = ref(props.filters.status || '');
const showDeleteDialog = ref(false);
const productToDelete = ref<Product | null>(null);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Product Management', href: '/admin/products' },
];

// Computed filtered products
const filteredProducts = computed(() => {
    let filtered = [...props.products.data];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(product =>
            product.name.toLowerCase().includes(query) ||
            product.sku.toLowerCase().includes(query) ||
            product.category.name.toLowerCase().includes(query) ||
            (product.sub_category && product.sub_category.name.toLowerCase().includes(query))
        );
    }

    // Category filter
    if (selectedCategory.value) {
        filtered = filtered.filter(product => product.category.id === parseInt(selectedCategory.value));
    }

    // Status filter
    if (selectedStatus.value) {
        if (selectedStatus.value === 'active') {
            filtered = filtered.filter(product => product.is_active);
        } else if (selectedStatus.value === 'inactive') {
            filtered = filtered.filter(product => !product.is_active);
        } else if (selectedStatus.value === 'featured') {
            filtered = filtered.filter(product => product.is_featured);
        }
    }

    return filtered;
});

// Clear all filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedStatus.value = '';
};

// Handle product deletion
const deleteProduct = (product: Product) => {
    productToDelete.value = product;
    showDeleteDialog.value = true;
};

// Confirm product deletion
const confirmDelete = () => {
    if (!productToDelete.value) return;
    
    router.delete(`/admin/products/${productToDelete.value.id}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            showDeleteDialog.value = false;
            productToDelete.value = null;
        },
        onError: (errors) => {
            console.error('Delete errors:', errors);
            showDeleteDialog.value = false;
            productToDelete.value = null;
        },
    });
};

// Cancel product deletion
const cancelDelete = () => {
    showDeleteDialog.value = false;
    productToDelete.value = null;
};

// Toggle product status
const toggleStatus = (product: Product) => {
    router.patch(`/admin/products/${product.id}/toggle-status`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Toast will be handled by flash message watcher
        },
        onError: (errors) => {
            console.error('Toggle status errors:', errors);
            toast.error('Failed to update product status. Please try again.', 'Error');
        },
    });
};

// Toggle featured status
const toggleFeatured = (product: Product) => {
    router.patch(`/admin/products/${product.id}/toggle-featured`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Toast will be handled by flash message watcher
        },
        onError: (errors) => {
            console.error('Toggle featured errors:', errors);
            toast.error('Failed to update featured status. Please try again.', 'Error');
        },
    });
};

// Format price
const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

// Get image URL
const getImageUrl = (image: string | null) => {
    if (!image) return '/images/placeholder.png';
    return `/upload/products/${image}`;
};
</script>

<template>
    <Head title="Product Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Product Management</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage your products inventory</p>
                </div>
                <Link
                    href="/admin/products/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                    <Plus class="h-4 w-4" />
                    Add New Product
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-4 rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                <!-- Search -->
                <div class="relative flex-1 min-w-64">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search products..."
                        class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                    />
                </div>

                <!-- Category Filter -->
                <select
                    v-model="selectedCategory"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                        {{ category.name }}
                    </option>
                </select>

                <!-- Status Filter -->
                <select
                    v-model="selectedStatus"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="featured">Featured</option>
                </select>

                <!-- Clear Filters -->
                <button
                    @click="clearFilters"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700"
                >
                    Clear
                </button>
            </div>

            <!-- Products Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Product
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Category
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
                            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <!-- Product Info -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-16 w-16 flex-shrink-0">
                                            <img
                                                :src="getImageUrl(product.image)"
                                                :alt="product.name"
                                                class="h-16 w-16 rounded-lg object-cover"
                                            />
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ product.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                SKU: {{ product.sku }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ product.category.name }}</div>
                                    <div v-if="product.sub_category" class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ product.sub_category.name }}
                                    </div>
                                </td>

                                <!-- Price -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ product.has_variations && product.price_range 
                                            ? product.price_range 
                                            : formatPrice(product.sale_price || product.price) 
                                        }}
                                    </div>
                                    <div v-if="product.sale_price && !product.has_variations" class="text-sm text-gray-500 line-through dark:text-gray-400">
                                        {{ formatPrice(product.price) }}
                                    </div>
                                    <div v-if="product.has_variations" class="text-xs text-gray-500 dark:text-gray-400">
                                        Variable pricing
                                    </div>
                                </td>

                                <!-- Stock -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        {{ product.has_variations ? product.total_stock || 0 : product.stock_quantity }}
                                    </div>
                                    <div v-if="product.has_variations" class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ product.variations_count }} variations
                                    </div>
                                    <div v-else class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ product.stock_quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <StatusBadge :is-active="product.is_active" />
                                        <div v-if="product.is_featured" class="flex items-center gap-1 text-xs text-yellow-600">
                                            <Star class="h-3 w-3" />
                                            Featured
                                        </div>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <!-- Toggle Status -->
                                        <button
                                            @click="toggleStatus(product)"
                                            :class="[
                                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium transition-colors',
                                                product.is_active
                                                    ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400'
                                                    : 'bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/20 dark:text-green-400'
                                            ]"
                                            :title="product.is_active ? 'Deactivate' : 'Activate'"
                                        >
                                            <ToggleRight v-if="product.is_active" class="h-3 w-3" />
                                            <ToggleLeft v-else class="h-3 w-3" />
                                        </button>

                                        <!-- Toggle Featured -->
                                        <button
                                            @click="toggleFeatured(product)"
                                            :class="[
                                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium transition-colors',
                                                product.is_featured
                                                    ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-400'
                                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-900/20 dark:text-gray-400'
                                            ]"
                                            :title="product.is_featured ? 'Unfeature' : 'Feature'"
                                        >
                                            <Star class="h-3 w-3" />
                                        </button>

                                        <!-- Manage Variations (only for products with variations) -->
                                        <Link
                                            v-if="product.has_variations"
                                            :href="`/admin/products/${product.id}/variations`"
                                            class="inline-flex items-center rounded-md bg-purple-100 px-2 py-1 text-xs font-medium text-purple-700 hover:bg-purple-200 dark:bg-purple-900/20 dark:text-purple-400"
                                            title="Manage Variations"
                                        >
                                            <Settings class="h-3 w-3" />
                                        </Link>

                                        <!-- Edit -->
                                        <Link
                                            :href="`/admin/products/${product.id}/edit`"
                                            class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400"
                                        >
                                            <Edit class="h-3 w-3" />
                                        </Link>

                                        <!-- Delete -->
                                        <button
                                            @click="deleteProduct(product)"
                                            class="inline-flex items-center rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-700 hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400"
                                        >
                                            <Trash2 class="h-3 w-3" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="filteredProducts.length === 0" class="py-12 text-center">
                        <Package class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No products found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ searchQuery || selectedCategory || selectedStatus ? 'Try adjusting your filters.' : 'Get started by creating a new product.' }}
                        </p>
                        <div v-if="!searchQuery && !selectedCategory && !selectedStatus" class="mt-6">
                            <Link
                                href="/admin/products/create"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700"
                            >
                                <Plus class="-ml-1 mr-2 h-5 w-5" />
                                Add New Product
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Summary -->
            <div v-if="filteredProducts.length > 0" class="text-sm text-gray-500 dark:text-gray-400">
                Showing {{ filteredProducts.length }} of {{ props.products.total }} products
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
                        Are you sure you want to delete "<strong>{{ productToDelete?.name }}</strong>"? 
                        <br><br>
                        <span class="text-red-600 font-medium">
                            This action cannot be undone and will permanently remove this product from your inventory.
                        </span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex gap-2 sm:gap-0">
                    <Button variant="outline" @click="cancelDelete">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Delete Product
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
