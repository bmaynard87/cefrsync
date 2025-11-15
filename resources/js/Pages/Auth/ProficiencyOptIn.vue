<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/components/AuthLayout.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
    user: {
        first_name: string;
        native_language: string;
        target_language: string;
    };
}

const props = defineProps<Props>();

const form = useForm({
    auto_update_proficiency: false,
});

const handleOptIn = (optIn: boolean) => {
    form.auto_update_proficiency = optIn;
    form.post(route('proficiency-opt-in.store'));
};
</script>

<template>
    <Head title="Proficiency Level Setup" />

    <AuthLayout
        title="Welcome to CefrSync!"
        :subtitle="`Hi ${user.first_name}, let's personalize your learning experience`"
    >
        <Card>
            <CardHeader>
                <CardTitle>Auto-Update Your Proficiency Level</CardTitle>
                <CardDescription>
                    You're learning {{ user.target_language }} from {{ user.native_language }}.
                    We noticed you didn't select a proficiency level during registration.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
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
            </CardContent>
        </Card>
    </AuthLayout>
</template>
