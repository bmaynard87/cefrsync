<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';

interface Chat {
    id: number;
    title: string;
    timestamp: string;
    isActive: boolean;
}

interface Props {
    chats: Chat[];
    activeChat: number | null;
    hasUnusedNewChat?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'new-chat': [];
    'select-chat': [chatId: number];
    'delete-chat': [chatId: number];
    'update-title': [chatId: number, newTitle: string];
}>();

const editingChatId = ref<number | null>(null);
const editingTitle = ref('');

const handleNewChat = () => {
    // Don't emit if there's already an unused new chat
    if (props.hasUnusedNewChat) {
        return;
    }
    emit('new-chat');
};

const handleSelectChat = (chatId: number) => {
    if (editingChatId.value !== chatId) {
        emit('select-chat', chatId);
    }
};

const handleDeleteChat = (event: Event, chatId: number) => {
    event.stopPropagation(); // Prevent selecting the chat when clicking delete
    if (confirm('Are you sure you want to delete this conversation?')) {
        emit('delete-chat', chatId);
    }
};

const startEditing = (event: Event, chat: Chat) => {
    event.stopPropagation();
    editingChatId.value = chat.id;
    editingTitle.value = chat.title;
};

const saveTitle = (event: Event, chatId: number) => {
    event.stopPropagation();
    if (editingTitle.value.trim()) {
        emit('update-title', chatId, editingTitle.value.trim());
    }
    editingChatId.value = null;
    editingTitle.value = '';
};

const cancelEditing = (event: Event) => {
    event.stopPropagation();
    editingChatId.value = null;
    editingTitle.value = '';
};

const handleKeydown = (event: KeyboardEvent, chatId: number) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        saveTitle(event, chatId);
    } else if (event.key === 'Escape') {
        cancelEditing(event);
    }
};
</script>

<template>
    <div class="flex h-screen w-64 flex-col border-r border-gray-200 bg-white">
        <!-- Logo -->
        <div class="border-b border-gray-200 px-4 py-4">
            <Link :href="route('language-chat.index')">
                <AppLogo size="sm" />
            </Link>
        </div>
        
        <!-- Sidebar Header -->
        <div class="border-b border-gray-200 px-4 py-4">
            <button
                data-test="new-chat-button"
                @click="handleNewChat"
                :disabled="hasUnusedNewChat"
                :class="[
                    'flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors',
                    hasUnusedNewChat 
                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                        : 'bg-blue-600 text-white hover:bg-blue-700'
                ]"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Chat
            </button>
        </div>

        <!-- Chat List -->
        <div data-test="chat-list" class="flex-1 overflow-y-auto p-2">
            <div class="space-y-1">
                <div
                    v-for="chat in chats"
                    :key="chat.id"
                    data-test="chat-item"
                    @click="handleSelectChat(chat.id)"
                    :class="[
                        'group flex w-full items-start gap-3 rounded-lg px-3 py-2.5 text-left transition-colors cursor-pointer',
                        activeChat === chat.id
                            ? 'bg-gray-100 hover:bg-gray-200'
                            : 'hover:bg-gray-100',
                    ]"
                >
                    <svg
                        class="mt-0.5 h-4 w-4 flex-shrink-0"
                        :class="
                            activeChat === chat.id
                                ? 'text-gray-600'
                                : 'text-gray-400'
                        "
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                        ></path>
                    </svg>
                    <div class="min-w-0 flex-1">
                        <!-- Editing Mode -->
                        <div v-if="editingChatId === chat.id" class="flex items-center gap-1" @click.stop>
                            <input
                                v-model="editingTitle"
                                @keydown="(e) => handleKeydown(e, chat.id)"
                                type="text"
                                class="flex-1 rounded border border-blue-500 px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                autofocus
                            />
                            <button
                                @click="(e) => saveTitle(e, chat.id)"
                                class="p-1 rounded hover:bg-green-100"
                                title="Save"
                            >
                                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            <button
                                @click="cancelEditing"
                                class="p-1 rounded hover:bg-red-100"
                                title="Cancel"
                            >
                                <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- View Mode -->
                        <div v-else class="group/title">
                            <div class="flex items-center gap-1">
                                <p
                                    class="truncate text-sm font-medium"
                                    :class="
                                        activeChat === chat.id
                                            ? 'text-gray-900'
                                            : 'text-gray-700'
                                    "
                                    :title="chat.title"
                                >
                                    {{ chat.title }}
                                </p>
                                <button
                                    @click="(e) => startEditing(e, chat)"
                                    class="flex-shrink-0 opacity-0 group-hover/title:opacity-100 p-0.5 rounded hover:bg-gray-200"
                                    title="Edit title"
                                >
                                    <svg class="h-3 w-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-0.5 truncate text-xs text-gray-500">
                                {{ chat.timestamp }}
                            </p>
                        </div>
                    </div>
                    <button
                        @click="(e) => handleDeleteChat(e, chat.id)"
                        class="opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded hover:bg-red-50"
                        title="Delete conversation"
                    >
                        <svg
                            class="h-4 w-4 text-gray-400 hover:text-red-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            ></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
