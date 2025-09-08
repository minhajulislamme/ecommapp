<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search, Edit, Trash2, ToggleLeft, ToggleRight, Palette, AlertTriangle } from 'lucide-vue-next';
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

interface ProductAttribute {
    id: number;
    name: string;
    slug: string;
    description?: string;
    type: 'text' | 'number' | 'color';
    options?: string[];
    is_required: boolean;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    attributes: {
        data: ProductAttribute[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search?: string;
        type?: string;
        status?: string;
    };
    typeOptions: Record<string, string>;
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');
const statusFilter = ref(props.filters.status || '');

// Delete confirmation dialog state
const showDeleteDialog = ref(false);
const attributeToDelete = ref<ProductAttribute | null>(null);

// Get all attributes data
const allAttributes = computed(() => props.attributes.data);

// Client-side filtering without page reload
const filteredAttributes = computed(() => {
    let filtered = allAttributes.value;

    // Apply search filter
    if (searchQuery.value.trim()) {
        const searchTerm = searchQuery.value.toLowerCase().trim();
        filtered = filtered.filter(attribute => 
            attribute.name.toLowerCase().includes(searchTerm) ||
            attribute.slug.toLowerCase().includes(searchTerm) ||
            (attribute.description && attribute.description.toLowerCase().includes(searchTerm))
        );
    }

    // Apply type filter
    if (typeFilter.value) {
        filtered = filtered.filter(attribute => attribute.type === typeFilter.value);
    }

    // Apply status filter
    if (statusFilter.value) {
        filtered = filtered.filter(attribute => {
            if (statusFilter.value === 'active') {
                return attribute.is_active;
            } else if (statusFilter.value === 'inactive') {
                return !attribute.is_active;
            }
            return true;
        });
    }

    return filtered;
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: '/admin/dashboard' },
    { title: 'Product Attributes', href: '/admin/product-attributes' },
];

const clearFilters = () => {
    searchQuery.value = '';
    typeFilter.value = '';
    statusFilter.value = '';
};

const toggleStatus = (attribute: ProductAttribute) => {
    router.patch(`/admin/product-attributes/${attribute.id}/toggle-status`, {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const deleteAttribute = (attribute: ProductAttribute) => {
    attributeToDelete.value = attribute;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (attributeToDelete.value) {
        router.delete(`/admin/product-attributes/${attributeToDelete.value.id}`, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                showDeleteDialog.value = false;
                attributeToDelete.value = null;
            },
            onError: () => {
                showDeleteDialog.value = false;
                attributeToDelete.value = null;
            }
        });
    }
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    attributeToDelete.value = null;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getTypeLabel = (type: string) => {
    return props.typeOptions[type] || type;
};

const getTypeBadgeClass = (type: string) => {
    const classes = {
        text: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
        number: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        color: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
    };
    return classes[type as keyof typeof classes] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
};

const getColorFromOption = (option: string) => {
    try {
        const colorData = JSON.parse(option);
        return colorData;
    } catch {
        // For backward compatibility with simple string colors
        return { name: option, code: '#000000' };
    }
};

const isValidColor = (option: string) => {
    try {
        JSON.parse(option);
        return true;
    } catch {
        return false;
    }
};

const hasColorOptions = (attribute: ProductAttribute) => {
    return attribute.type === 'color' && attribute.options && attribute.options.length > 0;
};
</script>

<template>
    <Head title="Product Attributes" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Product Attributes</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage product attributes like color, size, material, etc.</p>
                </div>
                <Link :href="'/admin/product-attributes/create'">
                    <Button class="flex items-center gap-2">
                        <Plus class="h-4 w-4" />
                        Add Attribute
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
                            type="text"
                            placeholder="Search attributes..."
                            class="pl-10"
                        />
                    </div>
                    <select
                        v-model="typeFilter"
                        class="flex h-9 w-32 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">All Types</option>
                        <option v-for="(label, value) in typeOptions" :key="value" :value="value">{{ label }}</option>
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
                    Showing {{ filteredAttributes.length }} of {{ allAttributes.length }} attributes
                </div>
            </div>

            <!-- Attributes Table -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800 overflow-hidden">
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Attribute</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Description</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Options</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Required</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Created</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-if="filteredAttributes.length === 0">
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <Palette class="h-12 w-12 text-gray-400" />
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">No attributes found</h3>
                                        <p class="text-gray-500 dark:text-gray-400">
                                            {{ searchQuery || typeFilter || statusFilter ? 'Try adjusting your search or filter criteria' : 'Get started by creating your first product attribute' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr v-for="attribute in filteredAttributes" :key="attribute.id" class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                <!-- Attribute Info -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                            <Palette class="h-5 w-5 text-gray-400" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ attribute.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ attribute.slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Type -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          :class="getTypeBadgeClass(attribute.type)">
                                        {{ getTypeLabel(attribute.type) }}
                                    </span>
                                </td>
                                
                                <!-- Description -->
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                        {{ attribute.description || 'No description' }}
                                    </p>
                                </td>
                                
                                <!-- Options -->
                                <td class="px-6 py-4">
                                    <div v-if="attribute.options && attribute.options.length > 0" class="flex flex-wrap gap-1 max-w-xs">
                                        <!-- Color options display -->
                                        <template v-if="attribute.type === 'color'">
                                            <div 
                                                v-for="(option, idx) in attribute.options.slice(0, 3)" 
                                                :key="idx"
                                                class="flex items-center gap-1"
                                            >
                                                <div 
                                                    v-if="isValidColor(option)"
                                                    class="w-4 h-4 rounded border border-gray-300"
                                                    :style="{ backgroundColor: getColorFromOption(option).code }"
                                                    :title="getColorFromOption(option).name"
                                                ></div>
                                                <span v-else class="text-xs bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded">{{ option }}</span>
                                            </div>
                                            <span v-if="attribute.options.length > 3" class="text-xs text-gray-500">+{{ attribute.options.length - 3 }} more</span>
                                        </template>
                                        
                                        <!-- Text/other options display -->
                                        <template v-else>
                                            <span 
                                                v-for="(option, idx) in attribute.options.slice(0, 2)" 
                                                :key="idx"
                                                class="text-xs bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded"
                                            >{{ option }}</span>
                                            <span v-if="attribute.options.length > 2" class="text-xs text-gray-500">+{{ attribute.options.length - 2 }} more</span>
                                        </template>
                                    </div>
                                    <span v-else class="text-sm text-gray-400">No options</span>
                                </td>
                                
                                <!-- Required -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          :class="attribute.is_required 
                                            ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' 
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
                                        {{ attribute.is_required ? 'Required' : 'Optional' }}
                                    </span>
                                </td>
                                
                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                          :class="attribute.is_active 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' 
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
                                        {{ attribute.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                
                                <!-- Created -->
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(attribute.created_at) }}
                                    </span>
                                </td>
                                
                                <!-- Actions -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Button variant="ghost" size="sm" asChild>
                                            <Link :href="`/admin/product-attributes/${attribute.id}/edit`">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="toggleStatus(attribute)">
                                            <component :is="attribute.is_active ? ToggleLeft : ToggleRight" class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="deleteAttribute(attribute)" class="text-red-600 hover:text-red-700">
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
                    <div v-if="filteredAttributes.length === 0" class="p-6">
                        <div class="flex flex-col items-center gap-2 py-8">
                            <Palette class="h-12 w-12 text-gray-400" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No attributes found</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-center">
                                {{ searchQuery || typeFilter || statusFilter ? 'Try adjusting your search or filter criteria' : 'Get started by creating your first product attribute' }}
                            </p>
                        </div>
                    </div>
                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="attribute in filteredAttributes" :key="attribute.id" class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="h-12 w-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                    <Palette class="h-6 w-6 text-gray-400" />
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ attribute.name }}</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ attribute.slug }}</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ml-2"
                                              :class="getTypeBadgeClass(attribute.type)">
                                            {{ getTypeLabel(attribute.type) }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center gap-4 mt-2">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                              :class="attribute.is_required 
                                                ? 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' 
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
                                            {{ attribute.is_required ? 'Required' : 'Optional' }}
                                        </span>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                              :class="attribute.is_active 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' 
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'">
                                            {{ attribute.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 line-clamp-2">
                                        {{ attribute.description || 'No description available' }}
                                    </p>
                                    
                                    <div class="flex items-center justify-between mt-3">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(attribute.created_at) }}
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 mt-4">
                                        <Button variant="outline" size="sm" asChild>
                                            <Link :href="`/admin/product-attributes/${attribute.id}/edit`" class="flex items-center gap-2">
                                                <Edit class="h-4 w-4" />
                                                Edit
                                            </Link>
                                        </Button>
                                        <Button variant="outline" size="sm" @click="toggleStatus(attribute)" class="flex items-center gap-2">
                                            <component :is="attribute.is_active ? ToggleLeft : ToggleRight" class="h-4 w-4" />
                                            {{ attribute.is_active ? 'Deactivate' : 'Activate' }}
                                        </Button>
                                        <Button variant="outline" size="sm" @click="deleteAttribute(attribute)" class="flex items-center gap-2 text-red-600 hover:text-red-700">
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
                    <DialogTitle class="flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5 text-red-600" />
                        Delete Product Attribute
                    </DialogTitle>
                    <DialogDescription class="text-left">
                        Are you sure you want to delete "{{ attributeToDelete?.name }}"? This action cannot be undone and will permanently remove:
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li>The attribute and all its data</li>
                            <li>Any product associations with this attribute</li>
                        </ul>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex gap-2 sm:gap-0">
                    <Button variant="outline" @click="cancelDelete">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        Delete Attribute
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
