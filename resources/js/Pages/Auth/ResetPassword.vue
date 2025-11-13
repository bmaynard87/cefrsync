<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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

    <div class="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-12">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo/Header -->
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900">CefrSync</h1>
                <p class="mt-2 text-sm text-gray-600">Language learning companion</p>
            </div>

            <!-- Main Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Set new password</h2>
                    <p class="mt-1 text-sm text-gray-600">Choose a strong password for your account</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>
                    
                    <!-- TODO: Implement a strong password only system -->
                    <div class="space-y-2">
                        <Label for="password">New Password</Label>
                        <Input
                            id="password"
                            type="password"
                            v-model="form.password"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirm Password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            :class="{ 'border-red-500': form.errors.password_confirmation }"
                        />
                        <p v-if="form.errors.password_confirmation" class="text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">Reset password</span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Resetting...
                        </span>
                    </Button>
                </form>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-500">
                © {{ new Date().getFullYear() }} CefrSync. All rights reserved.
            </p>
        </div>
    </div>
</template>
