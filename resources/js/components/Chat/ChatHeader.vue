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
}>();

const handleSettings = () => {
    emit('settings');
};

const displayLevel = computed(() => {
    if (!props.proficiencyLevel) {
        return 'Not Set';
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
    <div class="border-b border-gray-200 bg-white px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Language Exchange Chat</h1>
                <p class="text-sm text-gray-500">
                    Practice your language skills with AI assistance
                </p>
            </div>
            <div class="flex items-center gap-3">
                <InsightPanel />
                <button
                    data-test="settings-button"
                    @click="handleSettings"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                >
                    Settings
                </button>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                >
                    Log out
                </Link>
            </div>
        </div>

        <!-- Chat Parameters -->
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <div
                data-test="native-language"
                class="flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5"
            >
                <Languages
                    class="h-4 w-4 text-blue-600"
                />
                <div class="flex items-center gap-1.5 text-sm">
                    <span class="font-medium text-gray-700">Native:</span>
                    <span class="text-gray-900">{{ nativeLanguage }}</span>
                </div>
            </div>

            <ArrowRight class="h-4 w-4 text-gray-400" />

            <div
                data-test="target-language"
                class="flex items-center gap-2 rounded-lg bg-green-50 px-3 py-1.5"
            >
                <Languages
                    class="h-4 w-4 text-green-600"
                />
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
                <BarChart3
                    class="h-4 w-4 text-purple-600"
                />
                <div class="flex items-center gap-1.5 text-sm">
                    <span class="font-medium text-gray-700">Level:</span>
                    <span class="text-gray-900">{{ displayLevel }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
