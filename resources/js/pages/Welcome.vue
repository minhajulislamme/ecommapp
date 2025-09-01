<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TopNavigation from '@/components/TopNavigation.vue';
import UnderDevelopment from '@/components/UnderDevelopment.vue';
import BottomNavigation from '@/components/BottomNavigation.vue';
</script>

<template>
    <Head title="EcommApp - Coming Soon">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    
    <div class="min-h-screen">
        <!-- Top Navigation Component -->
        <TopNavigation />

        <!-- Main Content with top padding to account for fixed header -->
        <div class="pt-16">
            <!-- Under Development Component -->
            <UnderDevelopment />
        </div>

        <!-- Bottom Navigation Component -->
        <BottomNavigation />
    </div>
</template>
