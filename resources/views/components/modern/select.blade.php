@props([
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => null,
    'options' => [],
    'selected' => null,
    'invalid' => null,
    'searchable' => false,
    'disabled' => false,
])

@php
    $fieldName = $name ?: $attributes->whereStartsWith('wire:model')->first();
    $invalid ??= $fieldName ? $errors->has($fieldName) : false;
    $wireModel = $attributes->wire('model');
    $wireModelName = $wireModel->value();
    $searchOptions = collect($options)->map(fn ($optionLabel, $value) => [
        'value' => (string) $value,
        'label' => (string) $optionLabel,
    ])->values();
@endphp

<flux:field>
    @if($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    @if($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    @if($searchable)
        <div
            class="relative"
            x-data="{
                open: false,
                query: '',
                value: @if($wireModelName) $wire.entangle(@js($wireModelName)).live @else @js((string) $selected) @endif,
                options: {{ Illuminate\Support\Js::from($searchOptions) }},
                normalize(value) {
                    return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR')
                },
                get filteredOptions() {
                    const query = this.normalize(this.query.trim())
                    return query === '' ? this.options : this.options.filter(option => this.normalize(option.label).includes(query))
                },
                get selectedLabel() {
                    return this.options.find(option => String(option.value) === String(this.value))?.label ?? ''
                },
                choose(option) {
                    this.value = option.value
                    this.query = ''
                    this.open = false
                },
                close() {
                    this.query = ''
                    this.open = false
                },
            }"
            x-on:click.outside="close()"
            x-on:keydown.escape.stop="close()"
        >
            @if($fieldName && ! $wireModelName)
                <input type="hidden" name="{{ $fieldName }}" x-model="value" @disabled($disabled) />
            @endif

            <div class="relative">
                <input
                    type="text"
                    role="combobox"
                    autocomplete="off"
                    aria-autocomplete="list"
                    :aria-expanded="open"
                    :value="open ? query : selectedLabel"
                    x-on:focus="open = true; query = ''; $nextTick(() => $el.select())"
                    x-on:click="open = true"
                    x-on:input="query = $event.target.value; open = true"
                    x-on:keydown.arrow-down.prevent="$refs.options?.querySelector('button:not([disabled])')?.focus()"
                    @disabled($disabled)
                    @class([
                        'block h-10 w-full rounded-lg border bg-white py-2 ps-3 pe-10 text-base leading-[1.375rem] shadow-xs outline-none placeholder:text-zinc-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm dark:bg-white/10 dark:text-zinc-300 dark:placeholder:text-zinc-400',
                        'border-red-500 dark:border-red-500' => $invalid,
                        'border-zinc-200 border-b-zinc-300/80 dark:border-white/10' => ! $invalid,
                        'cursor-not-allowed bg-zinc-100 text-zinc-500 opacity-70 dark:bg-white/[7%] dark:text-zinc-400' => $disabled,
                    ])
                    placeholder="{{ $label ? 'Pesquisar '.mb_strtolower($label).'...' : ($placeholder ?: 'Pesquisar...') }}"
                    @if($invalid) aria-invalid="true" @endif
                />
                <button type="button" tabindex="-1" x-on:click="open = ! open; if (open) $nextTick(() => $el.previousElementSibling.focus())" class="absolute inset-y-0 end-0 flex w-10 items-center justify-center text-zinc-400" aria-label="Abrir opções" @disabled($disabled)>
                    <flux:icon name="chevron-up-down" class="size-4" />
                </button>
            </div>

            <div x-cloak x-show="open" x-transition.opacity.duration.100ms class="absolute z-30 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-900" role="listbox" x-ref="options">
                <button
                    type="button"
                    x-on:click="value = ''; close()"
                    class="flex w-full items-center rounded-md px-3 py-2 text-left text-sm text-zinc-500 hover:bg-zinc-100 focus:bg-zinc-100 focus:outline-none dark:hover:bg-zinc-800 dark:focus:bg-zinc-800"
                    role="option"
                    :aria-selected="String(value ?? '') === ''"
                >
                    {{ $placeholder ?: 'Nenhuma seleção' }}
                </button>
                <template x-for="option in filteredOptions" :key="option.value">
                    <button
                        type="button"
                        x-on:click="choose(option)"
                        x-on:keydown.arrow-down.prevent="$el.nextElementSibling?.focus()"
                        x-on:keydown.arrow-up.prevent="$el.previousElementSibling?.focus()"
                        class="flex w-full items-center justify-between gap-3 rounded-md px-3 py-2 text-left text-sm text-zinc-800 hover:bg-zinc-100 focus:bg-zinc-100 focus:outline-none dark:text-zinc-200 dark:hover:bg-zinc-800 dark:focus:bg-zinc-800"
                        role="option"
                        :aria-selected="String(option.value) === String(value)"
                    >
                        <span class="truncate" x-text="option.label"></span>
                        <flux:icon name="check" class="size-4 shrink-0 text-blue-600" x-show="String(option.value) === String(value)" />
                    </button>
                </template>
                <p x-show="filteredOptions.length === 0" class="px-3 py-3 text-sm text-zinc-500">Nenhuma opção encontrada.</p>
            </div>
        </div>
    @else
        <flux:select
            :name="$fieldName"
            :placeholder="$placeholder"
            :invalid="$invalid"
            :disabled="$disabled"
            {{ $attributes }}
        >
            @foreach($options as $value => $optionLabel)
                <x-modern.select.option :value="$value" :selected="(string) $selected === (string) $value">
                    {{ $optionLabel }}
                </x-modern.select.option>
            @endforeach

            {{ $slot }}
        </flux:select>
    @endif

    @if($fieldName)
        <flux:error :name="$fieldName" />
    @endif
</flux:field>
