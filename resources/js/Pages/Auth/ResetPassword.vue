<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/components/AuthLayout.vue';
import FormField from '@/components/FormField.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import PasswordStrengthIndicator from '@/components/PasswordStrengthIndicator.vue';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password" />

    <AuthLayout
        title="Set new password"
        subtitle="Choose a strong password for your account"
    >
        <form @submit.prevent="submit" class="space-y-5">
            <FormField
                id="email"
                label="Email"
                type="email"
                v-model="form.email"
                :error="form.errors.email"
                required
                autofocus
                autocomplete="username"
            />

            <div class="space-y-2">
                <FormField
                    id="password"
                    label="New Password"
                    type="password"
                    v-model="form.password"
                    :error="form.errors.password"
                    placeholder="••••••••"
                    required
                    autocomplete="new-password"
                />
                <PasswordStrengthIndicator :password="form.password" />
            </div>

            <FormField
                id="password_confirmation"
                label="Confirm Password"
                type="password"
                v-model="form.password_confirmation"
                :error="form.errors.password_confirmation"
                placeholder="••••••••"
                required
                autocomplete="new-password"
            />

            <LoadingButton
                :loading="form.processing"
                loading-text="Resetting..."
            >
                Reset password
            </LoadingButton>
        </form>
    </AuthLayout>
</template>
