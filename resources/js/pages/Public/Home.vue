<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Star, ShoppingCart, Eye, Tag, Package, TrendingUp } from 'lucide-vue-next';

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    sub_categories_count?: number;
    subCategories?: SubCategory[];
}

interface SubCategory {
    id: number;
    name: string;
    slug: string;
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
    featuredProducts: Product[];
    categories: Category[];
    latestProducts: Product[];
}

const props = defineProps<Props>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};
</script>

<template>
    <Head title="EcommApp - Your Online Shopping Destination" />
    
    <PublicLayout>
        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900 overflow-hidden">
            <div class="absolute inset-0">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
            </div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
                <div class="text-center">
                    <h1 class="text-4xl md:text-6xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-6">
                        Welcome to EcommApp
                    </h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                        Discover amazing products at unbeatable prices. Shop the latest trends and find everything you need.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link 
                            :href="'/products'"
                            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                        >
                            <Package class="w-5 h-5 mr-2" />
                            Shop Now
                        </Link>
                        <Link 
                            :href="'/categories'"
                            class="inline-flex items-center px-8 py-4 bg-white dark:bg-gray-800 text-gray-800 dark:text-white font-semibold rounded-xl shadow-lg hover:shadow-xl border border-gray-200 dark:border-gray-700 transform hover:scale-105 transition-all duration-200"
                        >
                            <Tag class="w-5 h-5 mr-2" />
                            Browse Categories
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Shop by Category</h2>
                    <p class="text-gray-600 dark:text-gray-400">Explore our wide range of product categories</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div 
                        v-for="category in categories" 
                        :key="category.id"
                        class="group cursor-pointer"
                    >
                        <Link :href="`/categories/${category.slug}`">
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform group-hover:scale-105 border border-gray-200 dark:border-gray-700">
                                <div class="relative overflow-hidden rounded-t-xl">
                                    <div v-if="category.image" class="h-48 bg-gray-100 dark:bg-gray-700">
                                        <img 
                                            :src="`/upload/categories/${category.image}`" 
                                            :alt="category.name"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        />
                                    </div>
                                    <div v-else class="h-48 bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900 dark:to-purple-900 flex items-center justify-center">
                                        <Tag class="w-16 h-16 text-blue-500 dark:text-blue-400" />
                                    </div>
                                </div>
                                
                                <div class="p-6">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ category.name }}</h3>
                                    <p v-if="category.description" class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                        {{ category.description }}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ category.sub_categories_count || 0 }} subcategories
                                        </span>
                                        <span class="text-blue-600 dark:text-blue-400 font-medium group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                                            Explore →
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
                
                <div class="text-center mt-8">
                    <Link 
                        :href="'/categories'"
                        class="inline-flex items-center px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-white font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                    >
                        View All Categories
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <section v-if="featuredProducts.length > 0" class="py-16 bg-gray-50 dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Featured Products</h2>
                    <p class="text-gray-600 dark:text-gray-400">Handpicked products just for you</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="product in featuredProducts" 
                        :key="product.id"
                        class="group cursor-pointer"
                    >
                        <Link :href="`/products/${product.slug}`">
                            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform group-hover:scale-105 border border-gray-200 dark:border-gray-700">
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
                                    
                                    <!-- Featured Badge -->
                                    <div class="absolute top-3 left-3">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                            <Star class="w-3 h-3 mr-1" />
                                            Featured
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
            </div>
        </section>

        <!-- Latest Products Section -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Latest Products</h2>
                    <p class="text-gray-600 dark:text-gray-400">Check out our newest arrivals</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="product in latestProducts" 
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
                                    
                                    <!-- Sale Badge -->
                                    <div v-if="product.sale_price" class="absolute top-3 right-3">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
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
                                            <Eye class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
                
                <div class="text-center mt-8">
                    <Link 
                        :href="'/products'"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <TrendingUp class="w-4 h-4 mr-2" />
                        View All Products
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
@keyframes blob {
    0% {
        transform: translate(0px, 0px) scale(1);
    }
    33% {
        transform: translate(30px, -50px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
    100% {
        transform: translate(0px, 0px) scale(1);
    }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
