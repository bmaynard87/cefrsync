<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title: string;
    subtitle?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-12">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo/Header -->
            <div class="flex flex-col items-center text-center">
                <Link :href="route('home')">
                    <AppLogo size="lg" />
                </Link>
                <p class="mt-2 text-sm text-gray-600">Language learning companion</p>
            </div>

            <!-- Main Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ title }}</h2>
                    <p v-if="subtitle" class="mt-1 text-sm text-gray-600">{{ subtitle }}</p>
                </div>

                <slot />
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-500">
                © {{ new Date().getFullYear() }} CefrSync. All rights reserved.
            </p>
        </div>
    </div>
</template>
