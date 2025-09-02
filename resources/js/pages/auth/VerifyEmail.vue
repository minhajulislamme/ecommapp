<script setup lang="ts">
import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { LoaderCircle, Mail, CheckCircle, AlertCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();

const page = usePage();
const user = page.props.auth.user;
</script>

<template>
    <AuthLayout title="Verify your email address" description="We've sent a verification link to your email address. Please check your inbox and click the link to verify your account.">
        <Head title="Email verification" />

        <div class="space-y-6">
            <!-- User email display -->
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-center dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                    <Mail class="h-4 w-4" />
                    <span>Verification email sent to:</span>
                </div>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ user?.email }}</p>
            </div>

            <!-- Status messages -->
            <div v-if="status === 'verification-link-sent'" class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-700 dark:bg-green-900/20">
                <div class="flex items-center space-x-3">
                    <CheckCircle class="h-5 w-5 text-green-600 dark:text-green-400" />
                    <div>
                        <h3 class="font-semibold text-green-800 dark:text-green-200">Email Sent Successfully!</h3>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            A new verification link has been sent to your email address. Please check your inbox (and spam folder).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-700 dark:bg-blue-900/20">
                <div class="flex items-start space-x-3">
                    <AlertCircle class="h-5 w-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                    <div>
                        <h3 class="font-semibold text-blue-800 dark:text-blue-200">What to do next:</h3>
                        <ul class="mt-2 space-y-1 text-sm text-blue-700 dark:text-blue-300">
                            <li>• Check your email inbox for a verification message</li>
                            <li>• Look in your spam or junk folder if you don't see it</li>
                            <li>• Click the verification link in the email</li>
                            <li>• Return to your dashboard once verified</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-4">
                <Form v-bind="EmailVerificationNotificationController.store.form()" class="text-center" v-slot="{ processing }">
                    <Button class="w-full" :disabled="processing">
                        <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Mail v-else class="mr-2 h-4 w-4" />
                        {{ processing ? 'Sending...' : 'Resend Verification Email' }}
                    </Button>
                </Form>

                <div class="flex items-center justify-center space-x-4 text-sm">
                    <TextLink href="/settings/profile" class="text-blue-600 hover:text-blue-500 dark:text-blue-400">
                        Update Email Address
                    </TextLink>
                    <span class="text-gray-400">•</span>
                    <TextLink :href="logout()" as="button" class="text-gray-600 hover:text-gray-500 dark:text-gray-400">
                        Log Out
                    </TextLink>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
