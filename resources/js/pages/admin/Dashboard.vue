<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Users, UserCheck, UserX, Settings, FolderOpen, Tag, Package } from 'lucide-vue-next';
import StatCard from '@/components/StatCard.vue';
import RecentUsersTable from '@/components/RecentUsersTable.vue';

interface Props {
    stats: {
        total_users: number;
        active_users: number;
        inactive_users: number;
        total_products: number;
        active_products: number;
        featured_products: number;
        total_categories: number;
        low_stock_products: number;
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
            <div class="grid auto-rows-min gap-4 md:grid-cols-3 lg:grid-cols-4">
                <!-- User Stats -->
                <StatCard
                    title="Total Users"
                    :value="props.stats.total_users"
                    :icon="Users"
                    color="blue"
                />
                <StatCard
                    title="Active Users"
                    :value="props.stats.active_users"
                    :icon="UserCheck"
                    color="green"
                />
                <StatCard
                    title="Inactive Users"
                    :value="props.stats.inactive_users"
                    :icon="UserX"
                    color="orange"
                />
                
                <!-- Product Stats -->
                <StatCard
                    title="Total Products"
                    :value="props.stats.total_products"
                    :icon="Package"
                    color="purple"
                />
                <StatCard
                    title="Active Products"
                    :value="props.stats.active_products"
                    :icon="Package"
                    color="green"
                />
                <StatCard
                    title="Featured Products"
                    :value="props.stats.featured_products"
                    :icon="Package"
                    color="orange"
                />
                <StatCard
                    title="Categories"
                    :value="props.stats.total_categories"
                    :icon="FolderOpen"
                    color="emerald"
                />
                <StatCard
                    title="Low Stock"
                    :value="props.stats.low_stock_products"
                    :icon="Package"
                    color="red"
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
                <RecentUsersTable :users="props.recent_users" />
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
                        <a
                            href="/admin/categories"
                            class="flex items-center gap-3 rounded-lg bg-green-50 p-3 text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/30"
                        >
                            <FolderOpen class="h-5 w-5" />
                            Manage Categories
                        </a>
                        <a
                            href="/admin/subcategories"
                            class="flex items-center gap-3 rounded-lg bg-purple-50 p-3 text-purple-700 hover:bg-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:hover:bg-purple-900/30"
                        >
                            <Tag class="h-5 w-5" />
                            Manage Sub-Categories
                        </a>
                        <a
                            href="/admin/products/create"
                            class="flex items-center gap-3 rounded-lg bg-orange-50 p-3 text-orange-700 hover:bg-orange-100 dark:bg-orange-900/20 dark:text-orange-400 dark:hover:bg-orange-900/30"
                        >
                            <Package class="h-5 w-5" />
                            Add New Product
                        </a>
                        <a
                            href="/admin/products"
                            class="flex items-center gap-3 rounded-lg bg-blue-50 p-3 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30"
                        >
                            <Package class="h-5 w-5" />
                            Manage Products
                        </a>
                    </div>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">User Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Active Rate</span>
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                {{ Math.round((props.stats.active_users / props.stats.total_users) * 100) }}%
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Managed</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ props.stats.total_users }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
