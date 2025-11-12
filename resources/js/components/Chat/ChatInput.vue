<script setup lang="ts">
interface Props {
    modelValue: string;
    disabled: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    send: [];
}>();

const handleInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement;
    emit('update:modelValue', target.value);
};

const handleKeydown = (event: KeyboardEvent) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        if (props.modelValue.trim() && !props.disabled) {
            emit('send');
        }
    }
};

const handleSend = () => {
    if (props.modelValue.trim() && !props.disabled) {
        emit('send');
    }
};

const isSendDisabled = () => {
    return !props.modelValue.trim() || props.disabled;
};
</script>

<template>
    <div class="border-t border-gray-200 bg-white px-4 py-4">
        <div class="mx-auto max-w-3xl">
            <div class="relative flex items-end gap-2">
                <textarea
                    :value="modelValue"
                    @input="handleInput"
                    @keydown="handleKeydown"
                    :disabled="disabled"
                    placeholder="Type your message here... (Shift+Enter for new line)"
                    rows="1"
                    class="max-h-32 min-h-[44px] flex-1 resize-none rounded-xl border border-gray-300 px-4 py-3 pr-12 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50"
                ></textarea>
                <button
                    data-test="send-button"
                    @click="handleSend"
                    :disabled="isSendDisabled()"
                    class="absolute bottom-2 right-2 flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white transition-all hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                        ></path>
                    </svg>
                </button>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-gray-500">
                <span>Press Enter to send, Shift+Enter for new line</span>
                <span>AI will analyze your conversation for learning insights</span>
            </div>
        </div>
    </div>
</template>
