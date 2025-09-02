<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from '@/composables/useToast';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { 
    Bell, 
    CheckCircle, 
    AlertTriangle, 
    AlertCircle, 
    Info, 
    Mail, 
    Users, 
    UserPlus, 
    UserMinus, 
    LogIn, 
    LogOut,
    Smartphone
} from 'lucide-vue-next';

const { toast, isMobile, clearAllToasts } = useToast();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notification Demo',
        href: '/admin/notification-demo',
    },
];

// Demo functions for different types of notifications
const showSuccessToast = () => {
    toast.success('Operation completed successfully!', 'Success');
};

const showErrorToast = () => {
    toast.error('Something went wrong. Please try again.', 'Error');
};

const showWarningToast = () => {
    toast.warning('This action requires your attention.', 'Warning');
};

const showInfoToast = () => {
    toast.info('Here is some useful information for you.', 'Information');
};

// Mobile-specific toast demonstrations
const showMobileLongMessage = () => {
    toast.success('This is a longer notification message designed to test how well the mobile interface handles text wrapping and readability on smaller screens.', 'Mobile Test');
};

const showMobileError = () => {
    toast.error('User "Minhaz" has been deactivated successfully! This action cannot be undone.', 'Account Deactivated');
};

const showMobileWarning = () => {
    toast.warning('Your session will expire in 5 minutes. Please save your work.', 'Session Warning');
};

const testMobileTouch = () => {
    toast.info('Tap the X button to test mobile touch targets', 'Touch Test');
};

const clearAll = () => {
    clearAllToasts();
};

const showMultipleToasts = () => {
    toast.success('First notification', 'Success 1');
    setTimeout(() => toast.warning('Second notification', 'Warning 2'), 500);
    setTimeout(() => toast.info('Third notification', 'Info 3'), 1000);
    setTimeout(() => toast.error('Fourth notification', 'Error 4'), 1500);
};

// User operation demonstrations
const simulateUserCreated = () => {
    toast.success('User John Doe has been created successfully!', 'User Created');
};

const simulateUserUpdated = () => {
    toast.success('User profile has been updated successfully!', 'User Updated');
};

const simulateUserDeleted = () => {
    toast.success('User has been deleted successfully!', 'User Deleted');
};

const simulateUserActivated = () => {
    toast.success('User account has been activated successfully!', 'Account Activated');
};

const simulateUserDeactivated = () => {
    toast.warning('User account has been deactivated.', 'Account Deactivated');
};

// Authentication demonstrations
const simulateLogin = () => {
    toast.success('Welcome back! You have been logged in successfully.', 'Login Successful');
};

const simulateLogout = () => {
    toast.info('You have been logged out successfully. See you again!', 'Logout');
};

const simulateRegister = () => {
    toast.success('Welcome! Your account has been created. Please verify your email.', 'Registration Successful');
};

// Email demonstrations
const simulateEmailSent = () => {
    toast.success('Email has been sent successfully!', 'Email Sent');
};

const simulateEmailFailed = () => {
    toast.error('Failed to send email. Please check your settings.', 'Email Failed');
};

const simulateWelcomeEmail = () => {
    toast.success('Welcome email sent to new user successfully!', 'Welcome Email Sent');
};

const simulatePasswordResetEmail = () => {
    toast.info('Password reset email has been sent to the user.', 'Password Reset Email');
};

// Server action simulations (these would actually make requests)
const sendTestEmail = () => {
    router.post('/admin/send-test-email', {}, {
        onSuccess: () => {
            // Toast will be shown automatically via flash message
        },
        onError: () => {
            // Toast will be shown automatically via flash message  
        }
    });
};

const simulateServerError = () => {
    // This simulates a server error response with flash message
    toast.error('Server error occurred. Please contact support.', 'Server Error');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Toast Notification Demo" />
        
        <div class="space-y-6 p-6">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    Toast Notification Demo
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Test different types of toast notifications in your application
                </p>
            </div>

            <!-- Basic Toast Types -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Bell class="h-5 w-5" />
                        Basic Toast Types
                    </CardTitle>
                    <CardDescription>
                        Test the four main types of toast notifications
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Button @click="showSuccessToast" class="flex items-center gap-2">
                        <CheckCircle class="h-4 w-4" />
                        Success Toast
                    </Button>
                    <Button @click="showErrorToast" variant="destructive" class="flex items-center gap-2">
                        <AlertCircle class="h-4 w-4" />
                        Error Toast
                    </Button>
                    <Button @click="showWarningToast" variant="outline" class="flex items-center gap-2">
                        <AlertTriangle class="h-4 w-4" />
                        Warning Toast
                    </Button>
                    <Button @click="showInfoToast" variant="secondary" class="flex items-center gap-2">
                        <Info class="h-4 w-4" />
                        Info Toast
                    </Button>
                </CardContent>
            </Card>

            <!-- Mobile Optimization Tests -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Smartphone class="h-5 w-5" />
                        Mobile Optimization Tests
                        <span v-if="isMobile()" class="ml-2 px-2 py-1 text-xs bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                            Mobile Detected
                        </span>
                    </CardTitle>
                    <CardDescription>
                        Test mobile-specific notification features including responsive design, touch targets, and duration optimization
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Button @click="showMobileLongMessage" class="flex items-center gap-2">
                        <Smartphone class="h-4 w-4" />
                        Long Message Test
                    </Button>
                    <Button @click="showMobileError" variant="destructive" class="flex items-center gap-2">
                        <AlertCircle class="h-4 w-4" />
                        Mobile Error Test
                    </Button>
                    <Button @click="testMobileTouch" variant="outline" class="flex items-center gap-2">
                        <CheckCircle class="h-4 w-4" />
                        Touch Target Test
                    </Button>
                    <Button @click="showMultipleToasts" variant="secondary" class="flex items-center gap-2">
                        <Bell class="h-4 w-4" />
                        Multiple Toasts
                    </Button>
                    <Button @click="clearAll" variant="outline" class="flex items-center gap-2 text-red-600 hover:text-red-700">
                        <AlertTriangle class="h-4 w-4" />
                        Clear All
                    </Button>
                </CardContent>
            </Card>

            <!-- User Operations -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Users class="h-5 w-5" />
                        User Management Operations
                    </CardTitle>
                    <CardDescription>
                        Toast notifications for user CRUD operations
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Button @click="simulateUserCreated" class="flex items-center gap-2">
                        <UserPlus class="h-4 w-4" />
                        User Created
                    </Button>
                    <Button @click="simulateUserUpdated" variant="outline" class="flex items-center gap-2">
                        <Users class="h-4 w-4" />
                        User Updated
                    </Button>
                    <Button @click="simulateUserDeleted" variant="destructive" class="flex items-center gap-2">
                        <UserMinus class="h-4 w-4" />
                        User Deleted
                    </Button>
                    <Button @click="simulateUserActivated" class="flex items-center gap-2">
                        <CheckCircle class="h-4 w-4" />
                        User Activated
                    </Button>
                    <Button @click="simulateUserDeactivated" variant="outline" class="flex items-center gap-2">
                        <AlertTriangle class="h-4 w-4" />
                        User Deactivated
                    </Button>
                </CardContent>
            </Card>

            <!-- Authentication Operations -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <LogIn class="h-5 w-5" />
                        Authentication Operations
                    </CardTitle>
                    <CardDescription>
                        Toast notifications for login, logout, and registration
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <Button @click="simulateLogin" class="flex items-center gap-2">
                        <LogIn class="h-4 w-4" />
                        Login Success
                    </Button>
                    <Button @click="simulateLogout" variant="outline" class="flex items-center gap-2">
                        <LogOut class="h-4 w-4" />
                        Logout
                    </Button>
                    <Button @click="simulateRegister" class="flex items-center gap-2">
                        <UserPlus class="h-4 w-4" />
                        Registration
                    </Button>
                </CardContent>
            </Card>

            <!-- Email Operations -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Mail class="h-5 w-5" />
                        Email Operations
                    </CardTitle>
                    <CardDescription>
                        Toast notifications for email sending operations
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Button @click="simulateEmailSent" class="flex items-center gap-2">
                        <Mail class="h-4 w-4" />
                        Email Sent
                    </Button>
                    <Button @click="simulateEmailFailed" variant="destructive" class="flex items-center gap-2">
                        <AlertCircle class="h-4 w-4" />
                        Email Failed
                    </Button>
                    <Button @click="simulateWelcomeEmail" class="flex items-center gap-2">
                        <CheckCircle class="h-4 w-4" />
                        Welcome Email
                    </Button>
                    <Button @click="simulatePasswordResetEmail" variant="outline" class="flex items-center gap-2">
                        <Info class="h-4 w-4" />
                        Password Reset
                    </Button>
                </CardContent>
            </Card>

            <!-- Server Actions -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5" />
                        Server Actions (Real)
                    </CardTitle>
                    <CardDescription>
                        These buttons make actual server requests and show toasts via Laravel flash messages
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Button @click="sendTestEmail" class="flex items-center gap-2">
                        <Mail class="h-4 w-4" />
                        Send Real Test Email
                    </Button>
                    <Button @click="simulateServerError" variant="destructive" class="flex items-center gap-2">
                        <AlertCircle class="h-4 w-4" />
                        Simulate Server Error
                    </Button>
                </CardContent>
            </Card>

            <!-- Instructions -->
            <Card>
                <CardHeader>
                    <CardTitle>How to Use Toast Notifications</CardTitle>
                </CardHeader>
                <CardContent class="prose dark:prose-invert max-w-none">
                    <h3>Client-Side Usage:</h3>
                    <pre class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg"><code>import { useToast } from '@/composables/useToast';

const { toast } = useToast();

// Show different types of toasts
toast.success('Success message', 'Success Title');
toast.error('Error message', 'Error Title');
toast.warning('Warning message', 'Warning Title');
toast.info('Info message', 'Info Title');

// Custom duration (optional)
toast.success('Custom message', 'Title', 3000);</code></pre>

                    <h3>Server-Side Usage (Laravel):</h3>
                    <pre class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg"><code>// In your controllers
return redirect()->back()
    ->with('success', 'Operation completed successfully!');

return redirect()->back()
    ->with('error', 'Something went wrong!');

return redirect()->back()
    ->with('warning', 'Warning message!');

return redirect()->back()
    ->with('info', 'Information message!');</code></pre>

                    <h3>Mobile Optimizations:</h3>
                    <ul>
                        <li><strong>Responsive Positioning:</strong> Toasts automatically adjust position for mobile screens</li>
                        <li><strong>Touch-Friendly Targets:</strong> Larger close buttons for better touch interaction</li>
                        <li><strong>Smart Duration:</strong> Longer duration on mobile for better readability</li>
                        <li><strong>Safe Area Support:</strong> Respects device safe areas (notches, home indicators)</li>
                        <li><strong>Enhanced Visibility:</strong> Better contrast and backdrop blur for mobile</li>
                        <li><strong>Optimized Animations:</strong> Smooth animations optimized for mobile performance</li>
                    </ul>

                    <h3>Automatic Flash Message Handling:</h3>
                    <p>The application automatically converts Laravel flash messages to toast notifications. No additional frontend code needed!</p>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
