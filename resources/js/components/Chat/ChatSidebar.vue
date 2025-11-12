<script setup lang="ts">
interface Chat {
    id: number;
    title: string;
    timestamp: string;
    isActive: boolean;
}

interface Props {
    chats: Chat[];
    activeChat: number | null;
}

defineProps<Props>();

const emit = defineEmits<{
    'new-chat': [];
    'select-chat': [chatId: number];
}>();

const handleNewChat = () => {
    emit('new-chat');
};

const handleSelectChat = (chatId: number) => {
    emit('select-chat', chatId);
};
</script>

<template>
    <div class="w-64 border-r border-gray-200 bg-white">
        <!-- Sidebar Header -->
        <div class="border-b border-gray-200 px-4 py-4">
            <button
                data-test="new-chat-button"
                @click="handleNewChat"
                class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Chat
            </button>
        </div>

        <!-- Chat List -->
        <div class="overflow-y-auto p-2" style="height: calc(100vh - 73px)">
            <div class="space-y-1">
                <button
                    v-for="chat in chats"
                    :key="chat.id"
                    data-test="chat-item"
                    @click="handleSelectChat(chat.id)"
                    :class="[
                        'group flex w-full items-start gap-3 rounded-lg px-3 py-2.5 text-left transition-colors',
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
                        <p
                            class="truncate text-sm font-medium"
                            :class="
                                activeChat === chat.id
                                    ? 'text-gray-900'
                                    : 'text-gray-700'
                            "
                        >
                            {{ chat.title }}
                        </p>
                        <p class="mt-0.5 truncate text-xs text-gray-500">
                            {{ chat.timestamp }}
                        </p>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>
