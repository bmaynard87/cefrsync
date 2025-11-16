<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useLanguageOptions } from '@/composables/useLanguageOptions';
import AuthLayout from '@/components/AuthLayout.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import LanguageSelect from '@/components/LanguageSelect.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';

interface Props {
    user: {
        first_name: string;
        native_language: string | null;
        target_language: string | null;
    };
    needsLanguageSetup: boolean;
}

const props = defineProps<Props>();
const { languages } = useLanguageOptions();

const form = useForm({
    native_language: props.user.native_language || '',
    target_language: props.user.target_language || '',
    proficiency_level: '',
    auto_update_proficiency: true,
});

const handleOptIn = (optIn: boolean) => {
    form.auto_update_proficiency = optIn;
    form.post(route('proficiency-opt-in.store'));
};

const handleSubmit = () => {
    form.post(route('proficiency-opt-in.store'));
};
</script>

<template>
    <Head title="Setup Your Learning Profile" />

    <AuthLayout
        :title="needsLanguageSetup ? 'Welcome to CefrSync!' : 'Proficiency Level Setup'"
        :subtitle="needsLanguageSetup ? `Hi ${user.first_name}, let's set up your learning profile` : `Hi ${user.first_name}, let's personalize your learning experience`"
    >
        <Card>
            <CardHeader>
                <CardTitle v-if="needsLanguageSetup">Language Learning Setup</CardTitle>
                <CardTitle v-else>Auto-Update Your Proficiency Level</CardTitle>
                <CardDescription v-if="needsLanguageSetup">
                    Let's start by selecting your native and target languages, then choose your current proficiency level.
                </CardDescription>
                <CardDescription v-else>
                    You're learning {{ user.target_language }} from {{ user.native_language }}.
                    We noticed you didn't select a proficiency level during registration.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <!-- Language Setup Form (for Google users) -->
                <form v-if="needsLanguageSetup" @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <LanguageSelect
                            id="native_language"
                            label="Native Language"
                            v-model="form.native_language"
                            :error="form.errors.native_language"
                            :exclude-value="form.target_language"
                            :options="languages"
                            placeholder="Select your native language"
                            required
                        />

                        <LanguageSelect
                            id="target_language"
                            label="Target Language"
                            v-model="form.target_language"
                            :error="form.errors.target_language"
                            :exclude-value="form.native_language"
                            :options="languages"
                            placeholder="Select language to learn"
                            required
                        />
                    </div>

                    <div class="space-y-3">
                        <Label>Auto-Update Proficiency Level *</Label>
                        <p class="text-sm text-gray-700">
                            Would you like CefrSync to automatically update your proficiency level
                            based on your performance and conversations?
                        </p>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-900 mb-2">How it works:</h4>
                            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                                <li>We analyze your language usage patterns</li>
                                <li>Track vocabulary and grammar complexity</li>
                                <li>Automatically adjust your CEFR level over time</li>
                                <li>You can always change this later in settings</li>
                            </ul>
                        </div>

                        <div class="flex gap-4 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="form.auto_update_proficiency"
                                    :value="true"
                                    class="h-4 w-4 text-blue-600"
                                />
                                <span class="text-sm font-medium">Yes, auto-update my level</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    v-model="form.auto_update_proficiency"
                                    :value="false"
                                    class="h-4 w-4 text-blue-600"
                                />
                                <span class="text-sm font-medium">No, I'll set it manually</span>
                            </label>
                        </div>
                        <p v-if="form.errors.auto_update_proficiency" class="text-sm text-red-600">
                            {{ form.errors.auto_update_proficiency }}
                        </p>
                    </div>

                    <div v-if="form.auto_update_proficiency === false" class="space-y-2 border-t pt-6">
                        <Label>Current Proficiency Level (CEFR) *</Label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <label
                                v-for="level in ['A1', 'A2', 'B1', 'B2', 'C1', 'C2']"
                                :key="level"
                                :class="[
                                    'flex items-center justify-center px-4 py-3 rounded-lg border-2 cursor-pointer transition-all',
                                    form.proficiency_level === level
                                        ? 'border-blue-600 bg-blue-50 text-blue-700'
                                        : 'border-gray-200 hover:border-gray-300 bg-white'
                                ]"
                            >
                                <input
                                    type="radio"
                                    v-model="form.proficiency_level"
                                    :value="level"
                                    class="sr-only"
                                />
                                <span class="font-semibold">{{ level }}</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            A1 = Beginner, A2 = Elementary, B1 = Intermediate, B2 = Upper Intermediate, C1 = Advanced, C2 = Proficient
                        </p>
                        <p v-if="form.errors.proficiency_level" class="text-sm text-red-600">
                            {{ form.errors.proficiency_level }}
                        </p>
                    </div>

                    <div class="flex justify-end pt-4">
                        <LoadingButton
                            type="submit"
                            :loading="form.processing"
                            loading-text="Setting up your profile..."
                            class="w-full sm:w-auto"
                        >
                            Complete Setup
                        </LoadingButton>
                    </div>

                    <p class="text-xs text-gray-500 text-center">
                        You can update these settings anytime from your profile.
                    </p>
                </form>

                <!-- Proficiency Opt-In Only (for regular users) -->
                <div v-else class="space-y-4">
                    <div class="space-y-3">
                        <p class="text-sm text-gray-700">
                            Would you like CefrSync to automatically update your proficiency level
                            based on your performance and conversations?
                        </p>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-900 mb-2">How it works:</h4>
                            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                                <li>We analyze your language usage patterns</li>
                                <li>Track vocabulary and grammar complexity</li>
                                <li>Automatically adjust your CEFR level (A1-C2)</li>
                                <li>You can always change this later in settings</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <LoadingButton
                            @click="handleOptIn(true)"
                            :loading="form.processing && form.auto_update_proficiency"
                            loading-text="Setting up..."
                            class="flex-1"
                        >
                            Yes, auto-update my level
                        </LoadingButton>
                        
                        <Button
                            @click="handleOptIn(false)"
                            :disabled="form.processing"
                            variant="outline"
                            class="flex-1"
                        >
                            No, I'll set it manually later
                        </Button>
                    </div>

                    <p class="text-xs text-gray-500 text-center">
                        You can update your proficiency level and this setting anytime from your profile.
                    </p>
                </div>
            </CardContent>
        </Card>
    </AuthLayout>
</template>
