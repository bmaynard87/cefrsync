<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Languages, ArrowRight, BarChart3 } from 'lucide-vue-next';
import { computed } from 'vue';
import InsightPanel from '@/components/Insights/InsightPanel.vue';

interface Props {
    nativeLanguage: string;
    targetLanguage: string;
    proficiencyLevel: string | null;
    proficiencyLabel?: string;
    autoUpdateProficiency?: boolean;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    settings: [];
    'menu-click': [];
}>();

const handleSettings = () => {
    emit('settings');
};

const displayLevel = computed(() => {
    if (!props.proficiencyLevel) {
        return props.autoUpdateProficiency ? 'Dynamic (Not Set)' : 'Not Set';
    }
    
    if (props.autoUpdateProficiency) {
        const levelText = props.proficiencyLabel 
            ? `${props.proficiencyLevel} - ${props.proficiencyLabel}`
            : props.proficiencyLevel;
        return `Dynamic (${levelText})`;
    }
    return props.proficiencyLevel + (props.proficiencyLabel ? ` (${props.proficiencyLabel})` : '');
});
</script>

<template>
    <div class="border-b border-gray-200 bg-white px-3 py-2 sm:px-6 sm:py-4">
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 sm:gap-0 min-w-0 flex-1">
                <!-- Hamburger Menu (Mobile Only) -->
                <button
                    @click="emit('menu-click')"
                    class="lg:hidden flex-shrink-0 p-2 -ml-2 rounded-lg hover:bg-gray-100 transition-colors"
                    aria-label="Open menu"
                >
                    <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                
                <div class="min-w-0">
                    <h1 class="text-base font-semibold text-gray-900 sm:text-xl truncate">Language Exchange Chat</h1>
                    <p class="hidden sm:block text-sm text-gray-500">
                        Practice your language skills with AI assistance
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-3 flex-shrink-0">
                <InsightPanel />
                <!-- Desktop Buttons -->
                <button
                    data-test="settings-button"
                    @click="handleSettings"
                    class="hidden sm:inline-flex rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                >
                    Settings
                </button>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="hidden sm:inline-flex rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                >
                    Log out
                </Link>
                <!-- Mobile Icon Buttons -->
                <button
                    data-test="settings-button-mobile"
                    @click="handleSettings"
                    class="sm:hidden p-2 rounded-lg border border-gray-300 text-gray-700 transition-colors hover:bg-gray-50"
                    aria-label="Settings"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </button>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="sm:hidden p-2 rounded-lg border border-gray-300 text-gray-700 transition-colors hover:bg-gray-50"
                    aria-label="Log out"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </Link>
            </div>
        </div>

        <!-- Chat Parameters -->
        <div class="mt-2 flex flex-wrap items-center gap-2 sm:mt-4 sm:gap-3">
            <div
                data-test="native-language"
                class="flex items-center gap-1.5 rounded-lg bg-blue-50 px-2 py-1 sm:gap-2 sm:px-3 sm:py-1.5"
            >
                <Languages
                    class="h-3 w-3 text-blue-600 sm:h-4 sm:w-4"
                />
                <div class="flex items-center gap-1 text-xs sm:gap-1.5 sm:text-sm">
                    <span class="font-medium text-gray-700">Native:</span>
                    <span class="text-gray-900">{{ nativeLanguage }}</span>
                </div>
            </div>

            <ArrowRight class="h-3 w-3 text-gray-400 sm:h-4 sm:w-4" />

            <div
                data-test="target-language"
                class="flex items-center gap-1.5 rounded-lg bg-green-50 px-2 py-1 sm:gap-2 sm:px-3 sm:py-1.5"
            >
                <Languages
                    class="h-3 w-3 text-green-600 sm:h-4 sm:w-4"
                />
                <div class="flex items-center gap-1 text-xs sm:gap-1.5 sm:text-sm">
                    <span class="font-medium text-gray-700">Target:</span>
                    <span class="text-gray-900">{{ targetLanguage }}</span>
                </div>
            </div>

            <div class="h-3 w-px bg-gray-300 sm:h-4"></div>

            <div
                data-test="proficiency-level"
                class="flex items-center gap-1.5 rounded-lg bg-purple-50 px-2 py-1 transition-all sm:gap-2 sm:px-3 sm:py-1.5"
            >
                <BarChart3
                    class="h-3 w-3 text-purple-600 sm:h-4 sm:w-4"
                />
                <div class="flex items-center gap-1 text-xs sm:gap-1.5 sm:text-sm">
                    <span class="font-medium text-gray-700">Level:</span>
                    <span class="text-gray-900">{{ displayLevel }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
