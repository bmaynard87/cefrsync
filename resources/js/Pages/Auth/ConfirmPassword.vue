<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/components/AuthLayout.vue';
import FormField from '@/components/FormField.vue';
import LoadingButton from '@/components/LoadingButton.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Confirm Password" />

    <AuthLayout
        title="Confirm password"
        subtitle="This is a secure area. Please confirm your password before continuing."
    >
        <form @submit.prevent="submit" class="space-y-5">
            <FormField
                id="password"
                label="Password"
                type="password"
                v-model="form.password"
                :error="form.errors.password"
                placeholder="••••••••"
                required
                autofocus
                autocomplete="current-password"
            />

            <LoadingButton
                :loading="form.processing"
                loading-text="Confirming..."
            >
                Confirm
            </LoadingButton>
        </form>
    </AuthLayout>
</template>
