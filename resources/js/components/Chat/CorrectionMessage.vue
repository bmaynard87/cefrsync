<script setup lang="ts">
import { AlertCircle, CheckCircle, AlertTriangle, Lightbulb } from 'lucide-vue-next';
import { computed } from 'vue';

interface CorrectionData {
    error_type: 'offensive' | 'meaningless' | 'unnatural' | 'archaic' | 'dangerous';
    severity: 'critical' | 'high' | 'medium';
    original_text: string;
    corrected_text: string;
    explanation: string;
    context: string;
    recommendations?: string[];
}

interface Props {
    content: string;
    correctionData: CorrectionData;
    timestamp: string;
}

const props = defineProps<Props>();

const errorTypeLabels: Record<string, string> = {
    offensive: 'Offensive Language',
    meaningless: 'Unclear Meaning',
    unnatural: 'Unnatural Phrasing',
    archaic: 'Archaic Expression',
    dangerous: 'Potentially Harmful'
};

const errorTypeColors: Record<string, string> = {
    offensive: 'text-red-700 dark:text-red-300',
    meaningless: 'text-orange-700 dark:text-orange-300',
    unnatural: 'text-yellow-700 dark:text-yellow-300',
    archaic: 'text-blue-700 dark:text-blue-300',
    dangerous: 'text-red-800 dark:text-red-200'
};

const severityColors: Record<string, string> = {
    critical: 'bg-red-200 text-red-900 dark:bg-red-900/40 dark:text-red-200',
    high: 'bg-orange-200 text-orange-900 dark:bg-orange-900/40 dark:text-orange-200',
    medium: 'bg-yellow-200 text-yellow-900 dark:bg-yellow-900/40 dark:text-yellow-200'
};

const borderColors: Record<string, string> = {
    critical: 'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-950/30',
    high: 'border-orange-300 dark:border-orange-700 bg-orange-50 dark:bg-orange-950/30',
    medium: 'border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-950/30'
};

const errorIcon = computed(() => {
    switch (props.correctionData.severity) {
        case 'critical': return AlertCircle;
        case 'high': return AlertTriangle;
        default: return Lightbulb;
    }
});
</script>

<template>
    <div :class="[
        'px-4 py-3 rounded-lg border-2 my-3',
        borderColors[correctionData.severity]
    ]">
        <!-- Header with error type and severity -->
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <component :is="errorIcon" :class="['w-5 h-5', errorTypeColors[correctionData.error_type]]" />
                <span :class="['font-semibold text-sm', errorTypeColors[correctionData.error_type]]">
                    {{ errorTypeLabels[correctionData.error_type] }}
                </span>
            </div>
            <span :class="[
                'text-xs font-medium px-2 py-1 rounded-full uppercase tracking-wide',
                severityColors[correctionData.severity]
            ]">
                {{ correctionData.severity }}
            </span>
        </div>

        <!-- Original vs Corrected Text -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
            <div class="space-y-1">
                <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wide">
                    Original
                </div>
                <div class="p-2 rounded bg-gray-100 dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-600">
                    <span class="text-sm line-through text-gray-800 dark:text-gray-100">
                        {{ correctionData.original_text }}
                    </span>
                </div>
            </div>
            <div class="space-y-1">
                <div
                    class="text-xs font-semibold text-green-700 dark:text-green-300 uppercase tracking-wide flex items-center gap-1">
                    <CheckCircle class="w-3 h-3" />
                    Suggested
                </div>
                <div class="p-2 rounded bg-green-50 dark:bg-green-950/30 border-2 border-green-300 dark:border-green-700">
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ correctionData.corrected_text }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Explanation -->
        <div class="mb-3 p-3 rounded bg-white/50 dark:bg-gray-800/50">
            <div class="text-xs font-semibold text-gray-800 dark:text-gray-100 uppercase tracking-wide mb-1">
                Explanation
            </div>
            <p class="text-sm text-gray-900 dark:text-gray-50">
                {{ correctionData.explanation }}
            </p>
        </div>

        <!-- Context -->
        <div class="mb-3 p-3 rounded bg-white/50 dark:bg-gray-800/50">
            <div class="text-xs font-semibold text-gray-800 dark:text-gray-100 uppercase tracking-wide mb-1">
                Context
            </div>
            <p class="text-sm text-gray-900 dark:text-gray-50">
                {{ correctionData.context }}
            </p>
        </div>

        <!-- Recommendations -->
        <div v-if="correctionData.recommendations && correctionData.recommendations.length > 0" class="mb-2 p-3 rounded bg-white/50 dark:bg-gray-800/50">
            <div
                class="text-xs font-semibold text-gray-800 dark:text-gray-100 uppercase tracking-wide mb-2 flex items-center gap-1">
                <Lightbulb class="w-3 h-3" />
                Recommendations
            </div>
            <ul class="space-y-1">
                <li v-for="(rec, index) in correctionData.recommendations" :key="index"
                    class="text-sm text-gray-900 dark:text-gray-50 flex items-start gap-2">
                    <span class="text-gray-400 dark:text-gray-500">•</span>
                    <span>{{ rec }}</span>
                </li>
            </ul>
        </div>

        <!-- Timestamp -->
        <div class="text-xs text-gray-500 dark:text-gray-500 text-right mt-2">
            {{ timestamp }}
        </div>
    </div>
</template>
