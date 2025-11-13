<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    size?: 'sm' | 'md' | 'lg';
    showText?: boolean;
    textClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
    showText: true,
    textClass: '',
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return { container: 'h-8 w-8', icon: 'h-5 w-5', text: 'text-xl' };
        case 'lg':
            return { container: 'h-12 w-12', icon: 'h-8 w-8', text: 'text-3xl' };
        default:
            return { container: 'h-10 w-10', icon: 'h-6 w-6', text: 'text-2xl' };
    }
});
</script>

<template>
    <div class="flex items-center gap-2">
        <div :class="['flex items-center justify-center rounded-lg bg-blue-600', sizeClasses.container]">
            <svg :class="['text-white', sizeClasses.icon]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
            </svg>
        </div>
        <span v-if="showText" :class="['font-bold text-gray-900', sizeClasses.text, textClass]">CefrSync</span>
    </div>
</template>
