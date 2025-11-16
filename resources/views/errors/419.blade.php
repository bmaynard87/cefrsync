<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Expired - {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gradient-to-b from-blue-50 to-white flex items-center justify-center px-6">
        <div class="max-w-2xl w-full text-center">
            <!-- Logo -->
            <div class="flex justify-center mb-8">
                <div class="flex items-center gap-2">
                    <div class="flex items-center justify-center rounded-lg bg-blue-600 h-12 w-12">
                        <svg class="text-white h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-900 text-3xl">CefrSync</span>
                </div>
            </div>

            <!-- Error Icon -->
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <div class="flex items-center justify-center rounded-full bg-yellow-100 h-32 w-32">
                        <svg class="h-16 w-16 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="absolute -bottom-2 -right-2 rounded-full bg-yellow-500 px-3 py-1">
                        <span class="text-white font-bold text-sm">419</span>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Session Expired
            </h1>
            <p class="text-lg text-gray-600 mb-8">
                Your session has expired for security reasons. Please refresh the page and try again.
            </p>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="location.reload()" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition-colors hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh Page
                </button>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-6 py-3 font-medium text-gray-700 transition-colors hover:bg-gray-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Go Home
                </a>
            </div>

            <!-- Help Text -->
            <div class="mt-8 text-sm text-gray-500">
                <p class="mb-2">This happens when:</p>
                <ul class="list-disc list-inside text-left max-w-md mx-auto">
                    <li>You've been inactive for too long</li>
                    <li>Your security token has expired</li>
                    <li>You opened the page in multiple tabs</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
