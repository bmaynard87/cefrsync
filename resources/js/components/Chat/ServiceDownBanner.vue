<script setup lang="ts">
import { AlertCircle } from 'lucide-vue-next';

interface Props {
    isServiceDown: boolean;
}

defineProps<Props>();
</script>

<template>
    <div
        v-if="isServiceDown"
        data-test="service-down-banner"
        class="border-l-4 border-red-200 bg-red-50 p-4"
    >
        <div class="flex items-start gap-3">
            <AlertCircle
                data-test="alert-icon"
                class="h-5 w-5 flex-shrink-0 text-red-600"
            />
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-red-800">
                    Language processing service is currently unavailable
                </h3>
                <p class="mt-1 text-sm text-red-700">
                    Chat functionality is temporarily disabled. Please try again later.
                </p>
            </div>
        </div>
    </div>
</template>
