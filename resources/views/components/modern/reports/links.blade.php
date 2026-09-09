<p class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Relatórios</p>
@foreach(['horas' => 'Por empresa', 'analista' => 'Por analista'] as $key => $label)
    <a href="{{ route('relatorios.'.$key) }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800">{{ $label }}</a>
@endforeach
