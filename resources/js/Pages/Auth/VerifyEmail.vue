<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Alert, AlertDescription } from '@/components/ui/alert';
import AuthLayout from '@/components/AuthLayout.vue';
import LoadingButton from '@/components/LoadingButton.vue';

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
    <Head title="Verify Email" />

    <AuthLayout
        title="Verify your email"
        subtitle="Thanks for signing up! Before getting started, please verify your email address by clicking the link we sent you. Didn't receive it? We can send another."
    >
        <Alert v-if="verificationLinkSent" class="mb-6 border-green-200 bg-green-50">
            <AlertDescription class="text-sm text-green-800">
                A new verification link has been sent to your email address.
            </AlertDescription>
        </Alert>

        <form @submit.prevent="submit" class="space-y-4">
            <LoadingButton
                :loading="form.processing"
                loading-text="Sending..."
            >
                Resend verification email
            </LoadingButton>

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
    </AuthLayout>
</template>
