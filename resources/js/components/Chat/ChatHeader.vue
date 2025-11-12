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
}>();

const handleSettings = () => {
    emit('settings');
};
</script>

<template>
    <div class="border-b border-gray-200 bg-white px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Language Exchange Chat</h1>
                <p class="text-sm text-gray-500">
                    Practice your language skills with AI assistance
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    data-test="settings-button"
                    @click="handleSettings"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                >
                    Settings
                </button>
            </div>
        </div>

        <!-- Chat Parameters -->
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <div
                data-test="native-language"
                class="flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5"
            >
                <svg
                    class="h-4 w-4 text-blue-600"
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
                    <span class="font-medium text-gray-700">Native:</span>
                    <span class="text-gray-900">{{ nativeLanguage }}</span>
                </div>
            </div>

            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>

            <div
                data-test="target-language"
                class="flex items-center gap-2 rounded-lg bg-green-50 px-3 py-1.5"
            >
                <svg
                    class="h-4 w-4 text-green-600"
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
                    <span class="font-medium text-gray-700">Target:</span>
                    <span class="text-gray-900">{{ targetLanguage }}</span>
                </div>
            </div>

            <div class="h-4 w-px bg-gray-300"></div>

            <div
                data-test="proficiency-level"
                class="flex items-center gap-2 rounded-lg bg-purple-50 px-3 py-1.5"
            >
                <svg
                    class="h-4 w-4 text-purple-600"
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
                    <span class="font-medium text-gray-700">Level:</span>
                    <span class="text-gray-900">
                        {{ proficiencyLevel }}{{ proficiencyLabel ? ` (${proficiencyLabel})` : '' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
