<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Users, UserPlus, Shield, Database, Eye, EyeOff, AlertCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'user',
    is_active: true,
});

const submit = () => {
    form.post('/superadmin/users', {
        onSuccess: () => {
            toast.success('User created successfully!');
            form.reset();
        },
        onError: () => {
            toast.error('Failed to create user. Please check the form and try again.');
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
</script>

<template>
    <Head title="Create User - Super Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto rounded-xl p-4 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Create New User</h1>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Add a new user to your application</p>
                </div>
                <div class="flex items-center gap-2">
                    <Shield class="h-8 w-8 text-red-500" />
                    <span class="text-sm font-semibold text-red-500">SUPER ADMIN</span>
                </div>
            </div>

            <!-- Create User Form -->
            <div class="max-w-2xl mx-auto w-full">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <UserPlus class="h-5 w-5" />
                            User Information
                        </CardTitle>
                        <CardDescription>
                            Fill in the details below to create a new user account.
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
                                <Label for="password">Password</Label>
                                <div class="relative">
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        placeholder="Enter password"
                                        :class="form.errors.password ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                        required
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
                                    Password must be at least 8 characters long.
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password</Label>
                                <div class="relative">
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        :type="showPasswordConfirmation ? 'text' : 'password'"
                                        placeholder="Confirm password"
                                        :class="form.errors.password_confirmation ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                                        required
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
                                        Active (user can log in immediately)
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
                                        <strong>Warning:</strong> You are creating a user with Super Admin privileges. This user will have full access to all system features and data.
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
                                <UserPlus class="h-4 w-4 mr-2" />
                                {{ form.processing ? 'Creating...' : 'Create User' }}
                            </Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
