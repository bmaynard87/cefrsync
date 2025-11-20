<script setup lang="ts">
import { ref, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Plus, Check, X, Pencil, Trash2 } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import { useIsMobile } from '@/composables/useIsMobile';

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
    isOpen?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isOpen: false,
});

const emit = defineEmits<{
    'new-chat': [];
    'select-chat': [chatId: number];
    'delete-chat': [chatId: number];
    'update-title': [chatId: number, newTitle: string];
    'close': [];
}>();

const editingChatId = ref<number | null>(null);
const editingTitle = ref('');
const editInputRef = ref<HTMLInputElement | null>(null);
const { isMobile } = useIsMobile();

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
        emit('close'); // Close drawer on mobile after selection
    }
};

const handleDeleteChat = (event: Event, chatId: number) => {
    event.stopPropagation(); // Prevent selecting the chat when clicking delete
    if (confirm('Are you sure you want to delete this conversation?')) {
        emit('delete-chat', chatId);
    }
};

const startEditing = async (event: Event, chat: Chat) => {
    event.stopPropagation();
    editingChatId.value = chat.id;
    editingTitle.value = chat.title;
    
    // Focus on desktop only
    if (!isMobile.value) {
        await nextTick();
        editInputRef.value?.focus();
    }
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
    <!-- Mobile Overlay -->
    <div 
        v-if="isOpen" 
        class="fixed inset-0 z-40 bg-black/50 lg:hidden"
        @click="emit('close')"
    ></div>
    
    <!-- Sidebar -->
    <div 
        :class="[
            'fixed lg:static inset-y-0 left-0 z-50 flex h-dvh lg:h-screen w-64 flex-col border-r border-gray-200 bg-white transform transition-transform duration-300 lg:translate-x-0',
            isOpen ? 'translate-x-0' : '-translate-x-full'
        ]"
    >
        <!-- Logo and Mobile Close -->
        <div class="flex-shrink-0 border-b border-gray-200 px-4 py-3">
            <div class="flex items-center justify-between">
                <Link :href="route('language-chat.index')">
                    <AppLogo size="sm" />
                </Link>
                <button
                    @click="emit('close')"
                    class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    aria-label="Close sidebar"
                >
                    <X class="h-5 w-5 text-gray-600" />
                </button>
            </div>
        </div>
        
        <!-- Sidebar Header -->
        <div class="flex-shrink-0 border-b border-gray-200 px-4 py-3">
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
                <Plus class="h-4 w-4" />
                New Chat
            </button>
        </div>

        <!-- Chat List -->
        <div data-test="chat-list" class="min-h-0 flex-1 overflow-y-auto p-2">
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
                                ref="editInputRef"
                                v-model="editingTitle"
                                @keydown="(e) => handleKeydown(e, chat.id)"
                                type="text"
                                class="flex-1 rounded border border-blue-500 px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                            <button
                                @click="(e) => saveTitle(e, chat.id)"
                                class="p-1 rounded hover:bg-green-100"
                                title="Save"
                            >
                                <Check class="h-4 w-4 text-green-600" />
                            </button>
                            <button
                                @click="cancelEditing"
                                class="p-1 rounded hover:bg-red-100"
                                title="Cancel"
                            >
                                <X class="h-4 w-4 text-red-600" />
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
                                    <Pencil class="h-3 w-3 text-gray-500" />
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
                        <Trash2
                            class="h-4 w-4 text-gray-400 hover:text-red-600"
                        />
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer with Profile Link (Mobile) -->
        <div class="flex-shrink-0 border-t border-gray-200 p-3 lg:hidden">
            <Link
                :href="route('profile.edit')"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100"
            >
                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Profile Settings</span>
            </Link>
        </div>
    </div>
</template>
