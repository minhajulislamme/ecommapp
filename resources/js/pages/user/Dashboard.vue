<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { User as UserIcon, Settings, Activity, Mail, AlertTriangle, CheckCircle } from 'lucide-vue-next';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { send } from '@/routes/verification';

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
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, {{ props.user.name }}!</h1>
                    <p class="text-gray-600 dark:text-gray-400">Here's what's happening with your account</p>
                </div>
                <div class="flex items-center gap-2">
                    <UserIcon class="h-8 w-8 text-green-500" />
                    <span class="text-sm font-semibold text-green-500">USER</span>
                </div>
            </div>

            <!-- Email Verification Notice -->
            <div v-if="!props.user.email_verified_at" class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-700 dark:bg-yellow-900/20">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <AlertTriangle class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200">
                            Verify Your Email Address
                        </h3>
                        <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                            Please verify your email address <strong>{{ props.user.email }}</strong> to access all features and secure your account.
                        </p>
                        <div class="mt-4 flex space-x-3">
                            <Link
                                :href="send()"
                                as="button"
                                class="inline-flex items-center rounded-md bg-yellow-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:bg-yellow-500 dark:hover:bg-yellow-600"
                            >
                                <Mail class="mr-2 h-4 w-4" />
                                Send Verification Email
                            </Link>
                            <Link
                                href="/settings/profile"
                                class="inline-flex items-center rounded-md border border-yellow-300 bg-white px-4 py-2 text-sm font-medium text-yellow-700 shadow-sm hover:bg-yellow-50 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:border-yellow-600 dark:bg-yellow-900/10 dark:text-yellow-300 dark:hover:bg-yellow-900/20"
                            >
                                <Settings class="mr-2 h-4 w-4" />
                                Go to Profile Settings
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <CheckCircle class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">
                            Email Verified
                        </h3>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            Your email address was verified on {{ formatDate(props.user.email_verified_at) }}. Your account is fully secured.
                        </p>
                    </div>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Account Information</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ props.user.name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                        <div class="flex items-center space-x-2">
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ props.user.email }}</p>
                            <span v-if="props.user.email_verified_at" class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                <CheckCircle class="mr-1 h-3 w-3" />
                                Verified
                            </span>
                            <span v-else class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                <AlertTriangle class="mr-1 h-3 w-3" />
                                Unverified
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</label>
                        <p class="text-lg font-semibold capitalize text-gray-900 dark:text-white">{{ props.user.role }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Status</label>
                        <p v-if="props.user.email_verified_at" class="text-lg font-semibold text-green-600 dark:text-green-400">
                            Verified on {{ formatDate(props.user.email_verified_at) }}
                        </p>
                        <p v-else class="text-lg font-semibold text-yellow-600 dark:text-yellow-400">
                            Pending verification
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
                                {{ formatDate(props.user.created_at) }}
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
