<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
                    <h2 class="text-2xl font-semibold text-gray-900">Confirm password</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        This is a secure area. Please confirm your password before continuing.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            v-model="form.password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            autofocus
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">Confirm</span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Confirming...
                        </span>
                    </Button>
                </form>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-500">
                © {{ new Date().getFullYear() }} CEFRSync. All rights reserved.
            </p>
        </div>
    </div>
</template>
