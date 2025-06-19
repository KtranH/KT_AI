<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>KT_AI - Sáng tạo ảnh với AI</title>
        <link rel="icon" href="{{ asset('img/voice.png') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Cloudflare Turnstile connections -->
        <link rel="preconnect" href="https://challenges.cloudflare.com">
        <link rel="dns-prefetch" href="https://challenges.cloudflare.com">
        <!-- Do NOT use preload for Cloudflare challenge resources as it causes warnings -->
        @vite(['resources/js/app.js'])
    </head>
    <body>
        <div id="app"></div>
        
        <!-- Prevent issues with Cloudflare PAT challenge -->
        <script>
            if (window.addEventListener) {
                // Handle and suppress Cloudflare-related errors
                window.addEventListener('error', function(e) {
                    if (e && e.filename && e.filename.includes('challenges.cloudflare.com')) {
                        console.warn('Suppressed Cloudflare challenge error:', e.message);
                        e.stopPropagation();
                        e.preventDefault();
                        return false;
                    }
                }, true);

                // Handle Cloudflare warnings about preloaded resources
                window.addEventListener('warning', function(e) {
                    if (e && e.message && e.message.includes('challenges.cloudflare.com')) {
                        e.stopPropagation();
                        e.preventDefault();
                        return false;
                    }
                }, true);
            }
        </script>
    </body>
</html>
