<script setup lang="ts">
import { ref, nextTick, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppShell from '@/components/AppShell.vue';
import ChatSidebar from '@/components/Chat/ChatSidebar.vue';
import ChatHeader from '@/components/Chat/ChatHeader.vue';
import ChatMessage from '@/components/Chat/ChatMessage.vue';
import ChatInput from '@/components/Chat/ChatInput.vue';
import TypingIndicator from '@/components/Chat/TypingIndicator.vue';

// Use Ziggy's route() helper
declare global {
    function route(name: string, params?: any): string;
}

interface Message {
    id: number;
    content: string;
    sender_type: 'user' | 'assistant';
    created_at: string;
    isAnalyzing?: boolean;
}

interface ChatHistory {
    id: number;
    title: string;
    last_message_at: string;
    native_language: string;
    target_language: string;
}

interface UserSettings {
    native_language: string;
    target_language: string;
    proficiency_level: string;
}

interface Props {
    chatHistory: ChatHistory[];
    userSettings: UserSettings;
}

const props = defineProps<Props>();

const chats = ref<ChatHistory[]>(props.chatHistory);
const activeChat = ref<number | null>(chats.value[0]?.id || null);
const messages = ref<Message[]>([]);
const inputMessage = ref('');
const isTyping = ref(false);
const chatContainer = ref<HTMLElement | null>(null);
const chatInputRef = ref<{ focus: () => void } | null>(null);

// Use user settings from props
const nativeLanguage = ref(props.userSettings.native_language);
const targetLanguage = ref(props.userSettings.target_language);
const proficiencyLevel = ref(props.userSettings.proficiency_level);

const proficiencyLabel = computed(() => {
    const levels: Record<string, string> = {
        'A1': 'Beginner',
        'A2': 'Elementary',
        'B1': 'Intermediate',
        'B2': 'Upper Intermediate',
        'C1': 'Advanced',
        'C2': 'Proficient',
    };
    return levels[proficiencyLevel.value] || proficiencyLevel.value;
});

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const loadMessages = async (chatId: number) => {
    try {
        const response = await fetch(route('language-chat.messages', { chatSession: chatId }));
        const data = await response.json();
        messages.value = data.messages;
        await scrollToBottom();
    } catch (error) {
        console.error('Error loading messages:', error);
    }
};

const sendMessage = async () => {
    if (!inputMessage.value.trim()) {
        return;
    }
    
    if (!activeChat.value) {
        await handleNewChat();
        // Wait a moment for the new chat to be created
        await new Promise(resolve => setTimeout(resolve, 100));
        if (!activeChat.value) {
            console.error('Failed to create chat session');
            return;
        }
    }

    const userMessageContent = inputMessage.value;
    const tempUserMessage: Message = {
        id: Date.now(),
        content: userMessageContent,
        sender_type: 'user',
        created_at: new Date().toISOString(),
    };

    messages.value.push(tempUserMessage);
    inputMessage.value = '';
    isTyping.value = true;

    await scrollToBottom();

    // Refocus the input after clearing
    await nextTick();
    chatInputRef.value?.focus();

    try {
        const url = route('language-chat.message', { chatSession: activeChat.value });
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ message: userMessageContent }),
        });

        const data = await response.json();
        
        // Replace temp message with real one from server
        const userIndex = messages.value.findIndex(m => m.id === tempUserMessage.id);
        if (userIndex !== -1) {
            messages.value[userIndex] = {
                ...data.user_message,
                sender_type: 'user' as const,
            };
        }

        // Add AI response
        messages.value.push({
            ...data.ai_response,
            sender_type: 'assistant' as const,
        });

        isTyping.value = false;
        await scrollToBottom();

        // Update chat's last_message_at and title if generated
        const chatIndex = chats.value.findIndex(c => c.id === activeChat.value);
        if (chatIndex !== -1) {
            chats.value[chatIndex].last_message_at = new Date().toISOString();
            if (data.new_title) {
                chats.value[chatIndex].title = data.new_title;
            }
        }
    } catch (error) {
        console.error('Error sending message:', error);
        isTyping.value = false;
    }
};

const handleNewChat = async () => {
    try {
        const response = await fetch(route('language-chat.create'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                native_language: nativeLanguage.value,
                target_language: targetLanguage.value,
                proficiency_level: proficiencyLevel.value,
            }),
        });

        const newSession = await response.json();
        
        // Add new session to chat list
        chats.value.unshift({
            id: newSession.id,
            title: 'New Conversation',
            last_message_at: newSession.created_at,
            native_language: newSession.native_language,
            target_language: newSession.target_language,
        });

        // Switch to new chat
        handleSelectChat(newSession.id);
    } catch (error) {
        console.error('Error creating new chat:', error);
    }
};

const handleSelectChat = async (chatId: number) => {
    activeChat.value = chatId;
    await loadMessages(chatId);
};

const handleDeleteChat = async (chatId: number) => {
    try {
        const response = await fetch(route('language-chat.destroy', { chatSession: chatId }), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        if (response.ok) {
            // Remove chat from list
            chats.value = chats.value.filter(chat => chat.id !== chatId);

            // If we deleted the active chat, switch to another one or clear messages
            if (activeChat.value === chatId) {
                if (chats.value.length > 0) {
                    handleSelectChat(chats.value[0].id);
                } else {
                    activeChat.value = null;
                    messages.value = [];
                }
            }
        }
    } catch (error) {
        console.error('Error deleting chat:', error);
    }
};

const handleUpdateTitle = async (chatId: number, newTitle: string) => {
    try {
        const response = await fetch(route('language-chat.update-title', { chatSession: chatId }), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ title: newTitle }),
        });

        if (response.ok) {
            // Update chat title in list
            const chatIndex = chats.value.findIndex(chat => chat.id === chatId);
            if (chatIndex !== -1) {
                chats.value[chatIndex].title = newTitle;
            }
        }
    } catch (error) {
        console.error('Error updating chat title:', error);
    }
};

const handleSettings = () => {
    router.visit('/profile');
};

// Load messages for the first chat on mount
onMounted(() => {
    if (activeChat.value) {
        loadMessages(activeChat.value);
    }
});
</script>

<template>
    <AppShell>
        <div class="flex h-screen w-full bg-gray-50">
            <ChatSidebar 
                :chats="chats" 
                :active-chat="activeChat" 
                @new-chat="handleNewChat" 
                @select-chat="handleSelectChat"
                @delete-chat="handleDeleteChat"
                @update-title="handleUpdateTitle"
            />

            <div class="flex min-w-0 flex-1 flex-col">
                <ChatHeader
                    :native-language="nativeLanguage"
                    :target-language="targetLanguage"
                    :proficiency-level="proficiencyLevel"
                    :proficiency-label="proficiencyLabel"
                    @settings="handleSettings"
                />

                <div ref="chatContainer" class="flex-1 overflow-y-auto px-4 py-6">
                    <!-- Empty State -->
                    <div v-if="!activeChat && chats.length === 0" class="flex h-full items-center justify-center">
                        <div class="text-center">
                            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-blue-100">
                                <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h2 class="mb-2 text-2xl font-semibold text-gray-900">Start Your Language Journey</h2>
                            <p class="mb-8 text-gray-600">Practice {{ targetLanguage }} with AI-powered conversations tailored to your level</p>
                            <div class="space-y-4">
                                <button
                                    @click="handleNewChat"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 text-base font-medium text-white transition-colors hover:bg-blue-700"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Start New Conversation
                                </button>
                                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-green-100">
                                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="mb-1 font-medium text-gray-900">Adaptive Learning</h3>
                                        <p class="text-sm text-gray-600">AI adjusts to your {{ proficiencyLevel }} level</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-purple-100">
                                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="mb-1 font-medium text-gray-900">Natural Conversation</h3>
                                        <p class="text-sm text-gray-600">Practice real-world dialogue</p>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-orange-100">
                                            <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="mb-1 font-medium text-gray-900">Instant Feedback</h3>
                                        <p class="text-sm text-gray-600">Get corrections and suggestions</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Messages -->
                    <div v-else class="mx-auto max-w-3xl space-y-6">
                        <ChatMessage
                            v-for="message in messages"
                            :key="message.id"
                            :content="message.content"
                            :role="message.sender_type"
                            :timestamp="new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })"
                            :is-analyzing="message.isAnalyzing"
                        />

                        <TypingIndicator v-if="isTyping" />
                    </div>
                </div>

                <ChatInput v-model="inputMessage" :disabled="isTyping" @send="sendMessage" ref="chatInputRef" />
            </div>
        </div>
    </AppShell>
</template>
