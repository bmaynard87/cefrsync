<script setup>
import AppShell from '@/components/AppShell.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile Settings" />

    <AppShell>
        <div class="flex h-screen w-full flex-col bg-gray-50">
            <!-- Header -->
            <div class="border-b border-gray-200 bg-white px-6 py-4">
                <div class="mx-auto max-w-4xl">
                    <div class="mb-3">
                        <Link
                            :href="route('language-chat.index')"
                            class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition-colors hover:text-gray-900"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Back to Chat
                        </Link>
                    </div>
                    <h1 class="text-2xl font-semibold text-gray-900">Profile Settings</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage your account settings and language preferences</p>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto px-6 py-8">
                <div class="mx-auto max-w-4xl space-y-6">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <UpdatePasswordForm />
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </div>
    </AppShell>
</template>
