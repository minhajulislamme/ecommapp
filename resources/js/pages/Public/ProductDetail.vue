<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Star, ShoppingCart, Heart, Share2, Package, Truck, Shield, RotateCcw, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

interface Product {
    id: number;
    name: string;
    slug: string;
    description?: string;
    price: number;
    sale_price?: number;
    sku: string;
    stock_quantity: number;
    image?: string;
    images?: string[];
    is_featured: boolean;
    category: {
        id: number;
        name: string;
        slug: string;
    };
    sub_category?: {
        id: number;
        name: string;
        slug: string;
    };
}

interface Props {
    product: Product;
    relatedProducts: Product[];
    similarProducts: Product[];
}

const props = defineProps<Props>();

const selectedImageIndex = ref(0);
const quantity = ref(1);

const allImages = [
    props.product.image,
    ...(props.product.images || [])
].filter(Boolean);

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
};

const selectImage = (index: number) => {
    selectedImageIndex.value = index;
};

const nextImage = () => {
    selectedImageIndex.value = (selectedImageIndex.value + 1) % allImages.length;
};

const prevImage = () => {
    selectedImageIndex.value = selectedImageIndex.value === 0 ? allImages.length - 1 : selectedImageIndex.value - 1;
};

const addToCart = () => {
    // Add to cart functionality
    alert(`Added ${quantity.value} ${props.product.name} to cart!`);
};

const saveToWishlist = () => {
    // Wishlist functionality
    alert(`Added ${props.product.name} to wishlist!`);
};

const shareProduct = () => {
    // Share functionality
    if (navigator.share) {
        navigator.share({
            title: props.product.name,
            text: props.product.description,
            url: window.location.href,
        });
    } else {
        // Fallback to copying URL
        navigator.clipboard.writeText(window.location.href);
        alert('Product URL copied to clipboard!');
    }
};
</script>

<template>
    <Head :title="`${product.name} - EcommApp`" />
    
    <PublicLayout>
        <!-- Breadcrumb -->
        <section class="bg-gray-50 dark:bg-gray-800 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm">
                        <li>
                            <Link href="/" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                Home
                            </Link>
                        </li>
                        <li class="text-gray-400">/</li>
                        <li>
                            <Link href="/products" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                Products
                            </Link>
                        </li>
                        <li class="text-gray-400">/</li>
                        <li>
                            <Link :href="`/categories/${product.category.slug}`" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                {{ product.category.name }}
                            </Link>
                        </li>
                        <li v-if="product.sub_category" class="text-gray-400">/</li>
                        <li v-if="product.sub_category">
                            <Link :href="`/products?subcategory=${product.sub_category.id}`" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                {{ product.sub_category.name }}
                            </Link>
                        </li>
                        <li class="text-gray-400">/</li>
                        <li class="text-gray-900 dark:text-white font-medium">{{ product.name }}</li>
                    </ol>
                </nav>
            </div>
        </section>

        <!-- Product Details -->
        <section class="py-12 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <!-- Product Images -->
                    <div class="space-y-4">
                        <!-- Main Image -->
                        <div class="relative aspect-square bg-gray-100 dark:bg-gray-800 rounded-2xl overflow-hidden group">
                            <div v-if="allImages.length > 0" class="w-full h-full">
                                <img 
                                    :src="`/upload/products/${allImages[selectedImageIndex]}`" 
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                />
                                
                                <!-- Navigation arrows for multiple images -->
                                <div v-if="allImages.length > 1" class="absolute inset-y-0 left-0 right-0 flex items-center justify-between p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button
                                        @click="prevImage"
                                        class="p-2 bg-white bg-opacity-80 rounded-full shadow-lg hover:bg-opacity-100 transition-all"
                                    >
                                        <ChevronLeft class="w-5 h-5 text-gray-800" />
                                    </button>
                                    <button
                                        @click="nextImage"
                                        class="p-2 bg-white bg-opacity-80 rounded-full shadow-lg hover:bg-opacity-100 transition-all"
                                    >
                                        <ChevronRight class="w-5 h-5 text-gray-800" />
                                    </button>
                                </div>
                            </div>
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <Package class="w-24 h-24 text-gray-400" />
                            </div>
                            
                            <!-- Badges -->
                            <div class="absolute top-4 left-4 flex flex-col gap-2">
                                <span v-if="product.is_featured" class="inline-flex items-center px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    <Star class="w-4 h-4 mr-1" />
                                    Featured
                                </span>
                                <span v-if="product.sale_price" class="inline-flex items-center px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                    Sale
                                </span>
                            </div>
                        </div>
                        
                        <!-- Thumbnail Images -->
                        <div v-if="allImages.length > 1" class="grid grid-cols-5 gap-2">
                            <button
                                v-for="(image, index) in allImages"
                                :key="index"
                                @click="selectImage(index)"
                                :class="[
                                    'aspect-square bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden border-2 transition-all',
                                    selectedImageIndex === index 
                                        ? 'border-blue-500 ring-2 ring-blue-500 ring-opacity-50' 
                                        : 'border-transparent hover:border-gray-300 dark:hover:border-gray-600'
                                ]"
                            >
                                <img 
                                    :src="`/upload/products/${image}`" 
                                    :alt="`${product.name} ${index + 1}`"
                                    class="w-full h-full object-cover"
                                />
                            </button>
                        </div>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="space-y-6">
                        <!-- Product Title and Category -->
                        <div>
                            <div class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 mb-2">
                                <Link :href="`/categories/${product.category.slug}`" class="hover:underline">
                                    {{ product.category.name }}
                                </Link>
                                <span v-if="product.sub_category">/</span>
                                <Link v-if="product.sub_category" :href="`/products?subcategory=${product.sub_category.id}`" class="hover:underline">
                                    {{ product.sub_category.name }}
                                </Link>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ product.name }}</h1>
                            <p class="text-gray-500 dark:text-gray-400">SKU: {{ product.sku }}</p>
                        </div>
                        
                        <!-- Price -->
                        <div class="flex items-center gap-4">
                            <div v-if="product.sale_price" class="flex items-center gap-3">
                                <span class="text-3xl font-bold text-red-600 dark:text-red-400">{{ formatPrice(product.sale_price) }}</span>
                                <span class="text-xl text-gray-500 line-through">{{ formatPrice(product.price) }}</span>
                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                    Save {{ formatPrice(product.price - product.sale_price) }}
                                </span>
                            </div>
                            <div v-else>
                                <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatPrice(product.price) }}</span>
                            </div>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="flex items-center gap-2">
                            <div :class="[
                                'w-3 h-3 rounded-full',
                                product.stock_quantity > 0 ? 'bg-green-500' : 'bg-red-500'
                            ]"></div>
                            <span :class="[
                                'font-medium',
                                product.stock_quantity > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                            ]">
                                {{ product.stock_quantity > 0 ? `In Stock (${product.stock_quantity} available)` : 'Out of Stock' }}
                            </span>
                        </div>
                        
                        <!-- Description -->
                        <div v-if="product.description">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Description</h3>
                            <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ product.description }}</p>
                        </div>
                        
                        <!-- Quantity and Add to Cart -->
                        <div v-if="product.stock_quantity > 0" class="space-y-4">
                            <div class="flex items-center gap-4">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Quantity:</label>
                                <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg">
                                    <button
                                        @click="quantity = Math.max(1, quantity - 1)"
                                        class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                    >
                                        -
                                    </button>
                                    <input
                                        v-model.number="quantity"
                                        type="number"
                                        min="1"
                                        :max="product.stock_quantity"
                                        class="w-16 text-center border-0 bg-transparent text-gray-900 dark:text-white focus:ring-0"
                                    />
                                    <button
                                        @click="quantity = Math.min(product.stock_quantity, quantity + 1)"
                                        class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-4">
                                <button
                                    @click="addToCart"
                                    class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    <ShoppingCart class="w-5 h-5" />
                                    Add to Cart
                                </button>
                                <button
                                    @click="saveToWishlist"
                                    class="flex items-center justify-center gap-2 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >
                                    <Heart class="w-5 h-5" />
                                    Wishlist
                                </button>
                                <button
                                    @click="shareProduct"
                                    class="flex items-center justify-center gap-2 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                >
                                    <Share2 class="w-5 h-5" />
                                    Share
                                </button>
                            </div>
                        </div>
                        
                        <!-- Out of Stock Message -->
                        <div v-else class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-red-800 dark:text-red-400 font-medium">This product is currently out of stock.</p>
                        </div>
                        
                        <!-- Features -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <Truck class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">Free Shipping</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">On orders over $50</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <Shield class="w-5 h-5 text-green-600 dark:text-green-400" />
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">Secure Payment</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">100% secure checkout</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <RotateCcw class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">Easy Returns</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">30-day return policy</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <section v-if="relatedProducts.length > 0" class="py-16 bg-gray-50 dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Related Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="relatedProduct in relatedProducts" 
                        :key="relatedProduct.id"
                        class="group cursor-pointer"
                    >
                        <Link :href="`/products/${relatedProduct.slug}`">
                            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform group-hover:scale-105 border border-gray-200 dark:border-gray-700">
                                <div class="relative overflow-hidden rounded-t-xl">
                                    <div v-if="relatedProduct.image" class="h-48 bg-gray-100 dark:bg-gray-700">
                                        <img 
                                            :src="`/upload/products/${relatedProduct.image}`" 
                                            :alt="relatedProduct.name"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        />
                                    </div>
                                    <div v-else class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                        <Package class="w-16 h-16 text-gray-400" />
                                    </div>
                                    
                                    <!-- Featured Badge -->
                                    <div v-if="relatedProduct.is_featured" class="absolute top-3 left-3">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                            <Star class="w-3 h-3 mr-1" />
                                            Featured
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">{{ relatedProduct.category.name }}</div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">{{ relatedProduct.name }}</h3>
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <div v-if="relatedProduct.sale_price" class="flex items-center gap-2">
                                                <span class="text-lg font-bold text-red-600 dark:text-red-400">{{ formatPrice(relatedProduct.sale_price) }}</span>
                                                <span class="text-sm text-gray-500 line-through">{{ formatPrice(relatedProduct.price) }}</span>
                                            </div>
                                            <div v-else>
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ formatPrice(relatedProduct.price) }}</span>
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

        <!-- Similar Products -->
        <section v-if="similarProducts.length > 0" class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Similar Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="similarProduct in similarProducts" 
                        :key="similarProduct.id"
                        class="group cursor-pointer"
                    >
                        <Link :href="`/products/${similarProduct.slug}`">
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform group-hover:scale-105 border border-gray-200 dark:border-gray-700">
                                <div class="relative overflow-hidden rounded-t-xl">
                                    <div v-if="similarProduct.image" class="h-48 bg-gray-100 dark:bg-gray-700">
                                        <img 
                                            :src="`/upload/products/${similarProduct.image}`" 
                                            :alt="similarProduct.name"
                                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        />
                                    </div>
                                    <div v-else class="h-48 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 flex items-center justify-center">
                                        <Package class="w-16 h-16 text-gray-400" />
                                    </div>
                                    
                                    <!-- Sale Badge -->
                                    <div v-if="similarProduct.sale_price" class="absolute top-3 right-3">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                            Sale
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="p-4">
                                    <div class="text-sm text-blue-600 dark:text-blue-400 mb-1">{{ similarProduct.category.name }}</div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">{{ similarProduct.name }}</h3>
                                    
                                    <div class="flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <div v-if="similarProduct.sale_price" class="flex items-center gap-2">
                                                <span class="text-lg font-bold text-red-600 dark:text-red-400">{{ formatPrice(similarProduct.sale_price) }}</span>
                                                <span class="text-sm text-gray-500 line-through">{{ formatPrice(similarProduct.price) }}</span>
                                            </div>
                                            <div v-else>
                                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ formatPrice(similarProduct.price) }}</span>
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
