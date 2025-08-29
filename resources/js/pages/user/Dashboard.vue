<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head } from '@inertiajs/vue3';
import { User as UserIcon, Settings, Activity } from 'lucide-vue-next';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';

interface Props {
    user: User;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/user/dashboard',
    },
];

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, {{ user.name }}!</h1>
                    <p class="text-gray-600 dark:text-gray-400">Here's what's happening with your account</p>
                </div>
                <div class="flex items-center gap-2">
                    <UserIcon class="h-8 w-8 text-green-500" />
                    <span class="text-sm font-semibold text-green-500">USER</span>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Account Information</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ user.email }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</label>
                        <p class="text-lg font-semibold capitalize text-gray-900 dark:text-white">{{ user.role }}</p>
                    </div>
                    <div v-if="user.email_verified_at">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verified</label>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400">
                            {{ formatDate(user.email_verified_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
                    <div class="space-y-3">
                        <a
                            href="/settings/profile"
                            class="flex items-center gap-3 rounded-lg bg-blue-50 p-3 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30"
                        >
                            <Settings class="h-5 w-5" />
                            Edit Profile
                        </a>
                        <a
                            href="/settings/password"
                            class="flex items-center gap-3 rounded-lg bg-gray-50 p-3 text-gray-700 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            <UserIcon class="h-5 w-5" />
                            Change Password
                        </a>
                    </div>
                </div>

                <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Account Status</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Account Status</span>
                            <div class="flex items-center gap-2">
                                <Activity class="h-4 w-4 text-green-500" />
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">Active</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Member Since</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatDate(user.created_at) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="relative min-h-[40vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <PlaceholderPattern />
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">Welcome to your dashboard</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">More features coming soon!</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
