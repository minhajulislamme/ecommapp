<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Plus, 
    Search, 
    Filter, 
    Edit, 
    Trash2, 
    UserCheck, 
    UserX,
    Shield,
    Settings,
    User
} from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        meta: any;
    };
    filters: {
        search?: string;
        role?: string;
    };
    roles: Record<string, string>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Super Admin Dashboard',
        href: '/superadmin/dashboard',
    },
    {
        title: 'User Management',
        href: '/superadmin/users',
    },
];

const searchTerm = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');

const search = () => {
    router.get('/superadmin/users', {
        search: searchTerm.value,
        role: selectedRole.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchTerm.value = '';
    selectedRole.value = '';
    router.get('/superadmin/users', {}, {
        preserveState: true,
        replace: true,
    });
};

const toggleUserStatus = (user: User) => {
    router.patch(`/superadmin/users/${user.id}/toggle-status`, {}, {
        preserveState: true,
    });
};

const deleteUser = (user: User) => {
    if (confirm(`Are you sure you want to delete ${user.name}?`)) {
        router.delete(`/superadmin/users/${user.id}`, {
            preserveState: true,
        });
    }
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
            return 'text-red-600 bg-red-50 dark:text-red-400 dark:bg-red-900/20';
        case 'admin':
            return 'text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/20';
        default:
            return 'text-green-600 bg-green-50 dark:text-green-400 dark:bg-green-900/20';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage all users, admins, and super admins</p>
                </div>
                <Link
                    href="/superadmin/users/create"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    <Plus class="h-4 w-4" />
                    Create User
                </Link>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <input
                                v-model="searchTerm"
                                type="text"
                                placeholder="Search users..."
                                class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                @keyup.enter="search"
                            />
                        </div>
                    </div>
                    <div class="w-48">
                        <select
                            v-model="selectedRole"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Roles</option>
                            <option v-for="(label, value) in roles" :key="value" :value="value">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                    <button
                        @click="search"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                    >
                        <Filter class="h-4 w-4" />
                    </button>
                    <button
                        @click="clearFilters"
                        class="rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">User</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Role</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Created</th>
                                <th class="px-6 py-4 text-left text-sm font-medium text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-700/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                            <span class="font-medium text-gray-600 dark:text-gray-300">
                                                {{ user.name.charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <component :is="getRoleIcon(user.role)" class="h-4 w-4" />
                                        <span
                                            :class="getRoleColor(user.role)"
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize"
                                        >
                                            {{ user.role }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        :class="user.is_active 
                                            ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' 
                                            : 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400'"
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    >
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(user.created_at) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/superadmin/users/${user.id}/edit`"
                                            class="rounded p-1 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="toggleUserStatus(user)"
                                            :class="user.is_active 
                                                ? 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20'
                                                : 'text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20'"
                                            class="rounded p-1"
                                        >
                                            <UserX v-if="user.is_active" class="h-4 w-4" />
                                            <UserCheck v-else class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteUser(user)"
                                            class="rounded p-1 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 dark:border-gray-700 dark:bg-gray-800 sm:px-6">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <!-- Mobile pagination -->
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Showing
                                <span class="font-medium">{{ users.meta.from }}</span>
                                to
                                <span class="font-medium">{{ users.meta.to }}</span>
                                of
                                <span class="font-medium">{{ users.meta.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <!-- Pagination links -->
                                <Link
                                    v-for="link in users.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        link.active
                                            ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                        'relative inline-flex items-center px-4 py-2 text-sm font-medium border',
                                    ]"
                                >
                                    {{ link.label }}
                                </Link>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
