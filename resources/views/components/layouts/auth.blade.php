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
                <img src="{{ asset('favicon.ico') }}" alt="" class="mx-auto mb-4 size-20 object-contain" aria-hidden="true">
                <p class="text-xl font-semibold tracking-tight">{{ config('app.name', 'Nexo Desk') }}</p>
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

            <div class="mt-6 flex justify-center" x-data>
                <div class="inline-flex items-center gap-1 rounded-xl border border-zinc-200 bg-white p-1 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" role="group" aria-label="Aparência">
                    @foreach(['light' => ['sun', 'Tema claro'], 'dark' => ['moon', 'Tema escuro'], 'system' => ['computer-desktop', 'Usar tema do sistema']] as $appearance => [$icon, $label])
                        <button
                            type="button"
                            x-on:click="$flux.appearance = '{{ $appearance }}'"
                            x-bind:aria-pressed="($flux.appearance === '{{ $appearance }}').toString()"
                            x-bind:class="$flux.appearance === '{{ $appearance }}' ? 'bg-zinc-100 text-zinc-950 dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white'"
                            class="inline-flex size-9 items-center justify-center rounded-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500"
                            aria-label="{{ $label }}"
                            title="{{ $label }}"
                        >
                            <flux:icon :name="$icon" class="size-4" aria-hidden="true" />
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </main>
    @livewireScripts
    @fluxScripts
</body>
</html>
