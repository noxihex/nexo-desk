@php($errorTitle = trim($__env->yieldContent('title')) ?: 'Erro')

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $errorTitle }} · {{ config('app.name', 'Nexo Desk') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite('resources/js/modern.js')
    @fluxAppearance
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <main class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-lg text-center">
            <a href="{{ auth()->check() ? route('home') : route('login') }}" class="inline-flex items-center gap-2.5" aria-label="{{ config('app.name', 'Nexo Desk') }}">
                <img src="{{ asset('favicon.ico') }}" alt="" class="size-10 object-contain" aria-hidden="true">
                <span class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Nexo Desk') }}</span>
            </a>

            <section class="mt-8 rounded-2xl border border-zinc-200 bg-white px-6 py-10 shadow-sm sm:px-10 dark:border-zinc-800 dark:bg-zinc-900" aria-labelledby="error-title">
                <p class="text-sm font-semibold tracking-widest text-blue-600 dark:text-blue-400">@yield('code')</p>
                <h1 id="error-title" class="mt-3 text-3xl font-semibold tracking-tight">{{ $errorTitle }}</h1>
                <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-zinc-600 dark:text-zinc-400">@yield('message')</p>

                <div class="mt-7 flex justify-center">
                    @yield('action')
                </div>
            </section>
        </div>
    </main>
</body>
</html>
