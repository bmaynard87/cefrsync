<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

interface Props {
    show: boolean;
    sessionId: number | null;
    currentParameters: {
        native_language: string;
        target_language: string;
        proficiency_level: string;
    };
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    updated: [params: { native_language: string; target_language: string; proficiency_level: string }];
}>();

const languages = [
    { value: '', label: 'Select Language' },
    { value: 'Arabic', label: 'Arabic' },
    { value: 'Chinese (Mandarin)', label: 'Chinese (Mandarin)' },
    { value: 'Chinese (Cantonese)', label: 'Chinese (Cantonese)' },
    { value: 'Dutch', label: 'Dutch' },
    { value: 'English', label: 'English' },
    { value: 'French', label: 'French' },
    { value: 'German', label: 'German' },
    { value: 'Greek', label: 'Greek' },
    { value: 'Hebrew', label: 'Hebrew' },
    { value: 'Hindi', label: 'Hindi' },
    { value: 'Italian', label: 'Italian' },
    { value: 'Japanese', label: 'Japanese' },
    { value: 'Korean', label: 'Korean' },
    { value: 'Polish', label: 'Polish' },
    { value: 'Portuguese', label: 'Portuguese' },
    { value: 'Russian', label: 'Russian' },
    { value: 'Spanish', label: 'Spanish' },
    { value: 'Swedish', label: 'Swedish' },
    { value: 'Turkish', label: 'Turkish' },
    { value: 'Vietnamese', label: 'Vietnamese' },
];

const proficiencyLevels = [
    { value: 'A1', label: 'A1 - Beginner' },
    { value: 'A2', label: 'A2 - Elementary' },
    { value: 'B1', label: 'B1 - Intermediate' },
    { value: 'B2', label: 'B2 - Upper Intermediate' },
    { value: 'C1', label: 'C1 - Advanced' },
    { value: 'C2', label: 'C2 - Proficient' },
];

const nativeLanguage = ref('');
const targetLanguage = ref('');
const proficiencyLevel = ref('');
const isSubmitting = ref(false);
const errors = ref<Record<string, string>>({});

watch(() => props.show, (show) => {
    if (show) {
        nativeLanguage.value = props.currentParameters.native_language;
        targetLanguage.value = props.currentParameters.target_language;
        proficiencyLevel.value = props.currentParameters.proficiency_level;
        errors.value = {};
    }
});

const handleClose = () => {
    if (!isSubmitting.value) {
        emit('close');
    }
};

const handleSubmit = () => {
    if (!props.sessionId) return;

    errors.value = {};
    isSubmitting.value = true;

    router.patch(
        `/language-chat/${props.sessionId}/parameters`,
        {
            native_language: nativeLanguage.value,
            target_language: targetLanguage.value,
            proficiency_level: proficiencyLevel.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isSubmitting.value = false;
                emit('updated', {
                    native_language: nativeLanguage.value,
                    target_language: targetLanguage.value,
                    proficiency_level: proficiencyLevel.value,
                });
                emit('close');
            },
            onError: (errs) => {
                errors.value = errs;
                isSubmitting.value = false;
            },
        }
    );
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        @click.self="handleClose"
    >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">
                    Edit Chat Parameters
                </h2>
                <button
                    @click="handleClose"
                    class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                    :disabled="isSubmitting"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label for="native_language" class="block text-sm font-medium text-gray-700 mb-1">
                        Native Language
                    </label>
                    <select
                        id="native_language"
                        v-model="nativeLanguage"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        :disabled="isSubmitting"
                    >
                        <option v-for="lang in languages" :key="lang.value" :value="lang.value">
                            {{ lang.label }}
                        </option>
                    </select>
                    <p v-if="errors.native_language" class="mt-1 text-sm text-red-600">
                        {{ errors.native_language }}
                    </p>
                </div>

                <div>
                    <label for="target_language" class="block text-sm font-medium text-gray-700 mb-1">
                        Target Language
                    </label>
                    <select
                        id="target_language"
                        v-model="targetLanguage"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        :disabled="isSubmitting"
                    >
                        <option v-for="lang in languages" :key="lang.value" :value="lang.value">
                            {{ lang.label }}
                        </option>
                    </select>
                    <p v-if="errors.target_language" class="mt-1 text-sm text-red-600">
                        {{ errors.target_language }}
                    </p>
                </div>

                <div>
                    <label for="proficiency_level" class="block text-sm font-medium text-gray-700 mb-1">
                        Proficiency Level (CEFR)
                    </label>
                    <select
                        id="proficiency_level"
                        v-model="proficiencyLevel"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        :disabled="isSubmitting"
                    >
                        <option v-for="level in proficiencyLevels" :key="level.value" :value="level.value">
                            {{ level.label }}
                        </option>
                    </select>
                    <p v-if="errors.proficiency_level" class="mt-1 text-sm text-red-600">
                        {{ errors.proficiency_level }}
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button
                        type="button"
                        @click="handleClose"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        :disabled="isSubmitting"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        :disabled="isSubmitting"
                    >
                        {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
