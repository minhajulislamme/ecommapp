<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search, Edit, Trash2, ToggleLeft, ToggleRight, FolderOpen, AlertTriangle } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
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
    slug: string;
    description?: string;
    image?: string;
    is_active: boolean;
    sub_categories_count?: number;
    created_at: string;
    updated_at: string;
}

interface Props {
    categories: {
        data: Category[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref('');
const statusFilter = ref('');

// Delete confirmation dialog state
const showDeleteDialog = ref(false);
const categoryToDelete = ref<Category | null>(null);

// Get all categories data
const allCategories = computed(() => props.categories.data);

// Client-side filtering
const filteredCategories = computed(() => {
    let filtered = allCategories.value;

    // Apply search filter
    if (searchQuery.value.trim()) {
        const searchTerm = searchQuery.value.toLowerCase().trim();
        filtered = filtered.filter(category => 
            category.name.toLowerCase().includes(searchTerm) ||
            category.slug.toLowerCase().includes(searchTerm) ||
            (category.description && category.description.toLowerCase().includes(searchTerm))
        );
    }

    // Apply status filter
    if (statusFilter.value) {
        filtered = filtered.filter(category => {
            if (statusFilter.value === 'active') {
                return category.is_active;
            } else if (statusFilter.value === 'inactive') {
                return !category.is_active;
            }
            return true;
        });
    }

    return filtered;
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Category Management', href: '/admin/categories' },
];

const clearFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
};

const toggleStatus = (category: Category) => {
    router.patch(`/admin/categories/${category.id}/toggle-status`, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteCategory = (category: Category) => {
    categoryToDelete.value = category;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (categoryToDelete.value) {
        router.delete(`/admin/categories/${categoryToDelete.value.id}`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                showDeleteDialog.value = false;
                categoryToDelete.value = null;
            },
            onError: () => {
                showDeleteDialog.value = false;
                categoryToDelete.value = null;
            }
        });
    }
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    categoryToDelete.value = null;
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
    <Head title="Category Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Category Management</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage product categories</p>
                </div>
                <Link :href="'/admin/categories/create'">
                    <Button class="flex items-center gap-2">
                        <Plus class="h-4 w-4" />
                        Add Category
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
                            placeholder="Search categories..."
                            class="pl-9"
                        />
                    </div>
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
                    Showing {{ filteredCategories.length }} of {{ allCategories.length }} categories
                </div>
            </div>

            <!-- Categories Table -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800 overflow-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Category</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Description</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Sub-Categories</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Created</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-if="filteredCategories.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <FolderOpen class="h-12 w-12 text-gray-400" />
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">No categories found</h3>
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery || statusFilter ? 'Try adjusting your search or filter criteria' : 'Get started by creating your first category' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="category in filteredCategories" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                
                                <!-- Category -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div v-if="category.image" class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0">
                                            <img :src="`/upload/categories/${category.image}`" :alt="category.name" class="h-full w-full object-cover" />
                                        </div>
                                        <div v-else class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                            <FolderOpen class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ category.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ category.slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Description -->
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                        {{ category.description || 'No description' }}
                                    </p>
                                </td>
                                
                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          :class="category.is_active 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' 
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                
                                <!-- Sub-Categories -->
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900 dark:text-white font-medium">
                                        {{ category.sub_categories_count || 0 }}
                                    </span>
                                </td>
                                
                                <!-- Created -->
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(category.created_at) }}
                                    </span>
                                </td>
                                
                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Button variant="ghost" size="sm" asChild>
                                            <Link :href="`/admin/categories/${category.id}/edit`">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="toggleStatus(category)">
                                            <component :is="category.is_active ? ToggleLeft : ToggleRight" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="deleteCategory(category)" class="text-red-600 hover:text-red-700">
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
                    <div v-if="filteredCategories.length === 0" class="p-6">
                        <div class="flex flex-col items-center gap-2 py-8">
                            <FolderOpen class="h-12 w-12 text-gray-400" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No categories found</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-center">
                                {{ searchQuery || statusFilter ? 'Try adjusting your search or filter criteria' : 'Get started by creating your first category' }}
                            </p>
                        </div>
                    </div>
                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="category in filteredCategories" :key="category.id" class="p-6">
                            <div class="flex items-start gap-4">
                                <div v-if="category.image" class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0">
                                    <img :src="`/upload/categories/${category.image}`" :alt="category.name" class="h-full w-full object-cover" />
                                </div>
                                <div v-else class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                    <FolderOpen class="h-6 w-6 text-gray-400" />
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ category.name }}</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ category.slug }}</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ml-2"
                                              :class="category.is_active 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' 
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
                                            {{ category.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 line-clamp-2">
                                        {{ category.description || 'No description available' }}
                                    </p>
                                    
                                    <div class="flex items-center justify-between mt-3">
                                        <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                            <span>{{ category.sub_categories_count || 0 }} sub-categories</span>
                                            <span>{{ formatDate(category.created_at) }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 mt-4">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link :href="`/admin/categories/${category.id}/edit`" class="flex items-center gap-2">
                                                <Edit class="h-4 w-4" />
                                                Edit
                                            </Link>
                                        </Button>
                                        <Button variant="outline" size="sm" @click="toggleStatus(category)" class="flex items-center gap-2">
                                            <component :is="category.is_active ? ToggleLeft : ToggleRight" class="h-4 w-4" />
                                            {{ category.is_active ? 'Deactivate' : 'Activate' }}
                                        </Button>
                                        <Button variant="outline" size="sm" @click="deleteCategory(category)" class="flex items-center gap-2 text-red-600">
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
                        Are you sure you want to delete "<strong>{{ categoryToDelete?.name }}</strong>"? 
                        <br><br>
                        <span class="text-red-600 font-medium">
                            This action cannot be undone and will permanently delete:
                        </span>
                        <ul class="mt-2 list-disc list-inside text-sm space-y-1">
                            <li>The category and all its data</li>
                            <li>All associated sub-categories</li>
                            <li>All uploaded images for the category and sub-categories</li>
                        </ul>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex gap-2 sm:gap-0">
                    <Button variant="outline" @click="cancelDelete">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Delete Category
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
