<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { useLanguageOptions } from '@/composables/useLanguageOptions';
import AuthLayout from '@/components/AuthLayout.vue';
import FormField from '@/components/FormField.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import LanguageSelect from '@/components/LanguageSelect.vue';
import PasswordStrengthIndicator from '@/components/PasswordStrengthIndicator.vue';
import GoogleSignInButton from '@/components/GoogleSignInButton.vue';
import { useRecaptcha } from '@/composables/useRecaptcha';

const { languages, proficiencyLevels } = useLanguageOptions();

// Pre-fill form in development mode for easier testing
const isDev = import.meta.env.DEV;

const form = useForm({
    first_name: isDev ? 'John' : '',
    last_name: isDev ? 'Doe' : '',
    email: isDev ? 'john.doe@example.com' : '',
    password: isDev ? 'SuperStrongPassword123!@#' : '',
    password_confirmation: isDev ? 'SuperStrongPassword123!@#' : '',
    native_language: isDev ? 'ja' : '',
    target_language: isDev ? 'en' : '',
    proficiency_level: isDev ? '' : '', // Leave empty to test opt-in flow
    recaptcha_token: '',
});

const { executeRecaptcha, error: recaptchaError } = useRecaptcha();
const googleError = ref<string | null>(null);

const submit = async () => {
    try {
        // Execute reCAPTCHA before submitting
        const token = await executeRecaptcha('register');
        form.recaptcha_token = token;

        form.post(route('register'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    } catch (err) {
        console.error('reCAPTCHA error:', err);
        // You might want to show an error message to the user
    }
};

const handleGoogleSignIn = (response: { credential: string }) => {
    googleError.value = null;
    
    // Send the credential to our backend
    router.post(route('auth.google.callback'), {
        credential: response.credential,
    }, {
        onError: (errors) => {
            googleError.value = errors.credential || 'Authentication failed. Please try again.';
        },
    });
};

const handleGoogleError = (error: { error: string }) => {
    console.error('Google Sign-In error:', error);
    googleError.value = 'Google Sign-In failed. Please try again.';
};
</script>

<template>

    <Head title="Register" />

    <AuthLayout title="Create your account" subtitle="Start your language learning journey today">
        <Alert v-if="googleError" class="mb-6 border-red-200 bg-red-50">
            <AlertDescription class="text-sm text-red-800">
                {{ googleError }}
            </AlertDescription>
        </Alert>

        <div class="mb-6">
            <GoogleSignInButton
                v-if="$page.props.auth?.googleClientId"
                :client-id="$page.props.auth.googleClientId"
                @signin="handleGoogleSignIn"
                @error="handleGoogleError"
            />
        </div>

        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <Separator />
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="bg-white px-2 text-gray-500">OR</span>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <FormField id="first_name" label="First Name" v-model="form.first_name" :error="form.errors.first_name"
                    placeholder="John" required autofocus autocomplete="given-name" />

                <FormField id="last_name" label="Last Name" v-model="form.last_name" :error="form.errors.last_name"
                    placeholder="Doe" required autocomplete="family-name" />
            </div>

            <FormField id="email" label="Email" type="email" v-model="form.email" :error="form.errors.email"
                placeholder="you@example.com" required autocomplete="username" />

            <LanguageSelect id="native_language" label="Native Language" v-model="form.native_language"
                :error="form.errors.native_language" :exclude-value="form.target_language" :options="languages"
                placeholder="Select your native language" required />

            <LanguageSelect id="target_language" label="Target Language" v-model="form.target_language"
                :error="form.errors.target_language" :exclude-value="form.native_language" :options="languages"
                placeholder="Select language to learn" required />

            <LanguageSelect id="proficiency_level" label="Proficiency Level (CEFR)" v-model="form.proficiency_level"
                :error="form.errors.proficiency_level" :options="proficiencyLevels" />

            <div class="space-y-2">
                <FormField id="password" label="Password" type="password" v-model="form.password"
                    :error="form.errors.password" placeholder="••••••••" required autocomplete="new-password" />
                <PasswordStrengthIndicator :password="form.password" />
            </div>

            <FormField id="password_confirmation" label="Confirm Password" type="password"
                v-model="form.password_confirmation" :error="form.errors.password_confirmation" placeholder="••••••••"
                required autocomplete="new-password" />

            <LoadingButton :loading="form.processing" loading-text="Creating account...">
                Create account
            </LoadingButton>
        </form>

        <Separator class="my-6" />

        <p class="text-center text-sm text-gray-600">
            Already have an account?
            <Link :href="route('login')" class="font-medium text-gray-900 hover:text-gray-700 hover:underline">
            Sign in
            </Link>
        </p>
    </AuthLayout>
</template>
