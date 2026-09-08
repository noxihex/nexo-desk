@props(['title', 'description' => null])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · {{ config('app.name', 'Nexo Desk') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite('resources/js/modern.js')
    @livewireStyles
    @fluxAppearance
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <main class="flex min-h-screen flex-col items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <span class="mx-auto mb-4 flex size-12 items-center justify-center rounded-2xl bg-blue-600 text-xl font-bold text-white shadow-sm" aria-hidden="true">N</span>
                <p class="text-xl font-semibold tracking-tight">{{ config('app.name', 'Nexo Desk') }}</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Central de atendimento</p>
            </div>

            <x-modern.card class="rounded-2xl bg-white p-6 shadow-sm sm:p-8 dark:bg-zinc-900">
                <header class="mb-6">
                    <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
                    @if($description)
                        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $description }}</p>
                    @endif
                </header>

                @foreach(['status' => 'success', 'success' => 'success', 'error' => 'warning'] as $key => $variant)
                    @if(session($key))
                        <x-modern.alert :variant="$variant" class="mb-5">{{ session($key) }}</x-modern.alert>
                    @endif
                @endforeach
                @if($errors->any())
                    <x-modern.alert variant="danger" heading="Revise os campos informados" class="mb-5">
                        <ul class="list-disc ps-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-modern.alert>
                @endif

                {{ $slot }}
            </x-modern.card>

            <div class="mt-6 flex justify-center">
                <label class="flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                    Aparência
                    <select aria-label="Aparência" x-data x-model="$flux.appearance" class="rounded-lg border border-zinc-200 bg-white px-2 py-1.5 text-zinc-700 focus-visible:outline-2 focus-visible:outline-blue-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                        <option value="system">Sistema</option>
                        <option value="light">Claro</option>
                        <option value="dark">Escuro</option>
                    </select>
                </label>
            </div>
        </div>
    </main>
    @livewireScripts
    @fluxScripts
</body>
</html>
