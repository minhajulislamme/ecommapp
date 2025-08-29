<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Users, UserCheck, UserX, Shield, Settings } from 'lucide-vue-next';
import StatCard from '@/components/StatCard.vue';
import RecentUsersTable from '@/components/RecentUsersTable.vue';

interface Props {
    stats: {
        total_users: number;
        active_users: number;
        inactive_users: number;
    };
    recent_users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        is_active: boolean;
        created_at: string;
    }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Admin Dashboard',
        href: '/admin/dashboard',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
                    <p class="text-gray-600 dark:text-gray-400">Manage users and system settings</p>
                </div>
                <div class="flex items-center gap-2">
                    <Settings class="h-8 w-8 text-blue-500" />
                    <span class="text-sm font-semibold text-blue-500">ADMIN</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <StatCard
                    title="Total Users"
                    :value="stats.total_users"
                    :icon="Users"
                    color="blue"
                />
                <StatCard
                    title="Active Users"
                    :value="stats.active_users"
                    :icon="UserCheck"
                    color="green"
                />
                <StatCard
                    title="Inactive Users"
                    :value="stats.inactive_users"
                    :icon="UserX"
                    color="orange"
                />
            </div>

            <!-- Recent Users -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recent Users</h2>
                    <a
                        href="/admin/users"
                        class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >
                        View all →
                    </a>
                </div>
                <RecentUsersTable :users="recent_users" />
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                    <div class="space-y-3">
                        <a
                            href="/admin/users"
                            class="flex items-center gap-3 rounded-lg bg-blue-50 p-3 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30"
                        >
                            <Users class="h-5 w-5" />
                            Manage Users
                        </a>
                    </div>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">User Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Active Rate</span>
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                {{ Math.round((stats.active_users / stats.total_users) * 100) }}%
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Managed</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ stats.total_users }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
