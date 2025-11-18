<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import SendIcon from '@/components/Icons/SendIcon.vue';
import AiDisclaimer from '@/components/Chat/AiDisclaimer.vue';

interface Props {
    modelValue: string;
    disabled: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    send: [];
}>();

const textareaRef = ref<HTMLTextAreaElement | null>(null);

// Responsive placeholder text
const placeholderText = computed(() => {
    if (window.innerWidth < 640) {
        return 'Type your message...';
    }
    return 'Type your message here... (Shift+Enter for new line)';
});

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

// Watch for when typing finishes (disabled becomes false) and refocus
watch(() => props.disabled, (newDisabled, oldDisabled) => {
    if (oldDisabled && !newDisabled) {
        // Just finished typing, refocus
        setTimeout(() => {
            textareaRef.value?.focus();
        }, 100);
    }
});

// Auto-focus on mount
onMounted(() => {
    textareaRef.value?.focus();
});

// Expose focus method to parent
const focus = () => {
    textareaRef.value?.focus();
};

defineExpose({
    focus,
});
</script>

<template>
    <div class="border-t border-gray-200 bg-white px-3 py-2 sm:px-4 sm:py-4">
        <div class="mx-auto max-w-3xl">
            <div class="relative flex items-end gap-2">
                <textarea ref="textareaRef" :value="modelValue" @input="handleInput" @keydown="handleKeydown"
                    :disabled="disabled" :placeholder="placeholderText" rows="1"
                    class="max-h-32 min-h-[44px] flex-1 resize-none rounded-xl border border-gray-300 px-3 py-2.5 pr-12 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50 sm:px-4 sm:py-3"></textarea>
                <button data-test="send-button" @click="handleSend" :disabled="isSendDisabled()"
                    class="absolute bottom-2 right-2 flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white transition-all hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <SendIcon />
                </button>
            </div>
            <AiDisclaimer />
        </div>
    </div>
</template>
