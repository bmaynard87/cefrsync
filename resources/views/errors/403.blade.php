<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Denied - {{ config('app.name') }}</title>

    @vite(['resources/js/app.ts'])
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
                    <div class="flex items-center justify-center rounded-full bg-red-100 h-32 w-32">
                        <svg class="h-16 w-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div class="absolute -bottom-2 -right-2 rounded-full bg-red-500 px-3 py-1">
                        <span class="text-white font-bold text-sm">403</span>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Access Denied
            </h1>
            <p class="text-lg text-gray-600 mb-8">
                @if(isset($exception) && $exception->getMessage())
                    {{ $exception->getMessage() }}
                @else
                    You don't have permission to access this resource. Please contact an administrator if you believe this is a mistake.
                @endif
            </p>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition-colors hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Go Home
                </a>
                <button onclick="history.back()" class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-6 py-3 font-medium text-gray-700 transition-colors hover:bg-gray-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Go Back
                </button>
            </div>

            <!-- Help Text -->
            <p class="mt-8 text-sm text-gray-500">
                Need help? <a href="mailto:support@cefrsync.com" class="text-blue-600 hover:text-blue-700 font-medium">Contact Support</a>
            </p>
        </div>
    </div>
</body>
</html>
