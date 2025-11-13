<script setup lang="ts">
import { Button } from '@/components/ui/button';

defineProps<{
    loading?: boolean;
    loadingText?: string;
    type?: 'submit' | 'button' | 'reset';
}>();
</script>

<template>
    <Button
        :type="type || 'submit'"
        class="w-full"
        :disabled="loading"
    >
        <span v-if="!loading">
            <slot />
        </span>
        <span v-else class="flex items-center gap-2">
            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loadingText || 'Loading...' }}
        </span>
    </Button>
</template>
