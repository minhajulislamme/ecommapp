<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search, Edit, Trash2, ToggleLeft, ToggleRight, Tag, AlertTriangle } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface Category {
    id: number;
    name: string;
}

interface SubCategory {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    is_active: boolean;
    created_at: string;
    category: Category;
}

interface Props {
    subCategories: {
        data: SubCategory[];
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

const searchQuery = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category_id || '');
const statusFilter = ref(props.filters.status || '');

// Delete confirmation dialog state
const showDeleteDialog = ref(false);
const subCategoryToDelete = ref<SubCategory | null>(null);

// Get all sub-categories data
const allSubCategories = computed(() => props.subCategories.data);

// Client-side filtering without page reload
const filteredSubCategories = computed(() => {
    let filtered = allSubCategories.value;

    // Apply search filter
    if (searchQuery.value.trim()) {
        const searchTerm = searchQuery.value.toLowerCase().trim();
        filtered = filtered.filter(subCategory => 
            subCategory.name.toLowerCase().includes(searchTerm) ||
            subCategory.slug.toLowerCase().includes(searchTerm) ||
            subCategory.category.name.toLowerCase().includes(searchTerm) ||
            (subCategory.description && subCategory.description.toLowerCase().includes(searchTerm))
        );
    }

    // Apply category filter
    if (categoryFilter.value) {
        filtered = filtered.filter(subCategory => 
            subCategory.category.id.toString() === categoryFilter.value
        );
    }

    // Apply status filter
    if (statusFilter.value) {
        filtered = filtered.filter(subCategory => {
            if (statusFilter.value === 'active') {
                return subCategory.is_active;
            } else if (statusFilter.value === 'inactive') {
                return !subCategory.is_active;
            }
            return true;
        });
    }

    return filtered;
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Sub-Category Management', href: '/admin/subcategories' },
];

const clearFilters = () => {
    searchQuery.value = '';
    categoryFilter.value = '';
    statusFilter.value = '';
};

const toggleStatus = (subCategory: SubCategory) => {
    router.patch(`/admin/subcategories/${subCategory.id}/toggle-status`, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteSubCategory = (subCategory: SubCategory) => {
    subCategoryToDelete.value = subCategory;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (subCategoryToDelete.value) {
        router.delete(`/admin/subcategories/${subCategoryToDelete.value.id}`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                showDeleteDialog.value = false;
                subCategoryToDelete.value = null;
            },
            onError: () => {
                showDeleteDialog.value = false;
                subCategoryToDelete.value = null;
            }
        });
    }
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    subCategoryToDelete.value = null;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Sub-Category Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sub-Category Management</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage product sub-categories</p>
                </div>
                <Link :href="'/admin/subcategories/create'">
                    <Button class="flex items-center gap-2">
                        <Plus class="h-4 w-4" />
                        Add Sub-Category
                    </Button>
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-1 items-center gap-2">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search sub-categories..."
                            class="pl-9"
                        />
                    </div>
                    <select 
                        v-model="categoryFilter"
                        class="flex h-9 w-48 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">All Categories</option>
                        <option v-for="category in props.categories" :key="category.id" :value="category.id.toString()">
                            {{ category.name }}
                        </option>
                    </select>
                    <select 
                        v-model="statusFilter"
                        class="flex h-9 w-32 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <Button @click="clearFilters" variant="ghost" size="sm">
                        Clear
                    </Button>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ filteredSubCategories.length }} of {{ allSubCategories.length }} sub-categories
                </div>
            </div>

            <!-- Sub-Categories Table -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800 overflow-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Sub-Category</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Parent Category</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Description</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Created</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-if="filteredSubCategories.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Tag class="h-12 w-12 text-gray-400" />
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">No sub-categories found</h3>
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery || categoryFilter || statusFilter ? 'Try adjusting your search or filter criteria' : 'Get started by creating your first sub-category' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="subCategory in filteredSubCategories" :key="subCategory.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                <!-- Sub-Category -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div v-if="subCategory.image" class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0">
                                            <img :src="`/upload/subcategories/${subCategory.image}`" :alt="subCategory.name" class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                            <Tag class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ subCategory.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ subCategory.slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Parent Category -->
                                <td class="px-6 py-4">
                                    <Link :href="`/admin/categories/${subCategory.category.id}`" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 text-sm font-medium">
                                        {{ subCategory.category.name }}
                                    </Link>
                                </td>
                                
                                <!-- Description -->
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                        {{ subCategory.description || 'No description' }}
                                    </p>
                                </td>
                                
                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          :class="subCategory.is_active 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' 
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
                                        {{ subCategory.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                
                                <!-- Created -->
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(subCategory.created_at) }}
                                    </span>
                                </td>
                                
                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Button variant="ghost" size="sm" asChild>
                                            <Link :href="`/admin/subcategories/${subCategory.id}/edit`">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="toggleStatus(subCategory)">
                                            <component :is="subCategory.is_active ? ToggleLeft : ToggleRight" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="deleteSubCategory(subCategory)" class="text-red-600 hover:text-red-700">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="lg:hidden">
                    <div v-if="filteredSubCategories.length === 0" class="p-6">
                        <div class="flex flex-col items-center gap-2 py-8">
                            <Tag class="h-12 w-12 text-gray-400" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No sub-categories found</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-center">
                                {{ searchQuery || categoryFilter || statusFilter ? 'Try adjusting your search or filter criteria' : 'Get started by creating your first sub-category' }}
                            </p>
                        </div>
                    </div>
                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="subCategory in filteredSubCategories" :key="subCategory.id" class="p-6">
                            <div class="flex items-start gap-4">
                                <div v-if="subCategory.image" class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0">
                                    <img :src="`/upload/subcategories/${subCategory.image}`" :alt="subCategory.name" class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                    <Tag class="h-6 w-6 text-gray-400" />
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ subCategory.name }}</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ subCategory.slug }}</p>
                                            <Link :href="`/admin/categories/${subCategory.category.id}`" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 mt-1">
                                                {{ subCategory.category.name }}
                                            </Link>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ml-2"
                                              :class="subCategory.is_active 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' 
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
                                            {{ subCategory.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 line-clamp-2">
                                        {{ subCategory.description || 'No description available' }}
                                    </p>
                                    
                                    <div class="flex items-center justify-between mt-3">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(subCategory.created_at) }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 mt-4">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link :href="`/admin/subcategories/${subCategory.id}/edit`" class="flex items-center gap-2">
                                                <Edit class="h-4 w-4" />
                                                Edit
                                            </Link>
                                        </Button>
                                        <Button variant="outline" size="sm" @click="toggleStatus(subCategory)" class="flex items-center gap-2">
                                            <component :is="subCategory.is_active ? ToggleLeft : ToggleRight" class="h-4 w-4" />
                                            {{ subCategory.is_active ? 'Deactivate' : 'Activate' }}
                                        </Button>
                                        <Button variant="outline" size="sm" @click="deleteSubCategory(subCategory)" class="flex items-center gap-2 text-red-600">
                                            <Trash2 class="h-4 w-4" />
                                            Delete
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
                        Are you sure you want to delete "<strong>{{ subCategoryToDelete?.name }}</strong>"? 
                        <br><br>
                        <span class="text-red-600 font-medium">This action cannot be undone.</span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex gap-2 sm:gap-0">
                    <Button variant="outline" @click="cancelDelete">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Delete Sub-Category
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
