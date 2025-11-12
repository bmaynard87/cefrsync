<script setup lang="ts"></script>

<template>
    <div class="flex gap-4">
        <div class="flex-shrink-0">
            <div
                data-test="typing-avatar"
                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-purple-600 to-blue-500 text-white"
            >
                <span class="text-sm font-semibold">AI</span>
            </div>
        </div>
        <div
            data-test="typing-container"
            class="rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700"
        >
            <div class="flex gap-1">
                <div data-test="typing-dot" class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.3s]"></div>
                <div data-test="typing-dot" class="h-2 w-2 animate-bounce rounded-full bg-gray-400 [animation-delay:-0.15s]"></div>
                <div data-test="typing-dot" class="h-2 w-2 animate-bounce rounded-full bg-gray-400"></div>
            </div>
        </div>
    </div>
</template>
