<script setup lang="ts">
import { ref, nextTick } from 'vue';
import AppShell from '@/components/AppShell.vue';
import ChatSidebar from '@/components/Chat/ChatSidebar.vue';
import ChatHeader from '@/components/Chat/ChatHeader.vue';
import ChatMessage from '@/components/Chat/ChatMessage.vue';
import ChatInput from '@/components/Chat/ChatInput.vue';
import TypingIndicator from '@/components/Chat/TypingIndicator.vue';

interface Message {
    id: number;
    content: string;
    role: 'user' | 'assistant';
    timestamp: string;
    isAnalyzing?: boolean;
}

interface Chat {
    id: number;
    title: string;
    timestamp: string;
    isActive: boolean;
}

const chats = ref<Chat[]>([
    { id: 1, title: 'Daily Routine Practice', timestamp: 'Today at 2:30 PM', isActive: true },
    { id: 2, title: 'Travel Vocabulary', timestamp: 'Yesterday at 4:15 PM', isActive: false },
    { id: 3, title: 'Food & Restaurants', timestamp: '2 days ago', isActive: false },
]);

const activeChat = ref<number>(1);

const messages = ref<Message[]>([
    {
        id: 1,
        content: "Hello! I'm your language exchange partner. Let's practice together! What would you like to talk about today?",
        role: 'assistant',
        timestamp: '2:30 PM',
    },
]);

const inputMessage = ref('');
const isTyping = ref(false);
const chatContainer = ref<HTMLElement | null>(null);

const nativeLanguage = ref('Spanish');
const targetLanguage = ref('English');
const proficiencyLevel = ref('B1');
const proficiencyLabel = ref('Intermediate');

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const sendMessage = async () => {
    if (!inputMessage.value.trim()) return;

    const userMessage: Message = {
        id: Date.now(),
        content: inputMessage.value,
        role: 'user',
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    };

    messages.value.push(userMessage);
    inputMessage.value = '';
    isTyping.value = true;

    await scrollToBottom();

    setTimeout(async () => {
        const assistantMessage: Message = {
            id: Date.now() + 1,
            content: "That's a great topic! Let me help you practice...",
            role: 'assistant',
            timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        };
        messages.value.push(assistantMessage);
        isTyping.value = false;
        await scrollToBottom();
    }, 1500);
};

const handleNewChat = () => {
    console.log('Creating new chat...');
};

const handleSelectChat = (chatId: number) => {
    activeChat.value = chatId;
    console.log('Selected chat:', chatId);
};

const handleSettings = () => {
    console.log('Opening settings...');
};

const handleEditParams = () => {
    console.log('Editing parameters...');
};
</script>

<template>
    <AppShell>
        <div class="flex h-screen bg-gray-50">
            <ChatSidebar :chats="chats" :active-chat="activeChat" @new-chat="handleNewChat" @select-chat="handleSelectChat" />

            <div class="flex flex-1 flex-col">
                <ChatHeader
                    :native-language="nativeLanguage"
                    :target-language="targetLanguage"
                    :proficiency-level="proficiencyLevel"
                    :proficiency-label="proficiencyLabel"
                    @settings="handleSettings"
                    @edit-params="handleEditParams"
                />

                <div ref="chatContainer" class="flex-1 overflow-y-auto px-4 py-6">
                    <div class="mx-auto max-w-3xl space-y-6">
                        <ChatMessage
                            v-for="message in messages"
                            :key="message.id"
                            :content="message.content"
                            :role="message.role"
                            :timestamp="message.timestamp"
                            :is-analyzing="message.isAnalyzing"
                        />

                        <TypingIndicator v-if="isTyping" />
                    </div>
                </div>

                <ChatInput v-model="inputMessage" :disabled="isTyping" @send="sendMessage" />
            </div>
        </div>
    </AppShell>
</template>
