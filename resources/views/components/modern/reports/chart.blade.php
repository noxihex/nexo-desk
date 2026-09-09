@props(['labels', 'series', 'title' => 'Evolução no período'])
@php
    $maximum = max(1, ...collect($series)->flatMap(fn ($item) => $item['values'])->all());
    $count = count($labels);
    $colors = ['#2563eb', '#16a34a', '#9333ea', '#d97706'];
@endphp
<x-modern.card class="space-y-4">
    <h2 class="text-lg font-semibold">{{ $title }}</h2>
    <div class="flex flex-wrap gap-4 text-sm">
        @foreach($series as $item)
            <span class="inline-flex items-center gap-2"><span class="size-3 rounded-full" style="background-color: {{ $colors[$loop->index] }}"></span>{{ $item['label'] }}</span>
        @endforeach
    </div>
    <svg viewBox="0 0 900 260" role="img" aria-label="{{ $title }}. Os valores detalhados estão disponíveis na tabela abaixo." class="w-full text-zinc-500 dark:text-zinc-400">
        @foreach([0, 0.5, 1] as $fraction)
            <line x1="45" x2="880" y1="{{ 220 - 190 * $fraction }}" y2="{{ 220 - 190 * $fraction }}" stroke="currentColor" opacity="0.2" />
            <text x="35" y="{{ 225 - 190 * $fraction }}" text-anchor="end" fill="currentColor" font-size="12">{{ round($maximum * $fraction, 1) }}</text>
        @endforeach
        @foreach($series as $item)
            @php
                $color = $colors[$loop->index];
                $points = collect($item['values'])->map(fn ($value, $i) => (45 + 835 * $i / max(1, $count - 1)).','.(220 - 190 * $value / $maximum))->implode(' ');
            @endphp
            <polyline points="{{ $points }}" fill="none" stroke="{{ $color }}" stroke-width="2.5" stroke-linejoin="round" />
            @foreach($item['values'] as $value)
                <circle cx="{{ 45 + 835 * $loop->index / max(1, $count - 1) }}" cy="{{ 220 - 190 * $value / $maximum }}" r="3" fill="{{ $color }}"><title>{{ $labels[$loop->index] }} — {{ $item['label'] }}: {{ $value }}</title></circle>
            @endforeach
        @endforeach
        @foreach($labels as $label)
            @if($loop->first || $loop->last || $loop->index % max(1, (int) ceil($count / 5)) === 0)
                <text x="{{ 45 + 835 * $loop->index / max(1, $count - 1) }}" y="245" text-anchor="{{ $loop->first ? 'start' : ($loop->last ? 'end' : 'middle') }}" fill="currentColor" font-size="11">{{ \Carbon\Carbon::parse($label)->format(strlen($label) > 10 ? 'd/m H:i' : 'd/m/Y') }}</text>
            @endif
        @endforeach
    </svg>
    <details class="text-sm">
        <summary class="cursor-pointer font-medium">Ver dados do gráfico</summary>
        <div class="mt-3 max-h-80 overflow-auto">
            <table class="w-full text-left">
                <thead><tr><th class="p-2">Período</th>@foreach($series as $item)<th class="p-2">{{ $item['label'] }}</th>@endforeach</tr></thead>
                <tbody>@foreach($labels as $i => $label)<tr><td class="p-2">{{ $label }}</td>@foreach($series as $item)<td class="p-2">{{ $item['values'][$i] }}</td>@endforeach</tr>@endforeach</tbody>
            </table>
        </div>
    </details>
</x-modern.card>
