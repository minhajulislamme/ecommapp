<script setup lang="ts">
import TopNavigation from '@/components/TopNavigation.vue';
import BottomNavigation from '@/components/BottomNavigation.vue';
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <!-- Top Navigation Component -->
        <TopNavigation />

        <!-- Main Content with top padding to account for fixed header -->
        <main class="pt-16">
            <slot />
        </main>

        <!-- Bottom Navigation Component -->
        <BottomNavigation />
    </div>
</template>
