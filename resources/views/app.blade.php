<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <script>
            (function () {
                var stored = null;
                try {
                    stored = localStorage.getItem('theme');
                } catch (e) {}

                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = stored === 'light' || stored === 'dark'
                    ? stored
                    : (prefersDark ? 'dark' : 'light');

                var root = document.documentElement;
                root.classList.toggle('dark', theme === 'dark');
                root.style.colorScheme = theme;
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&family=plus-jakarta-sans:400,500,600,700,800&family=lora:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="bg-white font-sans text-zinc-800 antialiased dark:bg-zinc-950 dark:text-zinc-200">
        <div id="app-loader" class="app-loader" aria-hidden="true">
            <div class="app-loader-spinner"></div>
        </div>

        @inertia
    </body>
</html>
