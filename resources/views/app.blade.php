<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Content Security Policy for Google Identity Services -->
    <meta http-equiv="Content-Security-Policy" content="
        script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 http://127.0.0.1:5174 https://accounts.google.com/gsi/client https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/;
        worker-src 'self' blob:;
        frame-src 'self' https://accounts.google.com/gsi/ https://www.google.com/recaptcha/;
        connect-src 'self' ws://localhost:5173 http://localhost:5173 ws://127.0.0.1:5174 http://127.0.0.1:5174 https://accounts.google.com/gsi/ https://www.google.com/recaptcha/;
        style-src 'self' 'unsafe-inline' https://accounts.google.com/gsi/style https://fonts.bunny.net;
        font-src 'self' https://fonts.bunny.net;
    ">

    <title inertia>CefrSync</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client" async></script>

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>