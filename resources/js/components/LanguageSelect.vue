<script setup lang="ts">
import { Label } from '@/components/ui/label';
import { computed } from 'vue';
import { useVModel } from '@vueuse/core';

const props = defineProps<{
    id: string;
    label: string;
    modelValue: string;
    error?: string;
    required?: boolean;
    excludeValue?: string;
    options: Array<{ value: string; label: string }>;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const value = useVModel(props, 'modelValue', emit);

const filteredOptions = computed(() => {
    if (!props.excludeValue) return props.options;
    return props.options.filter(opt => opt.value !== props.excludeValue);
});
</script>

<template>
    <div class="space-y-2">
        <Label :for="id">{{ label }}</Label>
        <select
            :id="id"
            v-model="value"
            :required="required"
            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            :class="{ 'border-red-500': error }"
        >
            <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
            <option v-for="option in filteredOptions" :key="option.value" :value="option.value">
                {{ option.label }}
            </option>
        </select>
        <p v-if="error" class="text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>
