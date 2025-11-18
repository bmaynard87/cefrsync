<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';

interface Props {
    text: string;
    speed?: number; // milliseconds per character
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    speed: 30,
    disabled: false,
});

const emit = defineEmits<{
    complete: [];
}>();

const displayedText = ref('');
const textDirection = ref<'ltr' | 'rtl' | 'auto'>('auto');
let typingTimer: ReturnType<typeof setTimeout> | null = null;

/**
 * Detects if text contains RTL characters (Arabic, Hebrew, etc.)
 */
function detectTextDirection(text: string): 'ltr' | 'rtl' | 'auto' {
    // RTL Unicode ranges:
    // Arabic: \u0600-\u06FF, \u0750-\u077F, \u08A0-\u08FF
    // Hebrew: \u0590-\u05FF
    const rtlPattern = /[\u0591-\u07FF\uFB1D-\uFDFD\uFE70-\uFEFC]/;
    
    // LTR patterns (Latin, Cyrillic, Greek, etc.)
    const ltrPattern = /[A-Za-z\u00C0-\u024F\u0400-\u04FF\u0370-\u03FF]/;
    
    const hasRtl = rtlPattern.test(text);
    const hasLtr = ltrPattern.test(text);
    
    // Returns 'auto' for mixed content to let the browser handle direction
    if (hasRtl && hasLtr) {
        return 'auto';
    } else if (hasRtl) {
        return 'rtl';
    } else if (hasLtr) {
        return 'ltr';
    }
    
    return 'auto';
}

function startTyping() {
    if (props.disabled) {
        displayedText.value = props.text;
        emit('complete');
        return;
    }

    displayedText.value = '';
    textDirection.value = detectTextDirection(props.text);
    
    if (!props.text) {
        emit('complete');
        return;
    }

    let currentIndex = 0;

    function typeNextCharacter() {
        if (currentIndex < props.text.length) {
            displayedText.value += props.text[currentIndex];
            currentIndex++;
            
            if (currentIndex < props.text.length) {
                typingTimer = setTimeout(typeNextCharacter, props.speed);
            } else {
                emit('complete');
            }
        }
    }

    // Start with a delay for consistent behavior
    typingTimer = setTimeout(typeNextCharacter, 0);
}

function stopTyping() {
    if (typingTimer) {
        clearTimeout(typingTimer);
        typingTimer = null;
    }
}

watch(() => props.text, () => {
    stopTyping();
    startTyping();
}, { immediate: true });

onBeforeUnmount(() => {
    stopTyping();
});
</script>

<template>
    <span class="relative inline-block">
        <!-- Invisible text to establish size -->
        <span class="invisible" aria-hidden="true" data-test="typewriter-size">{{ text }}</span>
        
        <!-- Visible typing text overlaid -->
        <span 
            class="absolute inset-0" 
            data-test="typewriter-text" 
            :dir="textDirection"
        >{{ displayedText }}</span>
    </span>
</template>
