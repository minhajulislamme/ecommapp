<template>
    <!-- Mobile Bottom Navigation (Hidden on Desktop) -->
    <nav class="fixed bottom-0 left-0 right-0 z-50 md:hidden bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg border-t border-gray-200/50 dark:border-gray-700/50 shadow-lg">
        <div class="grid grid-cols-5 h-16">
            <Link 
                v-for="item in navigationItems" 
                :key="item.name"
                :href="item.href"
                class="flex flex-col items-center justify-center space-y-1 text-xs font-medium transition-all duration-200 relative group"
                :class="isActive(item.href) 
                    ? 'text-blue-600 dark:text-blue-400' 
                    : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400'"
                @click="handleTap(item.name)"
            >
                <!-- Active Indicator -->
                <div 
                    v-if="isActive(item.href)"
                    class="absolute top-0 left-1/2 transform -translate-x-1/2 w-8 h-1 bg-blue-600 dark:bg-blue-400 rounded-full"
                ></div>
                
                <!-- Icon Container -->
                <div class="relative">
                    <div 
                        class="p-2 rounded-xl transition-all duration-200"
                        :class="isActive(item.href) 
                            ? 'bg-blue-50 dark:bg-blue-900/30 scale-110' 
                            : 'group-hover:bg-gray-50 dark:group-hover:bg-gray-800/50'"
                    >
                        <Icon 
                            :name="item.icon" 
                            class="w-5 h-5 transition-transform duration-200"
                            :class="isActive(item.href) ? 'scale-110' : ''"
                        />
                    </div>
                    
                    <!-- Badge for Cart -->
                    <span 
                        v-if="item.name === 'Cart' && cartItemCount > 0"
                        class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold"
                    >
                        {{ cartItemCount > 9 ? '9+' : cartItemCount }}
                    </span>
                    
                    <!-- Badge for Notifications -->
                    <span 
                        v-if="item.name === 'Account' && hasNotifications"
                        class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"
                    ></span>
                </div>
                
                <!-- Label -->
                <span 
                    class="transition-all duration-200"
                    :class="isActive(item.href) ? 'font-semibold' : ''"
                >
                    {{ item.name }}
                </span>
                
                <!-- Ripple Effect -->
                <div 
                    v-if="rippleActive === item.name"
                    class="absolute inset-0 bg-blue-500/20 rounded-xl animate-ping"
                ></div>
            </Link>
        </div>
        
        <!-- Safe Area Bottom Padding for newer iPhones -->
        <div class="h-safe-area-inset-bottom bg-white/95 dark:bg-gray-900/95"></div>
    </nav>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/components/Icon.vue';

const page = usePage();
const rippleActive = ref<string | null>(null);
const cartItemCount = ref(3);
const hasNotifications = ref(true);

const navigationItems = [
    { 
        name: 'Home', 
        href: '/', 
        icon: 'home'
    },
    { 
        name: 'Shop', 
        href: '/products', 
        icon: 'shopping-bag'
    },
    { 
        name: 'Search', 
        href: '/search', 
        icon: 'search'
    },
    { 
        name: 'Cart', 
        href: '/cart', 
        icon: 'shopping-cart'
    },
    { 
        name: 'Account', 
        href: '/profile', 
        icon: 'user'
    }
];

const isActive = (href: string): boolean => {
    if (href === '/') {
        return page.url === '/';
    }
    return page.url.startsWith(href);
};

// Add ripple effect on tap
const handleTap = (itemName: string) => {
    rippleActive.value = itemName;
    setTimeout(() => {
        rippleActive.value = null;
    }, 300);
};
</script>

<style scoped>
/* Safe area handling for devices with bottom notch */
.h-safe-area-inset-bottom {
    height: env(safe-area-inset-bottom, 0px);
}

/* Smooth transitions for all interactive elements */
* {
    -webkit-tap-highlight-color: transparent;
}

/* Custom animation for badges */
@keyframes bounce-in {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.2);
    }
    100% {
        transform: scale(1);
    }
}

.animate-bounce-in {
    animation: bounce-in 0.3s ease-out;
}

/* Hover effects for better interaction feedback */
@media (hover: hover) {
    .group:hover .group-hover\:bg-gray-50 {
        background-color: rgb(249 250 251 / 0.5);
    }
    
    .dark .group:hover .dark\:group-hover\:bg-gray-800\/50 {
        background-color: rgb(31 41 55 / 0.5);
    }
}
</style>
