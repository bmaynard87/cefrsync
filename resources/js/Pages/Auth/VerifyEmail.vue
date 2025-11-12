<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <Head title="Email Verification" />

    <div class="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-12">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo/Header -->
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900">CEFRSync</h1>
                <p class="mt-2 text-sm text-gray-600">Language learning companion</p>
            </div>

            <!-- Main Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Verify your email</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Thanks for signing up! Before getting started, please verify your email address by clicking the link we sent you. Didn't receive it? We can send another.
                    </p>
                </div>

                <Alert v-if="verificationLinkSent" class="mb-6 border-green-200 bg-green-50">
                    <AlertDescription class="text-sm text-green-800">
                        A new verification link has been sent to your email address.
                    </AlertDescription>
                </Alert>

                <form @submit.prevent="submit" class="space-y-4">
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">Resend verification email</span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Sending...
                        </span>
                    </Button>

                    <div class="text-center">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="text-sm font-medium text-gray-900 hover:text-gray-700 hover:underline"
                        >
                            Log out
                        </Link>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-500">
                © {{ new Date().getFullYear() }} CEFRSync. All rights reserved.
            </p>
        </div>
    </div>
</template>
