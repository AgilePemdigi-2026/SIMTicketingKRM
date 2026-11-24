@php
    $isMoney = $isMoney ?? false;

    $chartDays = array_values($daily);

    $dayCount = count($chartDays);

    $maximum = 0;

    foreach ($chartDays as $day) {
        foreach ($series as $item) {
            $maximum = max(
                $maximum,
                (float) $day[$item['key']]
            );
        }
    }

    $hasChartData = $maximum > 0;

    $axisMaximum = max(
        4,
        ceil($maximum / 4) * 4
    );

    $width = max(760, $dayCount * 48 + 130);

    $height = 280;

    $left = 100;
    $right = 24;
    $top = 20;
    $bottom = 220;

    $plotWidth = $width - $left - $right;

    $plotHeight = $bottom - $top;

    $groupWidth = $plotWidth / max(1, $dayCount);

    $barWidth = min(
        18,
        $groupWidth / (count($series) + 1)
    );

    $labelInterval = max(
        1,
        (int) ceil($dayCount / 24)
    );
@endphp

<section class="min-w-0 overflow-hidden rounded-lg border border-[#DCE5DE] bg-white">

    <div class="border-b border-[#E2E9E4] px-5 py-5">

        <h2 class="text-sm font-bold text-[#173D30]">
            {{ $title }}
        </h2>

        <p class="mt-1 text-xs leading-5 text-[#819187]">
            {{ $description }}
        </p>

    </div>

    <div class="p-5">

        @if (! $hasChartData)

            <div class="mb-4 rounded-sm border border-[#DCE5DE] bg-[#F5F8F5] px-4 py-3 text-xs text-[#527A65]">
                {{ $emptyMessage }}
            </div>

        @endif

        <div class="mb-4 flex flex-wrap gap-4">

            @foreach ($series as $item)

                <div class="flex items-center gap-2 text-xs text-[#527A65]">

                    <span
                        class="inline-block h-3 w-3 rounded-sm"
                        style="background-color: {{ $item['color'] }}"
                        aria-hidden="true"
                    ></span>

                    {{ $item['label'] }}

                </div>

            @endforeach

        </div>

        <div class="overflow-x-auto">

            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 {{ $width }} {{ $height }}"
                width="{{ $width }}"
                height="{{ $height }}"
                role="img"
                aria-label="{{ $title }}"
                class="block"
            >

                @for ($tick = 0; $tick <= 4; $tick++)

                    @php
                        $value = $axisMaximum * $tick / 4;

                        $y = $bottom
                            - $plotHeight * $tick / 4;

                        $axisLabel = $isMoney
                            ? number_format($value, 0, ',', '.')
                            : $formatNumber($value);
                    @endphp

                    <line
                        x1="{{ $left }}"
                        y1="{{ $y }}"
                        x2="{{ $width - $right }}"
                        y2="{{ $y }}"
                        stroke="#DCE5DE"
                        stroke-dasharray="4 4"
                    />

                    <text
                        x="{{ $left - 12 }}"
                        y="{{ $y + 4 }}"
                        text-anchor="end"
                        font-size="11"
                        fill="#718079"
                    >
                        {{ $axisLabel }}
                    </text>

                @endfor

                @foreach ($chartDays as $index => $day)

                    @php
                        $center = $left
                            + ($index + 0.5) * $groupWidth;
                    @endphp

                    @foreach ($series as $seriesIndex => $item)

                        @php
                            $value = (float) $day[$item['key']];

                            $barHeight = $value
                                / $axisMaximum
                                * $plotHeight;

                            $x = $center
                                - count($series) * $barWidth / 2
                                + $seriesIndex * $barWidth;

                            $y = $bottom - $barHeight;

                            $valueLabel = $isMoney
                                ? $formatMoney($day[$item['key']])
                                : $formatNumber($day[$item['key']]);
                        @endphp

                        <rect
                            x="{{ $x }}"
                            y="{{ $y }}"
                            width="{{ max(1, $barWidth - 2) }}"
                            height="{{ $barHeight }}"
                            rx="2"
                            fill="{{ $item['color'] }}"
                        >
                            <title>{{ $formatDate($day['date']) }} - {{ $item['label'] }}: {{ $valueLabel }}</title>
                        </rect>

                    @endforeach

                    @if ($index % $labelInterval === 0 || $loop->last)

                        <text
                            x="{{ $center }}"
                            y="{{ $bottom + 22 }}"
                            text-anchor="middle"
                            font-size="10"
                            fill="#718079"
                        >
                            {{ \Carbon\CarbonImmutable::parse($day['date'])->format('d/m') }}
                        </text>

                    @endif

                @endforeach

                <line
                    x1="{{ $left }}"
                    y1="{{ $bottom }}"
                    x2="{{ $width - $right }}"
                    y2="{{ $bottom }}"
                    stroke="#A5B9AA"
                />

                <text
                    x="{{ $left + $plotWidth / 2 }}"
                    y="{{ $height - 10 }}"
                    text-anchor="middle"
                    font-size="11"
                    fill="#718079"
                >
                    Tanggal (WIB)
                </text>

            </svg>

        </div>

        <p class="mt-3 text-[11px] leading-5 text-[#819187]">
            Arahkan kursor pada batang grafik untuk melihat nilai.
            Geser grafik secara horizontal untuk melihat tanggal lainnya.
            Nilai lengkap tersedia pada tabel rekapitulasi harian.
        </p>

    </div>

</section>
