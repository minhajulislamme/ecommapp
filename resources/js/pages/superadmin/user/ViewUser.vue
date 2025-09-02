<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Users, UserCheck, Shield, Database, Mail, Calendar, Clock, Edit, ArrowLeft, Trash2, Phone, MapPin, User } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    is_active: boolean;
    phone: string | null;
    address: string | null;
    profile_image: string | null;
    created_at: string;
    updated_at: string;
    email_verified_at: string | null;
}

interface Props {
    user: User;
}

const props = defineProps<Props>();

const isDeleting = ref(false);
const isTogglingStatus = ref(false);
const isRemovingImage = ref(false);
const showDeleteDialog = ref(false);

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

const getUserInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
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
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDateShort = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const deleteUser = async () => {
    showDeleteDialog.value = true;
};

const confirmDeleteUser = async () => {
    isDeleting.value = true;
    try {
        router.delete(`/superadmin/users/${props.user.id}`, {
            onSuccess: () => {
                // User deleted successfully
            },
            onError: () => {
                // Failed to delete user
            },
            onFinish: () => {
                isDeleting.value = false;
            }
        });
    } catch (error) {
        isDeleting.value = false;
        // An error occurred while deleting the user
    }
};

const toggleUserStatus = async () => {
    isTogglingStatus.value = true;
    const action = props.user.is_active ? 'deactivate' : 'activate';
    
    try {
        router.patch(`/superadmin/users/${props.user.id}/toggle-status`, {}, {
            onSuccess: () => {
                // User status toggled successfully
            },
            onError: () => {
                // Failed to toggle user status
            },
            onFinish: () => {
                isTogglingStatus.value = false;
            }
        });
    } catch (error) {
        isTogglingStatus.value = false;
        // An error occurred while trying to toggle the user status
    }
};

const removeProfileImage = async () => {
    isRemovingImage.value = true;
    
    try {
        router.delete(`/superadmin/users/${props.user.id}/remove-image`, {
            onSuccess: () => {
                // Profile image removed successfully
            },
            onError: () => {
                // Failed to remove profile image
            },
            onFinish: () => {
                isRemovingImage.value = false;
            }
        });
    } catch (error) {
        isRemovingImage.value = false;
        // An error occurred while removing the profile image
    }
};
</script>

<template>
    <Head :title="`View User: ${user.name} - Super Admin`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 sm:gap-6 overflow-x-auto rounded-xl p-4 sm:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <Button 
                            variant="ghost" 
                            size="sm"
                            @click="router.visit('/superadmin/users')"
                            class="flex items-center gap-2 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            <ArrowLeft class="h-4 w-4" />
                            Back to Users
                        </Button>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">User Details</h1>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">View detailed information about this user</p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="flex items-center gap-2">
                        <Shield class="h-8 w-8 text-red-500" />
                        <span class="text-sm font-semibold text-red-500">SUPER ADMIN</span>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="max-w-4xl mx-auto w-full">
                <!-- Main Profile Card -->
                <Card class="mb-6">
                    <CardHeader class="pb-4">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                    <template v-if="user.profile_image">
                                        <img 
                                            :src="`/upload/users/${user.profile_image}`" 
                                            :alt="user.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </template>
                                    <template v-else>
                                        <span class="text-2xl font-medium text-gray-600 dark:text-gray-300">
                                            {{ getUserInitials(user.name) }}
                                        </span>
                                    </template>
                                </div>
                                <div>
                                    <CardTitle class="text-2xl">{{ user.name }}</CardTitle>
                                    <CardDescription class="text-base flex items-center gap-2">
                                        <Mail class="h-4 w-4" />
                                        {{ user.email }}
                                    </CardDescription>
                                    <div class="mt-2">
                                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium" :class="getRoleColor(user.role)">
                                            <component :is="getRoleIcon(user.role)" class="h-4 w-4" />
                                            {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <Button 
                                    @click="router.visit(`/superadmin/users/${user.id}/edit`)"
                                    class="flex items-center gap-2"
                                >
                                    <Edit class="h-4 w-4" />
                                    Edit User
                                </Button>
                                <Button 
                                    variant="destructive"
                                    @click="deleteUser"
                                    :disabled="isDeleting"
                                    class="flex items-center gap-2"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    {{ isDeleting ? 'Deleting...' : 'Delete User' }}
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                </Card>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Account Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <UserCheck class="h-5 w-5" />
                                Account Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white font-medium">{{ user.name }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ user.email }}</dd>
                                </div>
                                
                                <div v-if="user.phone">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</dt>
                                    <dd class="mt-1 flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                        <Phone class="h-4 w-4 text-gray-400" />
                                        {{ user.phone }}
                                    </dd>
                                </div>
                                
                                <div v-if="user.address">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                                    <dd class="mt-1 flex items-start gap-2 text-sm text-gray-900 dark:text-white">
                                        <MapPin class="h-4 w-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                        <span>{{ user.address }}</span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">User Role</dt>
                                    <dd class="mt-1">
                                        <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium" :class="getRoleColor(user.role)">
                                            <component :is="getRoleIcon(user.role)" class="h-4 w-4" />
                                            {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                        </div>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium"
                                              :class="user.is_active 
                                                  ? 'text-green-700 bg-green-50 dark:text-green-400 dark:bg-green-900/20' 
                                                  : 'text-red-700 bg-red-50 dark:text-red-400 dark:bg-red-900/20'">
                                            <div class="h-2 w-2 rounded-full" 
                                                 :class="user.is_active ? 'bg-green-400' : 'bg-red-400'"></div>
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verification</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium"
                                              :class="user.email_verified_at 
                                                  ? 'text-green-700 bg-green-50 dark:text-green-400 dark:bg-green-900/20' 
                                                  : 'text-yellow-700 bg-yellow-50 dark:text-yellow-400 dark:bg-yellow-900/20'">
                                            <Mail class="h-3 w-3" />
                                            {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Activity Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Clock class="h-5 w-5" />
                                Activity Information
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Member Since</dt>
                                    <dd class="mt-1 flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                        <Calendar class="h-4 w-4 text-gray-400" />
                                        {{ formatDate(user.created_at) }}
                                    </dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                    <dd class="mt-1 flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                        <Clock class="h-4 w-4 text-gray-400" />
                                        {{ formatDate(user.updated_at) }}
                                    </dd>
                                </div>
                                
                                <div v-if="user.email_verified_at">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verified On</dt>
                                    <dd class="mt-1 flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                                        <Mail class="h-4 w-4 text-gray-400" />
                                        {{ formatDate(user.email_verified_at) }}
                                    </dd>
                                </div>
                                
                                <!-- Role Permissions -->
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Permissions</dt>
                                    <dd class="mt-2">
                                        <div class="space-y-2">
                                            <div v-if="user.role === 'superadmin'" class="text-sm">
                                                <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                                                    <Shield class="h-4 w-4" />
                                                    <span class="font-medium">Full System Access</span>
                                                </div>
                                                <p class="text-xs text-gray-500 ml-6">Can manage all users, settings, and system configurations</p>
                                            </div>
                                            <div v-else-if="user.role === 'admin'" class="text-sm">
                                                <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400">
                                                    <Database class="h-4 w-4" />
                                                    <span class="font-medium">Administrative Access</span>
                                                </div>
                                                <p class="text-xs text-gray-500 ml-6">Can manage users and basic system settings</p>
                                            </div>
                                            <div v-else class="text-sm">
                                                <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                                                    <Users class="h-4 w-4" />
                                                    <span class="font-medium">Standard User Access</span>
                                                </div>
                                                <p class="text-xs text-gray-500 ml-6">Can access user dashboard and basic features</p>
                                            </div>
                                        </div>
                                    </dd>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Quick Actions -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common actions you can perform for this user</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-3">
                            <Button 
                                variant="outline"
                                @click="router.visit(`/superadmin/users/${user.id}/edit`)"
                                class="flex items-center gap-2"
                            >
                                <Edit class="h-4 w-4" />
                                Edit Profile
                            </Button>
                            
                            <Button 
                                variant="outline"
                                @click="toggleUserStatus"
                                :disabled="isTogglingStatus"
                                class="flex items-center gap-2"
                                :class="user.is_active ? 'text-orange-600 border-orange-300 hover:bg-orange-50' : 'text-green-600 border-green-300 hover:bg-green-50'"
                            >
                                <UserCheck class="h-4 w-4" />
                                {{ isTogglingStatus ? 'Processing...' : (user.is_active ? 'Deactivate Account' : 'Activate Account') }}
                            </Button>

                            <Button 
                                v-if="user.profile_image"
                                variant="outline"
                                @click="removeProfileImage"
                                :disabled="isRemovingImage"
                                class="flex items-center gap-2 text-purple-600 border-purple-300 hover:bg-purple-50"
                            >
                                <Trash2 class="h-4 w-4" />
                                {{ isRemovingImage ? 'Removing...' : 'Remove Image' }}
                            </Button>
                            
                            <Button 
                                variant="outline"
                                @click="deleteUser"
                                :disabled="isDeleting"
                                class="flex items-center gap-2 text-red-600 border-red-300 hover:bg-red-50"
                            >
                                <Trash2 class="h-4 w-4" />
                                {{ isDeleting ? 'Deleting...' : 'Delete User' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>

    <!-- Delete Confirmation Dialog -->
    <ConfirmDialog
        :is-open="showDeleteDialog"
        :title="`Delete ${user.name}?`"
        :description="`Are you sure you want to delete ${user.name}? This user will be permanently removed from the system and this action cannot be undone.`"
        confirm-text="Delete User"
        cancel-text="Cancel"
        variant="destructive"
        @confirm="confirmDeleteUser"
        @close="showDeleteDialog = false"
    />
</template>
