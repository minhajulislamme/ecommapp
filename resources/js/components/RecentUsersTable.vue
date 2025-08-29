<script setup lang="ts">
import { User, Shield, Settings } from 'lucide-vue-next';

interface Props {
    users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        is_active: boolean;
        created_at: string;
    }>;
    showRole?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showRole: false,
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const getRoleIcon = (role: string) => {
    switch (role) {
        case 'superadmin':
            return Shield;
        case 'admin':
            return Settings;
        default:
            return User;
    }
};

const getRoleColor = (role: string) => {
    switch (role) {
        case 'superadmin':
            return 'text-red-600 dark:text-red-400';
        case 'admin':
            return 'text-blue-600 dark:text-blue-400';
        default:
            return 'text-green-600 dark:text-green-400';
    }
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">User</th>
                    <th v-if="props.showRole" class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Role</th>
                    <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th class="pb-3 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Joined</th>
                </tr>
            </thead>
            <tbody class="space-y-3">
                <tr v-for="user in users" :key="user.id" class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                            </div>
                        </div>
                    </td>
                    <td v-if="props.showRole" class="py-3">
                        <div class="flex items-center gap-2">
                            <component :is="getRoleIcon(user.role)" class="h-4 w-4" :class="getRoleColor(user.role)" />
                            <span class="text-sm font-medium capitalize" :class="getRoleColor(user.role)">
                                {{ user.role }}
                            </span>
                        </div>
                    </td>
                    <td class="py-3">
                        <span
                            :class="user.is_active 
                                ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' 
                                : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400'"
                            class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                        >
                            {{ user.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="py-3">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(user.created_at) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
