<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Tag, ChevronRight, Package } from 'lucide-vue-next';

interface SubCategory {
    id: number;
    name: string;
    slug: string;
    products_count: number;
}

interface Category {
    id: number;
    name: string;
    slug: string;
    description?: string;
    image?: string;
    sub_categories_count: number;
    products_count: number;
    subCategories: SubCategory[];
}

interface Props {
    categories: Category[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Categories - EcommApp" />
    
    <PublicLayout>
        <!-- Page Header -->
        <section class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Product Categories</h1>
                    <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                        Explore our wide range of product categories and find exactly what you're looking for.
                    </p>
                </div>
            </div>
        </section>

        <!-- Categories Grid -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-if="categories.length === 0" class="text-center py-12">
                    <Tag class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Categories Available</h3>
                    <p class="text-gray-600 dark:text-gray-400">We're working on adding more categories. Please check back soon!</p>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div 
                        v-for="category in categories" 
                        :key="category.id"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <!-- Category Header -->
                        <div class="relative">
                            <Link :href="`/categories/${category.slug}`" class="block group">
                                <div class="relative overflow-hidden">
                                    <div v-if="category.image" class="h-48 bg-gray-100 dark:bg-gray-700">
                                        <img 
                                            :src="`/upload/categories/${category.image}`" 
                                            :alt="category.name"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        />
                                    </div>
                                    <div v-else class="h-48 bg-gradient-to-br from-blue-100 to-purple-100 dark:from-blue-900 dark:to-purple-900 flex items-center justify-center">
                                        <Tag class="w-20 h-20 text-blue-500 dark:text-blue-400" />
                                    </div>
                                    
                                    <!-- Overlay -->
                                    <div class="absolute inset-0 bg-black bg-opacity-20 group-hover:bg-opacity-30 transition-all duration-300"></div>
                                    
                                    <!-- Category Info Overlay -->
                                    <div class="absolute bottom-4 left-4 right-4">
                                        <h3 class="text-2xl font-bold text-white mb-2">{{ category.name }}</h3>
                                        <div class="flex items-center gap-4 text-white/90 text-sm">
                                            <span>{{ category.products_count }} products</span>
                                            <span>{{ category.sub_categories_count }} subcategories</span>
                                        </div>
                                    </div>
                                    
                                    <!-- View Arrow -->
                                    <div class="absolute top-4 right-4 bg-white bg-opacity-20 backdrop-blur-sm rounded-full p-2 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                        <ChevronRight class="w-5 h-5 text-white" />
                                    </div>
                                </div>
                            </Link>
                        </div>

                        <!-- Category Content -->
                        <div class="p-6">
                            <p v-if="category.description" class="text-gray-600 dark:text-gray-400 mb-6 line-clamp-2">
                                {{ category.description }}
                            </p>
                            
                            <!-- Subcategories -->
                            <div v-if="category.subCategories && category.subCategories.length > 0">
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subcategories</h4>
                                <div class="grid grid-cols-1 gap-3">
                                    <Link 
                                        v-for="subCategory in category.subCategories" 
                                        :key="subCategory.id"
                                        :href="`/products?subcategory=${subCategory.id}`"
                                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors group"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                                <Tag class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ subCategory.name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                                            <span class="text-sm">{{ subCategory.products_count }} products</span>
                                            <ChevronRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                                        </div>
                                    </Link>
                                </div>
                            </div>
                            
                            <!-- No Subcategories Message -->
                            <div v-else class="text-center py-6">
                                <Package class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                                <p class="text-gray-500 dark:text-gray-400">No subcategories available</p>
                            </div>
                            
                            <!-- View All Products Button -->
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <Link 
                                    :href="`/categories/${category.slug}`"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    <Package class="w-4 h-4 mr-2" />
                                    View All {{ category.products_count }} Products
                                </Link>
                            </div>
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
