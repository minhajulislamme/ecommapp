<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Star, ShoppingCart, Package, Filter, Grid, List, Tag } from 'lucide-vue-next';
import { ref } from 'vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
}

interface SubCategory {
    id: number;
    name: string;
    slug: string;
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
    category: Category;
    products: {
        data: Product[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    subCategories: SubCategory[];
}

const props = defineProps<Props>();

const selectedSubCategory = ref('');
const sortBy = ref('latest');
const viewMode = ref<'grid' | 'list'>('grid');

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const filterBySubCategory = (subCategoryId: string) => {
    selectedSubCategory.value = subCategoryId;
    const params = new URLSearchParams();
    if (subCategoryId) params.set('subcategory', subCategoryId);
    if (sortBy.value !== 'latest') params.set('sort', sortBy.value);
    
    router.get(`/categories/${props.category.slug}`, Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true,
    });
};

const changeSorting = () => {
    const params = new URLSearchParams();
    if (selectedSubCategory.value) params.set('subcategory', selectedSubCategory.value);
    if (sortBy.value !== 'latest') params.set('sort', sortBy.value);
    
    router.get(`/categories/${props.category.slug}`, Object.fromEntries(params), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`${category.name} - EcommApp`" />
    
    <PublicLayout>
        <!-- Category Header -->
        <section class="relative bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 py-16 overflow-hidden">
            <div class="absolute inset-0">
                <div v-if="category.image" class="w-full h-full opacity-10">
                    <img 
                        :src="`/upload/categories/${category.image}`" 
                        :alt="category.name"
                        class="w-full h-full object-cover"
                    />
                </div>
            </div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav class="flex mb-8" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm">
                        <li>
                            <Link href="/" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                Home
                            </Link>
                        </li>
                        <li class="text-gray-400">/</li>
                        <li>
                            <Link href="/categories" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                Categories
                            </Link>
                        </li>
                        <li class="text-gray-400">/</li>
                        <li class="text-gray-900 dark:text-white font-medium">{{ category.name }}</li>
                    </ol>
                </nav>
                
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">{{ category.name }}</h1>
                    <p v-if="category.description" class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto mb-6">
                        {{ category.description }}
                    </p>
                    <div class="text-gray-600 dark:text-gray-400">
                        {{ products.total }} products found
                    </div>
                </div>
            </div>
        </section>

        <!-- Subcategories Filter -->
        <section v-if="subCategories.length > 0" class="py-8 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Shop by Subcategory</h3>
                <div class="flex flex-wrap gap-3">
                    <button
                        @click="filterBySubCategory('')"
                        :class="[
                            'px-4 py-2 rounded-lg font-medium transition-colors',
                            selectedSubCategory === '' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
                        ]"
                    >
                        All Products ({{ products.total }})
                    </button>
                    <button
                        v-for="subCategory in subCategories"
                        :key="subCategory.id"
                        @click="filterBySubCategory(subCategory.id.toString())"
                        :class="[
                            'flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors',
                            selectedSubCategory === subCategory.id.toString() 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'
                        ]"
                    >
                        <Tag class="w-4 h-4" />
                        {{ subCategory.name }} ({{ subCategory.products_count }})
                    </button>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section class="py-8 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Controls -->
                <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center mb-8">
                    <div class="text-gray-600 dark:text-gray-400">
                        Showing {{ products.data.length }} of {{ products.total }} products
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <!-- Sort -->
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</label>
                            <select
                                v-model="sortBy"
                                @change="changeSorting"
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
                
                <!-- No Products Message -->
                <div v-if="products.data.length === 0" class="text-center py-12">
                    <Package class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Products Found</h3>
                    <p class="text-gray-600 dark:text-gray-400">We don't have any products in this category yet.</p>
                </div>
                
                <!-- Products Grid -->
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
                                    <div v-if="product.sub_category" class="text-sm text-blue-600 dark:text-blue-400 mb-1">
                                        {{ product.sub_category.name }}
                                    </div>
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
                
                <!-- Products List -->
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
                                        <div v-if="product.sub_category" class="text-sm text-blue-600 dark:text-blue-400 mb-1">
                                            {{ product.sub_category.name }}
                                        </div>
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
