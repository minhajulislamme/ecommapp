<script setup lang="ts">
import type { LucideIcon } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    title: string;
    value: number | string;
    icon: LucideIcon;
    color?: 'blue' | 'green' | 'red' | 'purple' | 'orange' | 'emerald';
}

const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
});

const colorClasses = computed(() => {
    const colors = {
        blue: 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
        green: 'bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400',
        red: 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400',
        purple: 'bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
        orange: 'bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400',
        emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
    };
    return colors[props.color];
});
</script>

<template>
    <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ title }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ value }}</p>
            </div>
            <div :class="colorClasses" class="rounded-lg p-2">
                <component :is="icon" class="h-6 w-6" />
            </div>
        </div>
    </div>
</template>
