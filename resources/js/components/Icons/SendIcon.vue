<script setup lang="ts">
import { Send } from 'lucide-vue-next';

defineOptions({
    inheritAttrs: false,
});
</script>

<template>
    <Send
        class="h-5 w-5"
        v-bind="$attrs"
    />
</template>
