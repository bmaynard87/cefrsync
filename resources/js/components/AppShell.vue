<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

const isOpen = usePage().props.sidebarOpen;
const isAuthenticated = usePage().props.auth?.user !== undefined;
</script>

<template>
    <div v-if="variant === 'header'" class="flex h-[100dvh] w-full flex-col overflow-hidden"
        :data-authenticated="isAuthenticated || undefined">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen" :data-authenticated="isAuthenticated || undefined">
        <slot />
    </SidebarProvider>
</template>
