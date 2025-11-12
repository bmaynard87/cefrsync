<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    chatHistory: Array<{
        id: number;
        title: string;
        last_message_at: string;
        native_language: string;
        target_language: string;
    }>;
    userSettings: {
        native_language: string;
        target_language: string;
        proficiency_level: string;
    };
}>();
</script>

<template>
    <Head title="Language Chat" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        Language Chat - Coming Soon
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
