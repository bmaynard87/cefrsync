<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useVModel } from '@vueuse/core';

const props = defineProps<{
    id: string;
    label: string;
    type?: string;
    modelValue: string | boolean;
    error?: string;
    placeholder?: string;
    required?: boolean;
    autofocus?: boolean;
    autocomplete?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const value = useVModel(props, 'modelValue', emit);
</script>

<template>
    <div class="space-y-2">
        <Label :for="id">{{ label }}</Label>
        <Input
            :id="id"
            :type="type || 'text'"
            v-model="value"
            :placeholder="placeholder"
            :required="required"
            :autofocus="autofocus"
            :autocomplete="autocomplete"
            :class="{ 'border-red-500': error }"
        />
        <p v-if="error" class="text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>
