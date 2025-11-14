<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Checkbox } from '@/components/ui/checkbox';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Separator } from '@/components/ui/separator';
import AuthLayout from '@/components/AuthLayout.vue';
import FormField from '@/components/FormField.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { useRecaptcha } from '@/composables/useRecaptcha';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
    recaptcha_token: '',
});

const { executeRecaptcha, error: recaptchaError } = useRecaptcha();

const submit = async () => {
    try {
        // Execute reCAPTCHA before submitting
        const token = await executeRecaptcha('login');
        form.recaptcha_token = token;
        
        form.post(route('login'), {
            onFinish: () => form.reset('password'),
        });
    } catch (err) {
        console.error('reCAPTCHA error:', err);
        // You might want to show an error message to the user
    }
};
</script>

<template>
    <Head title="Log in" />

    <AuthLayout
        title="Welcome back"
        subtitle="Sign in to continue your learning journey"
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

            <FormField
                id="password"
                label="Password"
                type="password"
                v-model="form.password"
                :error="form.errors.password"
                placeholder="••••••••"
                required
                autocomplete="current-password"
            />

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="text-sm text-gray-700">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm font-medium text-gray-900 hover:text-gray-700 hover:underline"
                >
                    Forgot password?
                </Link>
            </div>

            <LoadingButton
                :loading="form.processing"
                loading-text="Signing in..."
            >
                Sign in
            </LoadingButton>
        </form>

        <Separator class="my-6" />

        <p class="text-center text-sm text-gray-600">
            Don't have an account?
            <Link
                :href="route('register')"
                class="font-medium text-gray-900 hover:text-gray-700 hover:underline"
            >
                Sign up
            </Link>
        </p>
    </AuthLayout>
</template>
