<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Users, UserCheck, Shield, Database, Eye, EyeOff, AlertCircle, Save } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    created_at: string;
    email_verified_at: string | null;
}

interface Props {
    user: User;
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

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const { toast } = useToast();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
    is_active: props.user.is_active,
});

const submit = () => {
    form.put(`/superadmin/users/${props.user.id}`, {
        onSuccess: () => {
            toast.success('User updated successfully!');
            // Don't reset the form on success, just clear password fields
            form.password = '';
            form.password_confirmation = '';
        },
        onError: () => {
            toast.error('Failed to update user. Please check the form and try again.');
        },
    });
};

const getRoleIcon = (role: string) => {
    switch (role) {
        case 'superadmin':
            return Shield;
        case 'admin':
            return Database;
        default:
            return Users;
    }
};

const getRoleColor = (role: string) => {
    switch (role) {
        case 'superadmin':
            return 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
        case 'admin':
            return 'text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800';
        default:
            return 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Edit User - Super Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto rounded-xl p-4 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Edit User</h1>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Update user information and settings</p>
                </div>
                <div class="flex items-center gap-2">
                    <Shield class="h-8 w-8 text-red-500" />
                    <span class="text-sm font-semibold text-red-500">SUPER ADMIN</span>
                </div>
            </div>

            <div class="max-w-4xl mx-auto w-full grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- User Info Card -->
                <div class="lg:col-span-1">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <UserCheck class="h-5 w-5" />
                                User Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex flex-col items-center text-center">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 mb-3">
                                    <span class="text-xl font-medium text-gray-600 dark:text-gray-300">
                                        {{ props.user.name.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ props.user.name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ props.user.email }}</p>
                            </div>
                            
                            <div class="space-y-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Role</dt>
                                    <dd class="mt-1">
                                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium" :class="getRoleColor(props.user.role)">
                                            <component :is="getRoleIcon(props.user.role)" class="h-4 w-4" />
                                            {{ props.user.role.charAt(0).toUpperCase() + props.user.role.slice(1) }}
                                        </div>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium"
                                              :class="props.user.is_active 
                                                  ? 'text-green-700 bg-green-50 dark:text-green-400 dark:bg-green-900/20' 
                                                  : 'text-red-700 bg-red-50 dark:text-red-400 dark:bg-red-900/20'">
                                            <div class="h-2 w-2 rounded-full" 
                                                 :class="props.user.is_active ? 'bg-green-400' : 'bg-red-400'"></div>
                                            {{ props.user.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-medium"
                                              :class="props.user.email_verified_at 
                                                  ? 'text-green-700 bg-green-50 dark:text-green-400 dark:bg-green-900/20' 
                                                  : 'text-yellow-700 bg-yellow-50 dark:text-yellow-400 dark:bg-yellow-900/20'">
                                            {{ props.user.email_verified_at ? 'Verified' : 'Unverified' }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Member Since</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                        {{ formatDate(props.user.created_at) }}
                                    </dd>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Edit Form -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Save class="h-5 w-5" />
                                Edit User Information
                            </CardTitle>
                            <CardDescription>
                                Update the user's details below. Leave password fields empty to keep the current password.
                            </CardDescription>
                        </CardHeader>

                        <form @submit.prevent="submit">
                            <CardContent class="space-y-6">
                                <!-- Name -->
                                <div class="space-y-2">
                                    <Label for="name">Full Name</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Enter user's full name"
                                        :class="form.errors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="space-y-2">
                                    <Label for="email">Email Address</Label>
                                    <Input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        placeholder="Enter user's email address"
                                        :class="form.errors.email ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                        required
                                    />
                                    <div v-if="form.errors.email" class="text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.email }}
                                    </div>
                                </div>

                                <!-- Role -->
                                <div class="space-y-2">
                                    <Label for="role">User Role</Label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <label 
                                            v-for="role in ['user', 'admin', 'superadmin']" 
                                            :key="role"
                                            class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none"
                                            :class="[
                                                form.role === role 
                                                    ? getRoleColor(role) 
                                                    : 'border-gray-300 bg-white hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:hover:bg-gray-700'
                                            ]"
                                        >
                                            <input
                                                type="radio"
                                                :value="role"
                                                v-model="form.role"
                                                class="sr-only"
                                            />
                                            <div class="flex w-full items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="text-sm">
                                                        <div class="flex items-center gap-2">
                                                            <component :is="getRoleIcon(role)" class="h-4 w-4" />
                                                            <span class="font-medium capitalize">{{ role }}</span>
                                                        </div>
                                                        <p class="text-xs mt-1 opacity-75">
                                                            <span v-if="role === 'user'">Standard user access</span>
                                                            <span v-else-if="role === 'admin'">Administrative privileges</span>
                                                            <span v-else>Full system access</span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div
                                                    v-if="form.role === role"
                                                    class="shrink-0 text-white"
                                                >
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div v-if="form.errors.role" class="text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.role }}
                                    </div>
                                </div>

                                <!-- Password -->
                                <div class="space-y-2">
                                    <Label for="password">New Password (Optional)</Label>
                                    <div class="relative">
                                        <Input
                                            id="password"
                                            v-model="form.password"
                                            :type="showPassword ? 'text' : 'password'"
                                            placeholder="Leave empty to keep current password"
                                            :class="form.errors.password ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                        />
                                        <button
                                            type="button"
                                            @click="showPassword = !showPassword"
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                        >
                                            <component :is="showPassword ? EyeOff : Eye" class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <div v-if="form.errors.password" class="text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.password }}
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Leave empty to keep the current password. If changed, must be at least 8 characters long.
                                    </p>
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-2" v-show="form.password">
                                    <Label for="password_confirmation">Confirm New Password</Label>
                                    <div class="relative">
                                        <Input
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            :type="showPasswordConfirmation ? 'text' : 'password'"
                                            placeholder="Confirm new password"
                                            :class="form.errors.password_confirmation ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                        />
                                        <button
                                            type="button"
                                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                        >
                                            <component :is="showPasswordConfirmation ? EyeOff : Eye" class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <div v-if="form.errors.password_confirmation" class="text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.password_confirmation }}
                                    </div>
                                </div>

                                <!-- Account Status -->
                                <div class="space-y-2">
                                    <Label>Account Status</Label>
                                    <div class="flex items-center space-x-2">
                                        <input
                                            id="is_active"
                                            type="checkbox"
                                            v-model="form.is_active"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                                        />
                                        <Label for="is_active" class="text-sm font-normal">
                                            Active (user can log in and access the system)
                                        </Label>
                                    </div>
                                    <div v-if="form.errors.is_active" class="text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.is_active }}
                                    </div>
                                </div>

                                <!-- Warning for Superadmin -->
                                <div v-if="form.role === 'superadmin'" class="rounded-md border border-orange-200 bg-orange-50 p-4 dark:border-orange-800 dark:bg-orange-900/20">
                                    <div class="flex">
                                        <AlertCircle class="h-4 w-4 text-orange-600 dark:text-orange-400 mr-2 mt-0.5" />
                                        <div class="text-sm text-orange-800 dark:text-orange-200">
                                            <strong>Warning:</strong> This user will have Super Admin privileges with full access to all system features and data.
                                        </div>
                                    </div>
                                </div>
                            </CardContent>

                            <CardFooter class="flex flex-col sm:flex-row gap-3 sm:justify-between">
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="router.visit('/superadmin/users')"
                                    class="w-full sm:w-auto"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full sm:w-auto"
                                >
                                    <Save class="h-4 w-4 mr-2" />
                                    {{ form.processing ? 'Updating...' : 'Update User' }}
                                </Button>
                            </CardFooter>
                        </form>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
