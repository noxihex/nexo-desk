@props([
    'model',
    'files' => [],
    'remove' => 'removeMessageAttachment',
    'errorName' => 'messageAttachments',
    'label' => 'Anexos',
    'description' => 'Até 5 arquivos, com no máximo 10 MB cada.',
])

<flux:field>
    <flux:label>{{ $label }}</flux:label>
    <div class="relative mt-2 overflow-hidden rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 transition hover:border-blue-400 hover:bg-blue-50/50 focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/20 dark:border-zinc-700 dark:bg-zinc-900/60 dark:hover:border-blue-500 dark:hover:bg-blue-950/20">
        <input
            type="file"
            multiple
            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt,.mp4,.kmz,.kml,.zip"
            wire:model="{{ $model }}"
            class="absolute inset-0 z-10 size-full cursor-pointer opacity-0"
            aria-label="Selecionar anexos"
        />
        <div class="flex min-h-24 items-center justify-center gap-4 px-5 py-4 text-center sm:text-left">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                <flux:icon name="cloud-arrow-up" class="size-5" />
            </span>
            <div>
                <p class="text-sm font-medium text-zinc-900 dark:text-white">Arraste arquivos ou clique para selecionar</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">JPG, PNG, PDF, Office, texto, vídeo, KML/KMZ ou ZIP</p>
            </div>
        </div>
        <div wire:loading.flex wire:target="{{ $model }}" class="absolute inset-0 z-20 items-center justify-center gap-2 bg-white/90 text-sm font-medium text-blue-700 dark:bg-zinc-900/90 dark:text-blue-300">
            <flux:icon name="arrow-path" class="size-5 animate-spin" />
            Preparando arquivos...
        </div>
    </div>
    <flux:description>{{ $description }}</flux:description>
    <flux:error :name="$model" />
    <flux:error :name="$model.'.*'" />
    <flux:error :name="$errorName" />
    <flux:error :name="$errorName.'.*'" />

    @if(count($files))
        <ul class="mt-3 space-y-2" aria-label="Arquivos selecionados">
            @foreach($files as $index => $file)
                @php
                    $fileName = method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'Arquivo '.($index + 1);
                    $fileSize = method_exists($file, 'getSize') ? (int) $file->getSize() : 0;
                    $formattedSize = $fileSize >= 1048576
                        ? number_format($fileSize / 1048576, 1, ',', '.').' MB'
                        : number_format(max($fileSize, 1) / 1024, 1, ',', '.').' KB';
                @endphp
                <li wire:key="{{ $model }}-attachment-{{ $index }}-{{ md5($fileName) }}" class="flex items-center gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-900">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <flux:icon name="document" class="size-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $fileName }}</span>
                        <span class="block text-xs text-zinc-500">{{ $formattedSize }}</span>
                    </span>
                    <button type="button" wire:click="{{ $remove }}({{ $index }})" class="relative z-30 rounded-md p-2 text-zinc-500 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 dark:hover:bg-red-950/40 dark:hover:text-red-300" aria-label="Remover arquivo {{ $fileName }}">
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </li>
            @endforeach
        </ul>
    @endif
</flux:field>
