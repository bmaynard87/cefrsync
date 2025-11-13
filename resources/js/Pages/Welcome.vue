<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
}>();
</script>

<template>
    <Head title="Welcome to CefrSync" />
    <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white">
        <!-- Header -->
        <header class="container mx-auto px-6 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">CefrSync</span>
                </div>
                
                <nav v-if="canLogin" class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('language-chat.index')"
                        class="rounded-lg bg-blue-600 px-6 py-2.5 font-medium text-white transition-colors hover:bg-blue-700"
                    >
                        Go to Chat
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-lg px-6 py-2.5 font-medium text-gray-700 transition-colors hover:text-blue-600"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-blue-600 px-6 py-2.5 font-medium text-white transition-colors hover:bg-blue-700"
                        >
                            Get Started
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="container mx-auto px-6 py-20">
            <div class="mx-auto max-w-4xl text-center">
                <h1 class="mb-6 text-5xl font-bold leading-tight text-gray-900 sm:text-6xl">
                    Master Languages with
                    <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        AI-Powered Conversations
                    </span>
                </h1>
                <p class="mb-10 text-xl text-gray-600">
                    Practice real conversations tailored to your CEFR level. Get instant feedback, track your progress, and achieve fluency faster with personalized AI coaching.
                </p>
                
                <div v-if="canLogin && !$page.props.auth.user" class="flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-8 py-4 text-lg font-semibold text-white transition-all hover:bg-blue-700 hover:shadow-lg"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Start Learning Free
                    </Link>
                    <Link
                        :href="route('login')"
                        class="inline-flex items-center gap-2 rounded-lg border-2 border-gray-300 bg-white px-8 py-4 text-lg font-semibold text-gray-700 transition-all hover:border-blue-600 hover:text-blue-600"
                    >
                        Sign In
                    </Link>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mx-auto mt-24 grid max-w-6xl gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-4 mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-green-100">
                        <svg class="h-7 w-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-gray-900">CEFR-Aligned Learning</h3>
                    <p class="text-gray-600">
                        Conversations adapt to your level from A1 (Beginner) to C2 (Proficient), following internationally recognized standards.
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-4 mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-purple-100">
                        <svg class="h-7 w-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-gray-900">Natural Conversations</h3>
                    <p class="text-gray-600">
                        Engage in realistic dialogues about topics you care about. Practice grammar, vocabulary, and cultural nuances naturally.
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-4 mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-orange-100">
                        <svg class="h-7 w-7 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-gray-900">Instant Feedback</h3>
                    <p class="text-gray-600">
                        Get immediate corrections and suggestions on grammar, vocabulary, and expression to accelerate your learning.
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-4 mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-blue-100">
                        <svg class="h-7 w-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-gray-900">Progress Tracking</h3>
                    <p class="text-gray-600">
                        Monitor your improvement across grammar, vocabulary, fluency, and more with detailed insights and analytics.
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-4 mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-pink-100">
                        <svg class="h-7 w-7 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-gray-900">24/7 Availability</h3>
                    <p class="text-gray-600">
                        Practice whenever inspiration strikes. Your AI language partner is always ready for a conversation.
                    </p>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-4 mx-auto flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-100">
                        <svg class="h-7 w-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="mb-3 text-xl font-semibold text-gray-900">Multiple Languages</h3>
                    <p class="text-gray-600">
                        Learn Spanish, French, German, Italian, Japanese, and more. Practice from your native language with ease.
                    </p>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="mx-auto mt-24 max-w-4xl rounded-2xl bg-gradient-to-r from-blue-600 to-purple-600 p-12 text-center shadow-xl">
                <h2 class="mb-4 text-3xl font-bold text-white sm:text-4xl">
                    Ready to Accelerate Your Language Learning?
                </h2>
                <p class="mb-8 text-xl text-blue-100">
                    Join thousands of learners practicing with AI-powered conversations
                </p>
                <Link
                    v-if="canLogin && canRegister && !$page.props.auth.user"
                    :href="route('register')"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-8 py-4 text-lg font-semibold text-blue-600 transition-all hover:bg-blue-50 hover:shadow-lg"
                >
                    Get Started for Free
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </Link>
                <Link
                    v-else-if="$page.props.auth.user"
                    :href="route('language-chat.index')"
                    class="inline-flex items-center gap-2 rounded-lg bg-white px-8 py-4 text-lg font-semibold text-blue-600 transition-all hover:bg-blue-50 hover:shadow-lg"
                >
                    Continue Learning
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </Link>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 py-8">
            <div class="container mx-auto px-6 text-center text-sm text-gray-600">
                <p>© 2024 CefrSync. Practice languages with confidence.</p>
            </div>
        </footer>
    </div>
</template>
