<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { useLanguageOptions } from '@/composables/useLanguageOptions';

const { languages, proficiencyLevels } = useLanguageOptions();

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    native_language: '',
    target_language: '',
    proficiency_level: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="flex min-h-screen w-full items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 px-4 py-12">
        <div class="w-full max-w-md space-y-8">
            <!-- Logo/Header -->
            <div class="text-center">
                <h1 class="text-4xl font-bold tracking-tight text-gray-900">CefrSync</h1>
                <p class="mt-2 text-sm text-gray-600">Language learning companion</p>
            </div>

            <!-- Main Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-xl">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900">Create your account</h2>
                    <p class="mt-1 text-sm text-gray-600">Start your language learning journey today</p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label for="first_name">First Name</Label>
                            <Input
                                id="first_name"
                                type="text"
                                v-model="form.first_name"
                                placeholder="John"
                                required
                                autofocus
                                autocomplete="given-name"
                                :class="{ 'border-red-500': form.errors.first_name }"
                            />
                            <p v-if="form.errors.first_name" class="text-sm text-red-600">
                                {{ form.errors.first_name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="last_name">Last Name</Label>
                            <Input
                                id="last_name"
                                type="text"
                                v-model="form.last_name"
                                placeholder="Doe"
                                required
                                autocomplete="family-name"
                                :class="{ 'border-red-500': form.errors.last_name }"
                            />
                            <p v-if="form.errors.last_name" class="text-sm text-red-600">
                                {{ form.errors.last_name }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            placeholder="you@example.com"
                            required
                            autocomplete="username"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="native_language">Native Language</Label>
                        <select
                            id="native_language"
                            v-model="form.native_language"
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            :class="{ 'border-red-500': form.errors.native_language }"
                        >
                            <option v-for="lang in languages" :key="lang.value" :value="lang.value">
                                {{ lang.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.native_language" class="text-sm text-red-600">
                            {{ form.errors.native_language }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="target_language">Target Language</Label>
                        <select
                            id="target_language"
                            v-model="form.target_language"
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            :class="{ 'border-red-500': form.errors.target_language }"
                        >
                            <option v-for="lang in languages" :key="lang.value" :value="lang.value">
                                {{ lang.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.target_language" class="text-sm text-red-600">
                            {{ form.errors.target_language }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="proficiency_level">Proficiency Level (CEFR)</Label>
                        <select
                            id="proficiency_level"
                            v-model="form.proficiency_level"
                            required
                            class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm ring-offset-white file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-950 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                            :class="{ 'border-red-500': form.errors.proficiency_level }"
                        >
                            <option v-for="level in proficiencyLevels" :key="level.value" :value="level.value">
                                {{ level.label }}
                            </option>
                        </select>
                        <p v-if="form.errors.proficiency_level" class="text-sm text-red-600">
                            {{ form.errors.proficiency_level }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            v-model="form.password"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirm Password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            placeholder="••••••••"
                            required
                            autocomplete="new-password"
                            :class="{ 'border-red-500': form.errors.password_confirmation }"
                        />
                        <p v-if="form.errors.password_confirmation" class="text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing">Create account</span>
                        <span v-else class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Creating account...
                        </span>
                    </Button>
                </form>

                <Separator class="my-6" />

                <p class="text-center text-sm text-gray-600">
                    Already have an account?
                    <Link
                        :href="route('login')"
                        class="font-medium text-gray-900 hover:text-gray-700 hover:underline"
                    >
                        Sign in
                    </Link>
                </p>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs text-gray-500">
                © {{ new Date().getFullYear() }} CefrSync. All rights reserved.
            </p>
        </div>
    </div>
</template>
