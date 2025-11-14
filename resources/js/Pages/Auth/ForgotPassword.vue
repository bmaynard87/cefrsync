<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Alert, AlertDescription } from '@/components/ui/alert';
import AuthLayout from '@/components/AuthLayout.vue';
import FormField from '@/components/FormField.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { useRecaptcha } from '@/composables/useRecaptcha';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
    recaptcha_token: '',
});

const { executeRecaptcha, error: recaptchaError } = useRecaptcha();

const submit = async () => {
    try {
        // Execute reCAPTCHA before submitting
        const token = await executeRecaptcha('forgot_password');
        form.recaptcha_token = token;
        
        form.post(route('password.email'));
    } catch (err) {
        console.error('reCAPTCHA error:', err);
        // You might want to show an error message to the user
    }
};
</script>

<template>
    <Head title="Forgot Password" />

    <AuthLayout
        title="Reset password"
        subtitle="No problem! Enter your email address and we'll send you a link to reset your password."
    >
        <Alert v-if="status" class="mb-6 border-green-200 bg-green-50">
            <AlertDescription class="text-sm text-green-800">
                {{ status }}
            </AlertDescription>
        </Alert>

        <form @submit.prevent="submit" class="space-y-5">
            <FormField
                id="email"
                label="Email"
                type="email"
                v-model="form.email"
                :error="form.errors.email"
                placeholder="you@example.com"
                required
                autofocus
                autocomplete="username"
            />

            <LoadingButton
                :loading="form.processing"
                loading-text="Sending..."
            >
                Send reset link
            </LoadingButton>
        </form>

        <div class="mt-6 text-center">
            <Link
                :href="route('login')"
                class="text-sm font-medium text-gray-900 hover:text-gray-700 hover:underline"
            >
                ← Back to sign in
            </Link>
        </div>
    </AuthLayout>
</template>
