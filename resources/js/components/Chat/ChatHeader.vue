<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Languages, ArrowRight, BarChart3, Settings, Menu } from 'lucide-vue-next';
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
    toggleSidebar: [];
}>();

const handleSettings = () => {
    emit('settings');
};

const handleToggleSidebar = () => {
    emit('toggleSidebar');
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
    <div class="border-b border-gray-200 bg-white px-4 py-3 sm:px-6 sm:py-4">
        <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <!-- Mobile Menu Button -->
                <button
                    @click="handleToggleSidebar"
                    class="flex-shrink-0 rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-100 lg:hidden"
                    aria-label="Toggle sidebar"
                >
                    <Menu class="h-5 w-5" />
                </button>
                
                <div class="min-w-0 flex-1">
                    <h1 class="text-lg font-semibold text-gray-900 sm:text-xl">Language Exchange Chat</h1>
                    <p class="hidden text-sm text-gray-500 sm:block">
                        Practice your language skills with AI assistance
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <InsightPanel />
                <Link
                    :href="route('profile.edit')"
                    class="hidden rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 sm:block"
                >
                    Profile
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 sm:px-4"
                >
                    <span class="hidden sm:inline">Log out</span>
                    <span class="sm:hidden">Logout</span>
                </Link>
            </div>
        </div>

        <!-- Chat Parameters -->
        <div class="mt-3 flex flex-wrap items-center gap-2 sm:mt-4 sm:gap-3">
            <div
                data-test="native-language"
                class="flex items-center gap-1.5 rounded-lg bg-blue-50 px-2.5 py-1.5 sm:gap-2 sm:px-3"
            >
                <Languages
                    class="h-4 w-4 flex-shrink-0 text-blue-600"
                />
                <div class="flex items-center gap-1 text-sm sm:gap-1.5">
                    <span class="hidden font-medium text-gray-700 sm:inline">Native:</span>
                    <span class="text-gray-900">{{ nativeLanguage }}</span>
                </div>
            </div>

            <ArrowRight class="h-4 w-4 flex-shrink-0 text-gray-400" />

            <div
                data-test="target-language"
                class="flex items-center gap-1.5 rounded-lg bg-green-50 px-2.5 py-1.5 sm:gap-2 sm:px-3"
            >
                <Languages
                    class="h-4 w-4 flex-shrink-0 text-green-600"
                />
                <div class="flex items-center gap-1 text-sm sm:gap-1.5">
                    <span class="hidden font-medium text-gray-700 sm:inline">Target:</span>
                    <span class="text-gray-900">{{ targetLanguage }}</span>
                </div>
            </div>

            <div class="hidden h-4 w-px bg-gray-300 sm:block"></div>

            <div
                data-test="proficiency-level"
                class="flex items-center gap-1.5 rounded-lg bg-purple-50 px-2.5 py-1.5 sm:gap-2 sm:px-3"
            >
                <BarChart3
                    class="h-4 w-4 flex-shrink-0 text-purple-600"
                />
                <div class="flex items-center gap-1 text-sm sm:gap-1.5">
                    <span class="hidden font-medium text-gray-700 sm:inline">Level:</span>
                    <span class="text-gray-900">{{ displayLevel }}</span>
                </div>
            </div>

            <button
                data-test="chat-settings-button"
                @click="handleSettings"
                class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 sm:ml-2 sm:px-3"
                title="Chat Settings"
            >
                <Settings class="h-4 w-4 flex-shrink-0" />
                <span class="hidden sm:inline">Chat Settings</span>
            </button>
        </div>
    </div>
</template>
