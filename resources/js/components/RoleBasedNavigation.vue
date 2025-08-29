<script setup lang="ts">
import { type NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { 
    LayoutGrid, 
    Users, 
    Shield, 
    Settings, 
    Database, 
    UserCheck,
    Home,
    Activity
} from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);

const navigationItems = computed((): NavItem[] => {
    if (!user.value) return [];

    const baseItems: NavItem[] = [];

    // Super Admin Navigation
    if (user.value.role === 'superadmin') {
        return [
            {
                title: 'Dashboard',
                href: '/superadmin/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'User Management',
                href: '/superadmin/users',
                icon: Users,
            },
            {
                title: 'System Settings',
                href: '/superadmin/settings',
                icon: Settings,
            },
            {
                title: 'Database',
                href: '/superadmin/database',
                icon: Database,
            },
        ];
    }

    // Admin Navigation
    if (user.value.role === 'admin') {
        return [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'User Management',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'Reports',
                href: '/admin/reports',
                icon: Activity,
            },
        ];
    }

    // Regular User Navigation
    return [
        {
            title: 'Dashboard',
            href: '/user/dashboard',
            icon: Home,
        },
        {
            title: 'Profile',
            href: '/settings/profile',
            icon: UserCheck,
        },
    ];
});

defineExpose({ navigationItems });
</script>

<template>
    <div>
        <!-- This component provides computed navigation items based on user role -->
        <slot :items="navigationItems" />
    </div>
</template>
