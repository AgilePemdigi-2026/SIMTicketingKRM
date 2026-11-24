@props([
    'title',
    'period',
    'labels' => [],
    'values' => [],
    'type' => 'bar',
    'money' => false,
])

@php
    $values = array_values($values);
    $labels = array_values($labels);

    $count = count($values);
    $maximum = $count ? max($values) : 0;

    $width = 420;
    $height = 145;
    $left = 4;
    $right = 4;
    $top = 8;
    $bottom = 120;

    $plotWidth = $width - $left - $right;
    $plotHeight = $bottom - $top;

    $axisMaximum = max(1, $maximum * 1.15);

    $step = $plotWidth / max(1, $count);
    $barWidth = $step * 0.8;

    $points = [];

    foreach ($values as $i => $value) {

        $x = $type === 'line' && $count > 1
            ? $left + $i * $plotWidth / ($count - 1)
            : $left + ($i + 0.5) * $step;

        $points[] = [
            'x' => $x,
            'y' => $bottom
                - ((float) $value / $axisMaximum)
                * $plotHeight,
        ];
    }

    $format = static fn ($value) =>
        ($money ? 'Rp ' : '')
        .number_format($value, $money ? 2 : 0, ',', '.');
@endphp

<section class="min-w-0 border border-[#D8E2DA] bg-white p-3.5">

    <div class="flex min-h-[30px] flex-wrap items-start justify-between gap-1">

        <h3 class="text-[11px] font-semibold text-[#173D30]">
            {{ $title }}
        </h3>

        <span class="text-[9px] text-[#819387]">
            {{ $period }}
        </span>

    </div>

    @if ($maximum > 0)

        <svg
            viewBox="0 0 {{ $width }} {{ $height }}"
            class="block h-auto w-full"
            role="img"
            aria-label="{{ $title }}"
        >

            @if ($type === 'bar')

                @for ($i = 0; $i <= 3; $i++)

                    @php
                        $y = $bottom - ($plotHeight / 3) * $i;
                    @endphp

                    <line
                        x1="{{ $left }}"
                        y1="{{ $y }}"
                        x2="{{ $width - $right }}"
                        y2="{{ $y }}"
                        stroke="#E0E8E1"
                        stroke-width="0.8"
                    />

                @endfor

            @endif

            @if ($type === 'line' && $count > 1)

                <polygon
                    points="{{ $points[0]['x'] }},{{ $bottom }}
                    @foreach ($points as $point)
                        {{ $point['x'] }},{{ $point['y'] }}
                    @endforeach
                    {{ $points[$count - 1]['x'] }},{{ $bottom }}"
                    fill="#E5EFE6"
                />

                <polyline
                    points="@foreach ($points as $point) {{ $point['x'] }},{{ $point['y'] }} @endforeach"
                    fill="none"
                    stroke="#397A5C"
                    stroke-width="1.3"
                />

                @foreach ($points as $i => $point)

                    <circle
                        cx="{{ $point['x'] }}"
                        cy="{{ $point['y'] }}"
                        r="4"
                        fill="transparent"
                    >
                        <title>{{ $labels[$i] }}: {{ $format($values[$i]) }}</title>
                    </circle>

                @endforeach

            @else

                @foreach ($points as $i => $point)

                    <rect
                        x="{{ $point['x'] - $barWidth / 2 }}"
                        y="{{ $point['y'] }}"
                        width="{{ $barWidth }}"
                        height="{{ max(0, $bottom - $point['y']) }}"
                        fill="{{ $i % 2 === 0 ? '#709B82' : '#A8C4B5' }}"
                    >

                        <title>{{ $labels[$i] }}: {{ $format($values[$i]) }}</title>

                    </rect>

                @endforeach

            @endif

            @foreach ($points as $i => $point)

                @if ($i % max(1, (int) ceil($count / 7)) === 0)

                    <text
                        x="{{ max(18, min($width - 18, $point['x'])) }}"
                        y="{{ $bottom + 16 }}"
                        text-anchor="middle"
                        font-size="8"
                        fill="#71877A"
                    >
                        {{ \Illuminate\Support\Str::limit($labels[$i], 10, '..') }}
                    </text>

                @endif

            @endforeach

        </svg>

    @else

        <div class="flex aspect-[420/145] items-center justify-center text-[11px] text-[#82978A]">

            Belum ada data

        </div>

    @endif

</section>
