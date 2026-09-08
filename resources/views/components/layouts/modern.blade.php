@props([
    'title' => null,
    'heading' => null,
])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' · '.config('app.name', 'Nexo Desk') : config('app.name', 'Nexo Desk') }}</title>

    @vite('resources/js/modern.js')
    @livewireStyles
    @fluxAppearance
</head>
<body
    {{ $attributes->class('min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100') }}
    x-data="{
        sidebarOpen: false,
        desktop: window.matchMedia('(min-width: 1024px)').matches,
        breakpoint: null,
        breakpointChanged: null,
        init() {
            this.breakpoint = window.matchMedia('(min-width: 1024px)');
            this.breakpointChanged = (event) => {
                this.desktop = event.matches;

                if (event.matches) this.sidebarOpen = false;
            };

            this.breakpoint.addEventListener('change', this.breakpointChanged);
        },
        destroy() {
            this.breakpoint?.removeEventListener('change', this.breakpointChanged);
        },
        openSidebar() {
            this.sidebarOpen = true;
            this.$nextTick(() => this.$refs.sidebarClose?.focus());
        },
        closeSidebar(restoreFocus = true) {
            this.sidebarOpen = false;
            if (restoreFocus) this.$nextTick(() => this.$refs.sidebarOpen?.focus());
        },
    }"
    x-on:keydown.escape.window="if (sidebarOpen) closeSidebar()"
>
    <div
        x-cloak
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-zinc-950/50 lg:hidden"
        aria-hidden="true"
        x-on:click="closeSidebar()"
    ></div>

    <aside
        id="modern-sidebar"
        class="fixed inset-y-0 start-0 z-50 flex w-72 [transform:translateX(var(--modern-sidebar-translate))] flex-col border-e border-zinc-200 bg-white shadow-xl transition-transform duration-200 ease-out lg:[transform:translateX(0)] lg:shadow-none dark:border-zinc-800 dark:bg-zinc-900"
        x-bind:style="`--modern-sidebar-translate: ${sidebarOpen ? '0%' : '-100%'}`"
        x-bind:aria-hidden="(! desktop && ! sidebarOpen).toString()"
        x-bind:inert="! desktop && ! sidebarOpen"
        x-trap.inert.noscroll="! desktop && sidebarOpen"
        aria-label="Navegação principal"
    >
        <div class="flex h-16 shrink-0 items-center justify-between border-b border-zinc-200 px-5 dark:border-zinc-800">
            <span class="text-lg font-semibold tracking-tight text-zinc-950 dark:text-white">
                {{ config('app.name', 'Nexo Desk') }}
            </span>

            <button
                type="button"
                class="inline-flex size-10 items-center justify-center rounded-lg text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 lg:hidden dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white"
                x-ref="sidebarClose"
                x-on:click="closeSidebar()"
                aria-label="Fechar menu"
            >
                <flux:icon.x-mark class="size-5" aria-hidden="true" />
            </button>
        </div>

        <nav class="min-h-0 flex-1 overflow-y-auto px-4 py-5" aria-label="Páginas modernas">
            @isset($navigation)
                {{ $navigation }}
            @else
                <p class="rounded-lg border border-dashed border-zinc-300 px-3 py-4 text-sm leading-6 text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                    As páginas modernas serão adicionadas aqui conforme a migração avançar.
                </p>
            @endisset
        </nav>

        @auth
            <div class="shrink-0 border-t border-zinc-200 p-4 dark:border-zinc-800">
                <flux:dropdown position="top" align="start" class="w-full">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="str(auth()->user()->name)->split('/\s+/')->filter()->take(2)->map(fn ($part) => str($part)->substr(0, 1))->implode('')"
                        class="w-full"
                    />

                    <flux:menu>
                        <flux:menu.item icon="sun" x-on:click="$flux.appearance = 'light'">Tema claro</flux:menu.item>
                        <flux:menu.item icon="moon" x-on:click="$flux.appearance = 'dark'">Tema escuro</flux:menu.item>
                        <flux:menu.item icon="computer-desktop" x-on:click="$flux.appearance = 'system'">Usar tema do sistema</flux:menu.item>
                        <flux:menu.separator />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <flux:menu.item type="submit" icon="arrow-right-start-on-rectangle">
                                Sair
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        @endauth
    </aside>

    <div class="min-h-screen lg:ps-72">
        <header class="sticky top-0 z-30 flex h-16 items-center border-b border-zinc-200 bg-white/95 px-4 backdrop-blur lg:hidden dark:border-zinc-800 dark:bg-zinc-900/95">
            <button
                type="button"
                class="inline-flex size-10 items-center justify-center rounded-lg text-zinc-600 hover:bg-zinc-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 dark:text-zinc-300 dark:hover:bg-zinc-800"
                x-ref="sidebarOpen"
                x-on:click="openSidebar()"
                x-bind:aria-expanded="sidebarOpen.toString()"
                aria-controls="modern-sidebar"
                aria-label="Abrir menu"
            >
                <flux:icon.bars-3 class="size-5" aria-hidden="true" />
            </button>
            <span class="ms-3 truncate font-semibold">{{ config('app.name', 'Nexo Desk') }}</span>
        </header>

        <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
            @if($heading || isset($actions))
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    @if($heading)
                        <flux:heading size="xl" level="1">{{ $heading }}</flux:heading>
                    @endif

                    @isset($actions)
                        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
                    @endisset
                </div>
            @endif

            <div class="mb-6 space-y-3" aria-live="polite">
                @if(session('success'))
                    <flux:callout variant="success" icon="check-circle" :text="session('success')" />
                @endif

                @if(session('error'))
                    <flux:callout variant="danger" icon="exclamation-triangle" :text="session('error')" />
                @endif

                @if($errors->any())
                    <flux:callout variant="danger" icon="exclamation-triangle" heading="Revise os campos informados">
                        <ul class="list-disc space-y-1 ps-5 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </flux:callout>
                @endif
            </div>

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    @fluxScripts
</body>
</html>
