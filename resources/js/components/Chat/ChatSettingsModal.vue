<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import LanguageSelect from '@/components/LanguageSelect.vue';
import { useLanguageOptions } from '@/composables/useLanguageOptions';


interface Props {
    open: boolean;
    chatSessionId: number;
    nativeLanguage: string;
    targetLanguage: string;
    proficiencyLevel: string | null;
    localizeCorrections?: boolean;
    localizeInsights?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    localizeCorrections: false,
    localizeInsights: false,
});

const emit = defineEmits<{
    'update:open': [value: boolean];
    updated: [];
}>();

const nativeLanguage = ref(props.nativeLanguage);
const targetLanguage = ref(props.targetLanguage);
const proficiencyLevel = ref(props.proficiencyLevel || 'auto');
const localizeCorrections = ref(props.localizeCorrections);
const localizeInsights = ref(props.localizeInsights);
const isSaving = ref(false);
const errors = ref<Record<string, string>>({});

const { languages } = useLanguageOptions();

// Watch for prop changes when modal opens
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        nativeLanguage.value = props.nativeLanguage;
        targetLanguage.value = props.targetLanguage;
        proficiencyLevel.value = props.proficiencyLevel || 'auto';
        localizeCorrections.value = props.localizeCorrections;
        localizeInsights.value = props.localizeInsights;
        errors.value = {};
    }
});

const proficiencyLevels = [
    { value: 'auto', label: 'Auto', description: 'Automatically adjust based on your performance' },
    { value: 'A1', label: 'A1 - Beginner', description: 'Can understand and use familiar everyday expressions' },
    { value: 'A2', label: 'A2 - Elementary', description: 'Can communicate in simple routine tasks' },
    { value: 'B1', label: 'B1 - Intermediate', description: 'Can deal with most situations while traveling' },
    { value: 'B2', label: 'B2 - Upper Intermediate', description: 'Can interact with native speakers fluently' },
    { value: 'C1', label: 'C1 - Advanced', description: 'Can use language flexibly and effectively' },
    { value: 'C2', label: 'C2 - Mastery', description: 'Can understand virtually everything with ease' },
];

const handleClose = () => {
    emit('update:open', false);
};

const handleSave = () => {
    errors.value = {};
    isSaving.value = true;

    router.patch(
        route('language-chat.update-parameters', { chatSession: props.chatSessionId }),
        {
            native_language: nativeLanguage.value,
            target_language: targetLanguage.value,
            proficiency_level: proficiencyLevel.value,
            localize_corrections: localizeCorrections.value,
            localize_insights: localizeInsights.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('updated');
                handleClose();
            },
            onError: (errs) => {
                errors.value = errs;
            },
            onFinish: () => {
                isSaving.value = false;
            },
        }
    );
};
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[500px]">
            <DialogHeader>
                <DialogTitle>Chat Session Settings</DialogTitle>
                <DialogDescription>
                    Customize the language settings for this chat session. These settings only apply to this
                    conversation.
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-6 py-4">
                <!-- Native Language -->
                <LanguageSelect id="native-language" label="Native Language" v-model="nativeLanguage"
                    :options="languages" :exclude-value="targetLanguage" placeholder="Select your native language"
                    :error="errors.native_language" />

                <!-- Target Language -->
                <LanguageSelect id="target-language" label="Target Language" v-model="targetLanguage"
                    :options="languages" :exclude-value="nativeLanguage" placeholder="Select language you're learning"
                    :error="errors.target_language" />

                <!-- Proficiency Level -->
                <div class="grid gap-2">
                    <Label for="proficiency-level">Proficiency Level</Label>
                    <select id="proficiency-level" v-model="proficiencyLevel"
                        class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-white focus:outline-none focus:ring-2 focus:ring-gray-950 focus:ring-offset-2">
                        <!-- TODO: Add default option-->
                        <option v-for="level in proficiencyLevels" :key="level.value" :value="level.value">
                            {{ level.label }} - {{ level.description }}
                        </option>
                    </select>
                    <p v-if="errors.proficiency_level" class="text-sm text-red-600">
                        {{ errors.proficiency_level }}
                    </p>
                </div>

                <!-- Localization Options -->
                <div class="space-y-4 rounded-lg border border-gray-200 p-4">
                    <h4 class="text-sm font-medium">Localization Preferences</h4>

                    <!-- Localize Corrections -->
                    <div class="flex items-center justify-between">
                        <div class="space-y-0.5">
                            <Label for="localize-corrections" class="font-normal">
                                Localize Corrections
                            </Label>
                            <p class="text-xs text-gray-500">
                                Receive error corrections in your native language
                            </p>
                        </div>
                        <input id="localize-corrections" type="checkbox" v-model="localizeCorrections"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500" />
                    </div>

                    <!-- Localize Insights -->
                    <div class="flex items-center justify-between">
                        <div class="space-y-0.5">
                            <Label for="localize-insights" class="font-normal">
                                Localize Insights
                            </Label>
                            <p class="text-xs text-gray-500">
                                Receive learning insights in your native language
                            </p>
                        </div>
                        <input id="localize-insights" type="checkbox" v-model="localizeInsights"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500" />
                    </div>
                </div>
            </div>

            <DialogFooter>
                <Button variant="outline" @click="handleClose" :disabled="isSaving">
                    Cancel
                </Button>
                <Button @click="handleSave" :disabled="isSaving">
                    {{ isSaving ? 'Saving...' : 'Save Changes' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
