<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { useLanguageOptions } from '@/composables/useLanguageOptions';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    first_name: user.first_name,
    last_name: user.last_name,
    email: user.email,
    native_language: user.native_language || '',
    target_language: user.target_language || '',
    proficiency_level: user.proficiency_level || '',
    auto_update_proficiency: user.auto_update_proficiency || false,
    localize_insights: user.localize_insights || false,
});

const { languages, proficiencyLevels } = useLanguageOptions();

// Filter out the selected language from the other dropdown
const availableNativeLanguages = computed(() => {
    if (!form.target_language) return languages;
    return languages.filter(lang => lang.value !== form.target_language);
});

const availableTargetLanguages = computed(() => {
    if (!form.native_language) return languages;
    return languages.filter(lang => lang.value !== form.native_language);
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information, email address, and language learning preferences.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <InputLabel for="first_name" value="First Name" />

                    <TextInput
                        id="first_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.first_name"
                        required
                        autofocus
                        autocomplete="given-name"
                    />

                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>

                <div>
                    <InputLabel for="last_name" value="Last Name" />

                    <TextInput
                        id="last_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.last_name"
                        required
                        autocomplete="family-name"
                    />

                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-base font-medium text-gray-900 mb-4">
                    Language Learning Preferences
                </h3>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <InputLabel for="native_language" value="Native Language" />

                        <select
                            id="native_language"
                            v-model="form.native_language"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Select your native language</option>
                            <option v-for="lang in availableNativeLanguages" :key="lang.value" :value="lang.value">
                                {{ lang.label }}
                            </option>
                        </select>

                        <InputError class="mt-2" :message="form.errors.native_language" />
                    </div>

                    <div>
                        <InputLabel for="target_language" value="Target Language" />

                        <select
                            id="target_language"
                            v-model="form.target_language"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Select language to learn</option>
                            <option v-for="lang in availableTargetLanguages" :key="lang.value" :value="lang.value">
                                {{ lang.label }}
                            </option>
                        </select>

                        <InputError class="mt-2" :message="form.errors.target_language" />
                    </div>

                    <div class="sm:col-span-2">
                        <InputLabel for="proficiency_level" value="Proficiency Level (CEFR)" />

                        <select
                            id="proficiency_level"
                            v-model="form.proficiency_level"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option v-for="level in proficiencyLevels" :key="level.value" :value="level.value">
                                {{ level.label }}
                            </option>
                        </select>

                        <InputError class="mt-2" :message="form.errors.proficiency_level" />
                        
                        <p class="mt-1 text-xs text-gray-500">
                            Your default proficiency level for language learning sessions.
                        </p>
                    </div>

                    <div class="sm:col-span-2">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="auto_update_proficiency"
                                    type="checkbox"
                                    v-model="form.auto_update_proficiency"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="auto_update_proficiency" class="font-medium text-gray-700">
                                    Auto-update proficiency level
                                </label>
                                <p class="text-gray-500">
                                    Allow LangGPT to automatically update your proficiency level based on your progress (when highly confident).
                                </p>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.auto_update_proficiency" />
                    </div>

                    <div class="sm:col-span-2">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input
                                    id="localize_insights"
                                    type="checkbox"
                                    v-model="form.localize_insights"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="localize_insights" class="font-medium text-gray-700">
                                    Localize insights to native language
                                </label>
                                <p class="text-gray-500">
                                    Receive grammar and vocabulary feedback in your native language for easier understanding.
                                </p>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="form.errors.localize_insights" />
                    </div>
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
