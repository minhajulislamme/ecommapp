<template>
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur-lg border-b border-gray-200/50 dark:border-gray-700/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <Link :href="home.url()" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <Icon name="shopping-bag" class="w-5 h-5 text-white" />
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            EcommApp
                        </span>
                    </Link>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <Link 
                        v-for="item in navigationItems" 
                        :key="item.name"
                        :href="item.href"
                        class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200 flex items-center space-x-2 hover:bg-gray-100 dark:hover:bg-gray-800"
                        :class="{ 
                            'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20': isActive(item.href),
                            'hover:scale-105': !isActive(item.href)
                        }"
                    >
                        <Icon :name="item.icon" class="w-4 h-4" />
                        <span>{{ item.name }}</span>
                    </Link>
                </div>

                <!-- Desktop Actions -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Search -->
                    <div class="relative group">
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search products..."
                            class="w-64 xl:w-80 pl-10 pr-4 py-2.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all text-sm placeholder-gray-500 dark:placeholder-gray-400"
                            @focus="showSearchResults = true"
                            @blur="hideSearchResults"
                        />
                        <Icon name="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
                        
                        <!-- Search Results Dropdown -->
                        <div 
                            v-if="showSearchResults && searchQuery"
                            class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-2 max-h-64 overflow-y-auto z-50"
                        >
                            <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                                Search results will appear here...
                            </div>
                        </div>
                    </div>

                    <!-- Cart -->
                    <button class="relative p-2.5 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 group">
                        <Icon name="shopping-cart" class="w-6 h-6 group-hover:scale-110 transition-transform" />
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs rounded-full flex items-center justify-center font-semibold shadow-sm">
                            {{ cartItemCount }}
                        </span>
                    </button>

                    <!-- Authentication Section -->
                    <div v-if="isAuthenticated" class="relative" ref="userMenuRef">
                        <!-- Authenticated User Menu -->
                        <button 
                            @click="toggleUserMenu"
                            class="flex items-center space-x-2 p-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                        >
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <Icon name="user" class="w-4 h-4 text-white" />
                            </div>
                            <span class="text-sm font-medium">{{ page.props.auth.user?.name || 'User' }}</span>
                            <Icon name="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showUserMenu }" />
                        </button>

                        <!-- User Dropdown -->
                        <div 
                            v-if="showUserMenu"
                            class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 py-2 z-50 backdrop-blur-lg"
                        >
                            <!-- User Info Header -->
                            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ page.props.auth.user?.name || 'User' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ page.props.auth.user?.email || '' }}</p>
                            </div>
                            
                            <!-- Menu Items -->
                            <div class="py-1">
                                <Link 
                                    v-for="item in userMenuItems" 
                                    :key="item.name"
                                    :href="item.href"
                                    @click="showUserMenu = false"
                                    class="flex items-center space-x-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors group"
                                >
                                    <Icon :name="item.icon" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                    <span>{{ item.name }}</span>
                                </Link>
                            </div>
                            
                            <!-- Logout Button -->
                            <div class="border-t border-gray-200 dark:border-gray-700 py-1">
                                <button 
                                    @click="handleLogout"
                                    class="flex items-center space-x-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors w-full text-left group"
                                >
                                    <Icon name="log-out" class="w-4 h-4 group-hover:scale-110 transition-transform" />
                                    <span>Sign Out</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Guest Authentication Links -->
                    <div v-else class="flex items-center space-x-3">
                        <Link
                            :href="login.url()"
                            class="inline-flex items-center px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 hover:scale-105"
                        >
                            <Icon name="shield-check" class="w-4 h-4 mr-2" />
                            Log in
                        </Link>
                        <Link
                            :href="register.url()"
                            class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white text-sm font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                        >
                            <Icon name="user-plus" class="w-4 h-4 mr-2" />
                            Register
                        </Link>
                    </div>

                    <!-- Dark Mode Toggle -->
                    <button 
                        @click="toggleDarkMode"
                        class="p-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <Icon :name="isDarkMode ? 'sun' : 'moon'" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Mobile Menu Button and Theme Toggle -->
                <div class="md:hidden flex items-center space-x-2">
                    <!-- Mobile Dark Mode Toggle -->
                    <button 
                        @click="toggleDarkMode"
                        class="p-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                        title="Toggle theme"
                    >
                        <Icon :name="isDarkMode ? 'sun' : 'moon'" class="w-5 h-5" />
                    </button>
                    
                    <!-- Mobile Menu Button -->
                    <button 
                        @click="toggleMobileMenu"
                        class="p-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                        title="Menu"
                    >
                        <Icon :name="showMobileMenu ? 'x' : 'menu'" class="w-6 h-6" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div 
            v-if="showMobileMenu"
            class="md:hidden bg-white/95 dark:bg-gray-900/95 backdrop-blur-lg border-t border-gray-200/50 dark:border-gray-700/50"
        >
            <div class="px-4 py-6 space-y-4">
                <!-- Mobile Search -->
                <div class="relative group">
                    <input 
                        v-model="mobileSearchQuery"
                        type="text" 
                        placeholder="Search products..."
                        class="w-full pl-10 pr-4 py-3 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all placeholder-gray-500 dark:placeholder-gray-400"
                    />
                    <Icon name="search" class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
                </div>

                <!-- Mobile Navigation Items -->
                <div class="space-y-1">
                    <Link 
                        v-for="item in navigationItems" 
                        :key="item.name"
                        :href="item.href"
                        @click="showMobileMenu = false"
                        class="flex items-center space-x-3 px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-all group"
                        :class="{ 
                            'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': isActive(item.href),
                            'hover:translate-x-1': !isActive(item.href)
                        }"
                    >
                        <Icon :name="item.icon" class="w-5 h-5 group-hover:scale-110 transition-transform" />
                        <span class="font-medium">{{ item.name }}</span>
                    </Link>
                </div>

                <!-- Mobile User Actions -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                    <!-- Authenticated User Menu Items -->
                    <template v-if="isAuthenticated">
                        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <Icon name="user" class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ page.props.auth.user?.name || 'User' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ page.props.auth.user?.email || '' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <Link 
                            v-for="item in userMenuItems" 
                            :key="item.name"
                            :href="item.href"
                            @click="showMobileMenu = false"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
                        >
                            <Icon :name="item.icon" class="w-5 h-5" />
                            <span class="font-medium">{{ item.name }}</span>
                        </Link>
                        
                        <button 
                            @click="handleLogout"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors w-full text-left"
                        >
                            <Icon name="log-out" class="w-5 h-5" />
                            <span class="font-medium">Sign Out</span>
                        </button>
                    </template>
                    
                    <!-- Guest Authentication Links -->
                    <template v-else>
                        <Link
                            :href="login.url()"
                            @click="showMobileMenu = false"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors"
                        >
                            <Icon name="shield-check" class="w-5 h-5" />
                            <span class="font-medium">Log in</span>
                        </Link>
                        <Link
                            :href="register.url()"
                            @click="showMobileMenu = false"
                            class="flex items-center space-x-3 px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl transition-all transform hover:scale-105"
                        >
                            <Icon name="user-plus" class="w-5 h-5" />
                            <span class="font-medium">Register</span>
                        </Link>
                    </template>
                    
                    <!-- Theme and Settings Section -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <Icon :name="isDarkMode ? 'moon' : 'sun'" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ isDarkMode ? 'Dark Mode' : 'Light Mode' }}
                                </span>
                            </div>
                            <button 
                                @click="toggleDarkMode"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                :class="isDarkMode ? 'bg-blue-600' : 'bg-gray-200'"
                            >
                                <span
                                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                    :class="isDarkMode ? 'translate-x-6' : 'translate-x-1'"
                                />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { login, register, dashboard, logout, home } from '@/routes';
import Icon from '@/components/Icon.vue';

const page = usePage();

// Check if user is authenticated
const isAuthenticated = computed(() => page.props.auth?.user !== null);

// Reactive state
const searchQuery = ref('');
const mobileSearchQuery = ref('');
const showSearchResults = ref(false);
const showUserMenu = ref(false);
const showMobileMenu = ref(false);
const isDarkMode = ref(false);
const cartItemCount = ref(3);

// Template refs
const userMenuRef = ref<HTMLElement>();

// Navigation items
const navigationItems = [
    { name: 'Home', href: home.url(), icon: 'home' },
    { name: 'Products', href: '/products', icon: 'shopping-bag' },
    { name: 'Categories', href: '/categories', icon: 'grid' },
    { name: 'Deals', href: '/deals', icon: 'tag' },
    { name: 'About', href: '/about', icon: 'info' },
    { name: 'Contact', href: '/contact', icon: 'mail' }
];

const userMenuItems = [
    { name: 'Dashboard', icon: 'layout-dashboard', href: dashboard.url() },
    { name: 'Profile', icon: 'user', href: '/profile' },
    { name: 'Orders', icon: 'package', href: '/orders' },
    { name: 'Wishlist', icon: 'heart', href: '/wishlist' },
    { name: 'Settings', icon: 'settings', href: '/settings' }
];

// Methods
const toggleUserMenu = () => {
    showUserMenu.value = !showUserMenu.value;
};

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value;
};

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    document.documentElement.classList.toggle('dark', isDarkMode.value);
    localStorage.setItem('darkMode', isDarkMode.value.toString());
};

const hideSearchResults = () => {
    setTimeout(() => {
        showSearchResults.value = false;
    }, 200);
};

const handleLogout = () => {
    router.post(logout.url(), {}, {
        onFinish: () => {
            showUserMenu.value = false;
        }
    });
};

const isActive = (href: string): boolean => {
    if (href === '/') {
        return page.url === '/';
    }
    return page.url.startsWith(href);
};

const handleClickOutside = (event: Event) => {
    if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
        showUserMenu.value = false;
    }
};

// Lifecycle
onMounted(() => {
    // Check for saved dark mode preference
    const savedDarkMode = localStorage.getItem('darkMode');
    if (savedDarkMode) {
        isDarkMode.value = savedDarkMode === 'true';
        document.documentElement.classList.toggle('dark', isDarkMode.value);
    }

    // Add click outside listener
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Custom scrollbar for search results */
::-webkit-scrollbar {
    width: 4px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 2px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}
</style>
