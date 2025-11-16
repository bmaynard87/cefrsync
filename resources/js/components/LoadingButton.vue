<script setup lang="ts">
import { Button } from '@/components/ui/button';
import Spinner from '@/components/ui/spinner/Spinner.vue';

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
            <Spinner class="h-4 w-4" />
            {{ loadingText || 'Loading...' }}
        </span>
    </Button>
</template>
