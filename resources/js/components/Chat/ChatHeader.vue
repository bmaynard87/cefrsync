<script setup lang="ts">
interface Props {
    nativeLanguage: string;
    targetLanguage: string;
    proficiencyLevel: string;
    proficiencyLabel?: string;
}

defineProps<Props>();

const emit = defineEmits<{
    settings: [];
    'edit-params': [];
}>();

const handleSettings = () => {
    emit('settings');
};

const handleEditParams = () => {
    emit('edit-params');
};
</script>

<template>
    <div class="border-b border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Language Exchange Chat</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Practice your language skills with AI assistance
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    data-test="settings-button"
                    @click="handleSettings"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Settings
                </button>
            </div>
        </div>

        <!-- Chat Parameters -->
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <div
                data-test="native-language"
                class="flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 dark:bg-blue-900/20"
            >
                <svg
                    class="h-4 w-4 text-blue-600 dark:text-blue-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"
                    ></path>
                </svg>
                <div class="flex items-center gap-1.5 text-sm">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Native:</span>
                    <span class="text-gray-900 dark:text-white">{{ nativeLanguage }}</span>
                </div>
            </div>

            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>

            <div
                data-test="target-language"
                class="flex items-center gap-2 rounded-lg bg-green-50 px-3 py-1.5 dark:bg-green-900/20"
            >
                <svg
                    class="h-4 w-4 text-green-600 dark:text-green-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"
                    ></path>
                </svg>
                <div class="flex items-center gap-1.5 text-sm">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Target:</span>
                    <span class="text-gray-900 dark:text-white">{{ targetLanguage }}</span>
                </div>
            </div>

            <div class="h-4 w-px bg-gray-300 dark:bg-gray-600"></div>

            <div
                data-test="proficiency-level"
                class="flex items-center gap-2 rounded-lg bg-purple-50 px-3 py-1.5 dark:bg-purple-900/20"
            >
                <svg
                    class="h-4 w-4 text-purple-600 dark:text-purple-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                    ></path>
                </svg>
                <div class="flex items-center gap-1.5 text-sm">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Level:</span>
                    <span class="text-gray-900 dark:text-white">
                        {{ proficiencyLevel }}{{ proficiencyLabel ? ` (${proficiencyLabel})` : '' }}
                    </span>
                </div>
            </div>

            <button
                data-test="edit-params-button"
                @click="handleEditParams"
                class="ml-auto flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                    ></path>
                </svg>
                Edit
            </button>
        </div>
    </div>
</template>
