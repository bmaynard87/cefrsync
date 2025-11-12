<script setup lang="ts">
interface Props {
    content: string;
    role: 'user' | 'assistant';
    timestamp: string;
    isAnalyzing?: boolean;
}

defineProps<Props>();
</script>

<template>
    <div
        data-test="message-container"
        class="flex gap-4"
        :class="role === 'user' ? 'flex-row-reverse' : 'flex-row'"
    >
        <!-- Avatar -->
        <div class="flex-shrink-0">
            <div
                data-test="message-avatar"
                class="flex h-10 w-10 items-center justify-center rounded-full text-white"
                :class="
                    role === 'user'
                        ? 'bg-blue-600'
                        : 'bg-gradient-to-br from-purple-600 to-blue-500'
                "
            >
                <span class="text-sm font-semibold">{{ role === 'user' ? 'You' : 'AI' }}</span>
            </div>
        </div>

        <!-- Message Content -->
        <div
            data-test="message-content"
            class="max-w-[70%] rounded-2xl px-4 py-3"
            :class="
                role === 'user'
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-900 shadow-sm ring-1 ring-gray-200'
            "
        >
            <p class="whitespace-pre-wrap break-words text-sm leading-relaxed">
                {{ content }}
            </p>
            <div
                class="mt-2 flex items-center gap-2 text-xs"
                :class="role === 'user' ? 'text-blue-100' : 'text-gray-500'"
            >
                <span>{{ timestamp }}</span>
                <span v-if="isAnalyzing" data-test="analyzing-indicator" class="flex items-center gap-1">
                    <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                    Analyzing...
                </span>
            </div>
        </div>
    </div>
</template>
