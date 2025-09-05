<script setup lang="ts">
interface Props {
    isActive: boolean;
    activeText?: string;
    inactiveText?: string;
}

const props = withDefaults(defineProps<Props>(), {
    activeText: 'Active',
    inactiveText: 'Inactive',
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
            props.isActive
                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
        ]"
    >
        {{ props.isActive ? props.activeText : props.inactiveText }}
    </span>
</template>
