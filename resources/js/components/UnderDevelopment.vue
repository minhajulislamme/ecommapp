<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900">
        <!-- Main Content -->
        <div class="flex items-center justify-center min-h-screen px-4 pb-24 sm:pb-20">
            <div class="text-center max-w-2xl mx-auto">
                <!-- Logo/Icon -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full shadow-lg mb-6">
                        <Icon name="code" class="w-12 h-12 text-white" />
                    </div>
                </div>

                <!-- Main Heading -->
                <h1 class="text-4xl md:text-6xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-4">
                    Coming Soon
                </h1>

                <!-- Subheading -->
                <h2 class="text-xl md:text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-6">
                    We're building something amazing!
                </h2>

                <!-- Description -->
                <p class="text-gray-600 dark:text-gray-400 text-lg leading-relaxed mb-8 max-w-lg mx-auto">
                    Our e-commerce platform is currently under development. 
                    We're working hard to bring you the best shopping experience.
                </p>

                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>Development Progress</span>
                        <span>{{ progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                        <div 
                            class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full transition-all duration-1000 ease-out"
                            :style="`width: ${progress}%`"
                        ></div>
                    </div>
                </div>

                <!-- Features Coming Soon -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div 
                        v-for="feature in features" 
                        :key="feature.name"
                        class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl p-6 shadow-lg border border-white/20 dark:border-gray-700/50"
                    >
                        <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg mb-4">
                            <Icon :name="feature.icon" class="w-6 h-6 text-white" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                            {{ feature.name }}
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="space-y-4">
                    <p class="text-gray-600 dark:text-gray-400">
                        Want to be notified when we launch?
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center max-w-md mx-auto">
                        <input 
                            v-model="email"
                            type="email" 
                            placeholder="Enter your email"
                            class="w-full sm:flex-1 px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                        />
                        <button 
                            @click="subscribe"
                            class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                        >
                            Notify Me
                        </button>
                    </div>
                </div>

                <!-- Social Links -->
                <div class="mt-8">
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Follow us for updates</p>
                    <div class="flex justify-center space-x-4">
                        <a 
                            v-for="social in socialLinks" 
                            :key="social.name"
                            href="#"
                            class="inline-flex items-center justify-center w-10 h-10 bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-full shadow-md hover:shadow-lg transform hover:scale-110 transition-all duration-200 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400"
                        >
                            <Icon :name="social.icon" class="w-5 h-5" />
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Animated Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob"></div>
            <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute top-40 left-40 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Icon from '@/components/Icon.vue';

const progress = ref(0);
const email = ref('');

const features = [
    {
        name: 'Product Catalog',
        icon: 'shopping-bag',
        description: 'Browse through thousands of products with advanced filtering and search.'
    },
    {
        name: 'Secure Checkout',
        icon: 'shield-check',
        description: 'Safe and secure payment processing with multiple payment options.'
    },
    {
        name: 'Order Tracking',
        icon: 'truck',
        description: 'Real-time order tracking and delivery notifications.'
    }
];

const socialLinks = [
    { name: 'Facebook', icon: 'facebook' },
    { name: 'Twitter', icon: 'twitter' },
    { name: 'Instagram', icon: 'instagram' },
    { name: 'LinkedIn', icon: 'linkedin' }
];

const subscribe = () => {
    if (email.value) {
        // Here you would typically send the email to your backend
        alert(`Thank you! We'll notify you at ${email.value} when we launch.`);
        email.value = '';
    }
};

onMounted(() => {
    // Animate progress bar
    setTimeout(() => {
        progress.value = 75;
    }, 500);
});
</script>

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

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
