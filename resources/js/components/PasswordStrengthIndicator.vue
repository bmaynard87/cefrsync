<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    password: string;
}>();

interface PasswordRequirement {
    label: string;
    met: boolean;
}

const requirements = computed<PasswordRequirement[]>(() => [
    {
        label: 'At least 8 characters',
        met: props.password.length >= 8,
    },
    {
        label: 'Contains uppercase letter',
        met: /[A-Z]/.test(props.password),
    },
    {
        label: 'Contains lowercase letter',
        met: /[a-z]/.test(props.password),
    },
    {
        label: 'Contains number',
        met: /[0-9]/.test(props.password),
    },
    {
        label: 'Contains special character',
        met: /[^A-Za-z0-9]/.test(props.password),
    },
]);

const strength = computed(() => {
    const metCount = requirements.value.filter(r => r.met).length;
    if (metCount === 0) return { label: '', percent: 0, color: 'bg-gray-200' };
    if (metCount <= 2) return { label: 'Weak', percent: 33, color: 'bg-red-500' };
    if (metCount <= 4) return { label: 'Good', percent: 66, color: 'bg-yellow-500' };
    return { label: 'Strong', percent: 100, color: 'bg-green-500' };
});

const allRequirementsMet = computed(() => 
    requirements.value.every(r => r.met)
);
</script>

<template>
    <div v-if="password" class="space-y-3">
        <!-- Strength bar -->
        <div class="space-y-1">
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-600">Password strength</span>
                <span 
                    class="font-medium"
                    :class="{
                        'text-red-600': strength.label === 'Weak',
                        'text-yellow-600': strength.label === 'Good',
                        'text-green-600': strength.label === 'Strong',
                    }"
                >
                    {{ strength.label }}
                </span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div 
                    class="h-full transition-all duration-300"
                    :class="strength.color"
                    :style="{ width: `${strength.percent}%` }"
                />
            </div>
        </div>

        <!-- Requirements checklist -->
        <div class="space-y-1">
            <div
                v-for="(req, index) in requirements"
                :key="index"
                class="flex items-center gap-2 text-xs"
            >
                <svg
                    class="h-4 w-4 flex-shrink-0"
                    :class="req.met ? 'text-green-600' : 'text-gray-400'"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path
                        v-if="req.met"
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"
                    />
                    <path
                        v-else
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"
                    />
                </svg>
                <span :class="req.met ? 'text-gray-700' : 'text-gray-500'">
                    {{ req.label }}
                </span>
            </div>
        </div>
    </div>
</template>
