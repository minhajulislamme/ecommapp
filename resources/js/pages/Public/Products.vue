<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Search, Filter, Grid, List, Star, ShoppingCart, Package, SlidersHorizontal, ChevronDown } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Category {
    id: number;
    name: string;
    products_count: number;
}

interface SubCategory {
    id: number;
    name: string;
    products_count: number;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    description?: string;
    price: number;
    sale_price?: number;
    image?: string;
    is_featured: boolean;
    category: {
        id: number;
        name: string;
    };
    sub_category?: {
        id: number;
        name: string;
    };
}

interface Props {
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    categories: Category[];
    subCategories: SubCategory[];
    filters: {
        category?: string;
        subcategory?: string;
        search?: string;
        min_price?: string;
        max_price?: string;
        sort?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const selectedSubCategory = ref(props.filters.subcategory || '');
const minPrice = ref(props.filters.min_price || '');
const maxPrice = ref(props.filters.max_price || '');
const sortBy = ref(props.filters.sort || 'latest');
const viewMode = ref<'grid' | 'list'>('grid');
const showMobileFilters = ref(false);

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const applyFilters = () => {
    const queryParams = new URLSearchParams();
    
    if (searchQuery.value) queryParams.set('search', searchQuery.value);
    if (selectedCategory.value) queryParams.set('category', selectedCategory.value);
    if (selectedSubCategory.value) queryParams.set('subcategory', selectedSubCategory.value);
    if (minPrice.value) queryParams.set('min_price', minPrice.value);
    if (maxPrice.value) queryParams.set('max_price', maxPrice.value);
    if (sortBy.value !== 'latest') queryParams.set('sort', sortBy.value);
    
    router.get('/products', Object.fromEntries(queryParams), {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedSubCategory.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    sortBy.value = 'latest';
    
    router.get('/products', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const filteredSubCategories = computed(() => {
    if (!selectedCategory.value) return props.subCategories;
    return props.subCategories.filter(sub => 
        // You might need to add category_id to subcategories in the controller
        true // For now, show all subcategories
    );
});
</script>

<template>
    <Head title="Products - EcommApp" />
    
    <PublicLayout>
        <!-- Page Header -->
        <section class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">All Products</h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                        Discover our complete collection of amazing products at great prices.
                    </p>
                </div>
            </div>
        </section>

        <!-- Filters and Products -->
        <section class="py-8 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Sidebar Filters (Desktop) -->
                    <div class="hidden lg:block w-80 flex-shrink-0">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 sticky top-24">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                                <SlidersHorizontal class="w-5 h-5" />
                                Filters
                            </h3>
                            
                            <!-- Search -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                                <div class="relative">
                                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search products..."
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        @keyup.enter="applyFilters"
                                    />
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                                <select
                                    v-model="selectedCategory"
                                    class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="category in categories" :key="category.id" :value="category.id">
                                        {{ category.name }} ({{ category.products_count }})
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Subcategory -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subcategory</label>
                                <select
                                    v-model="selectedSubCategory"
                                    :disabled="!selectedCategory"
                                    class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:opacity-50"
                                >
                                    <option value="">All Subcategories</option>
                                    <option v-for="subCategory in filteredSubCategories" :key="subCategory.id" :value="subCategory.id">
                                        {{ subCategory.name }} ({{ subCategory.products_count }})
                                    </option>
                                </select>
                            </div>
                            
                            <!-- Price Range -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price Range</label>
                                <div class="flex gap-2">
                                    <input
                                        v-model="minPrice"
                                        type="number"
                                        placeholder="Min"
                                        class="flex-1 p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                    <input
                                        v-model="maxPrice"
                                        type="number"
                                        placeholder="Max"
                                        class="flex-1 p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    />
                                </div>
                            </div>
                            
                            <!-- Filter Actions -->
                            <div class="flex flex-col gap-2">
                                <button
                                    @click="applyFilters"
                                    class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    Apply Filters
                                </button>
                                <button
                                    @click="clearFilters"
                                    class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >
                                    Clear All
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Content -->
                    <div class="flex-1">
                        <!-- Mobile Filters Button + Sort + View Toggle -->
                        <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center mb-6">
                            <div class="flex items-center gap-4">
                                <!-- Mobile Filter Button -->
                                <button
                                    @click="showMobileFilters = !showMobileFilters"
                                    class="lg:hidden flex items-center gap-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >
                                    <Filter class="w-4 h-4" />
                                    Filters
                                </button>
                                
                                <!-- Results Count -->
                                <div class="text-gray-600 dark:text-gray-400">
                                    Showing {{ products.data.length }} of {{ products.total }} products
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <!-- Sort -->
                                <div class="flex items-center gap-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</label>
                                    <select
                                        v-model="sortBy"
                                        @change="applyFilters"
                                        class="p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="latest">Latest</option>
                                        <option value="price_low">Price: Low to High</option>
                                        <option value="price_high">Price: High to Low</option>
                                        <option value="name">Name: A to Z</option>
                                        <option value="featured">Featured</option>
                                    </select>
                                </div>
                                
                                <!-- View Toggle -->
                                <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                                    <button
                                        @click="viewMode = 'grid'"
                                        :class="[
                                            'p-2 transition-colors',
                                            viewMode === 'grid' 
                                                ? 'bg-blue-600 text-white' 
                                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600'
                                        ]"
                                    >
                                        <Grid class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="viewMode = 'list'"
                                        :class="[
                                            'p-2 transition-colors',
                                            viewMode === 'list' 
                                                ? 'bg-blue-600 text-white' 
                                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-600'
                                        ]"
                                    >
                                        <List class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mobile Filters (Collapsible) -->
                        <div v-if="showMobileFilters" class="lg:hidden mb-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                            <!-- Same filters as desktop sidebar, but in mobile layout -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Search -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                                    <div class="relative">
                                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input
                                            v-model="searchQuery"
                                            type="text"
                                            placeholder="Search products..."
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            @keyup.enter="applyFilters"
                                        />
                                    </div>
                                </div>
                                
                                <!-- Category -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
                                    <select
                                        v-model="selectedCategory"
                                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="">All Categories</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>
                                
                                <!-- Subcategory -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subcategory</label>
                                    <select
                                        v-model="selectedSubCategory"
                                        class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="">All Subcategories</option>
                                        <option v-for="subCategory in filteredSubCategories" :key="subCategory.id" :value="subCategory.id">
                                            {{ subCategory.name }}
                                        </option>
                                    </select>
                                </div>
                                
                                <!-- Price Range -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price Range</label>
                                    <div class="flex gap-2">
                                        <input
                                            v-model="minPrice"
                                            type="number"
                                            placeholder="Min Price"
                                            class="flex-1 p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        />
                                        <input
                                            v-model="maxPrice"
                                            type="number"
                                            placeholder="Max Price"
                                            class="flex-1 p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        />
                                    </div>
                                </div>
                                
                                <!-- Filter Actions -->
                                <div class="sm:col-span-2 flex gap-2">
                                    <button
                                        @click="applyFilters"
                                        class="flex-1 px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                    >
                                        Apply Filters
                                    </button>
                                    <button
                                        @click="clearFilters"
                                        class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        Clear All
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Products Grid/List -->
                        <div v-if="products.data.length === 0" class="text-center py-12">
                            <Package class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Products Found</h3>
                            <p class="text-gray-600 dark:text-gray-400">Try adjusting your filters or search terms.</p>
                        </div>
                        
                        <!-- Grid View -->
                        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <div 
                                v-for="product in products.data" 
                                :key="product.id"
                                class="group cursor-pointer"
                            >
                                <Link :href="`/products/${product.slug}`">
                                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform group-hover:scale-105 border border-gray-200 dark:border-gray-700">
                                        <div class="relative overflow-hidden rounded-t-xl">
                                            <div v-if="product.image" class="h-48 bg-gray-100 dark:bg-gray-700">
                                                <img 
                                                    :src="`/upload/products/${product.image}`" 
                                                    :alt="product.name"
                                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                                />
                                            </div>
                                            <div v-else class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                                <Package class="w-16 h-16 text-gray-400" />
                                            </div>
                                            
                                            <!-- Badges -->
                                            <div class="absolute top-3 left-3 flex flex-col gap-2">
                                                <span v-if="product.is_featured" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                    <Star class="w-3 h-3 mr-1" />
                                                    Featured
                                                </span>
                                                <span v-if="product.sale_price" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                    Sale
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="p-4">
                                            <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">{{ product.category.name }}</div>
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">{{ product.name }}</h3>
                                            
                                            <div class="flex items-center justify-between">
                                                <div class="flex flex-col">
                                                    <div v-if="product.sale_price" class="flex items-center gap-2">
                                                        <span class="text-lg font-bold text-red-600 dark:text-red-400">{{ formatPrice(product.sale_price) }}</span>
                                                        <span class="text-sm text-gray-500 line-through">{{ formatPrice(product.price) }}</span>
                                                    </div>
                                                    <div v-else>
                                                        <span class="text-lg font-bold text-gray-900 dark:text-white">{{ formatPrice(product.price) }}</span>
                                                    </div>
                                                </div>
                                                
                                                <button class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-200">
                                                    <ShoppingCart class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                        
                        <!-- List View -->
                        <div v-else class="space-y-4">
                            <div 
                                v-for="product in products.data" 
                                :key="product.id"
                                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200 dark:border-gray-700 p-6"
                            >
                                <Link :href="`/products/${product.slug}`" class="flex gap-6 group">
                                    <div class="relative overflow-hidden rounded-lg flex-shrink-0">
                                        <div v-if="product.image" class="w-32 h-32 bg-gray-100 dark:bg-gray-700">
                                            <img 
                                                :src="`/upload/products/${product.image}`" 
                                                :alt="product.name"
                                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                            />
                                        </div>
                                        <div v-else class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                            <Package class="w-12 h-12 text-gray-400" />
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">{{ product.category.name }}</div>
                                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ product.name }}</h3>
                                                <p v-if="product.description" class="text-gray-600 dark:text-gray-400 line-clamp-2 mb-4">
                                                    {{ product.description }}
                                                </p>
                                                
                                                <div class="flex items-center gap-4">
                                                    <div class="flex flex-col">
                                                        <div v-if="product.sale_price" class="flex items-center gap-2">
                                                            <span class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatPrice(product.sale_price) }}</span>
                                                            <span class="text-lg text-gray-500 line-through">{{ formatPrice(product.price) }}</span>
                                                        </div>
                                                        <div v-else>
                                                            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatPrice(product.price) }}</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-2">
                                                        <span v-if="product.is_featured" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                            <Star class="w-3 h-3 mr-1" />
                                                            Featured
                                                        </span>
                                                        <span v-if="product.sale_price" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                            Sale
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <button class="p-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                                <ShoppingCart class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                        
                        <!-- Pagination -->
                        <div v-if="products.last_page > 1" class="mt-12 flex justify-center">
                            <nav class="flex items-center gap-2">
                                <Link
                                    v-for="link in products.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : link.url
                                            ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                    preserve-scroll
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
