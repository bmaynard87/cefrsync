<script setup lang="ts">
import Spinner from '@/components/ui/spinner/Spinner.vue';
import TypewriterText from '@/components/ui/TypewriterText.vue';

interface Props {
    content: string;
    role: 'user' | 'assistant';
    timestamp: string;
    translation?: string | null;
    isAnalyzing?: boolean;
    disableTypewriter?: boolean;
}

withDefaults(defineProps<Props>(), {
    disableTypewriter: false,
    translation: null,
});
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
                <TypewriterText
                    v-if="role === 'assistant' && !disableTypewriter"
                    :text="translation || content"
                    :speed="30"
                />
                <span v-else>{{ translation || content }}</span>
            </p>
            <div
                class="mt-2 flex items-center gap-2 text-xs"
                :class="role === 'user' ? 'text-blue-100' : 'text-gray-500'"
            >
                <span>{{ timestamp }}</span>
                <span v-if="isAnalyzing" data-test="analyzing-indicator" class="flex items-center gap-1">
                    <Spinner class="h-3 w-3" />
                    Analyzing...
                </span>
            </div>
        </div>
    </div>
</template>
