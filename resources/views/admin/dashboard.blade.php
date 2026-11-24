@extends('layouts.admin')

@section('title', 'Dashboard Admin - Kebun Raya Mangrove')

@section('content')

@php

    $number = static fn ($value) =>
        number_format($value, 0, ',', '.');

    $money = static function ($value) {

        [$whole, $fraction] = array_pad(
            explode('.', (string) $value, 2),
            2,
            '00'
        );

        $fraction = str_pad($fraction, 2, '0');

        return 'Rp '.preg_replace(
            '/\B(?=(\d{3})+(?!\d))/',
            '.',
            $whole
        ).($fraction !== '00' ? ','.$fraction : '');

    };

    $dateLabel = $today->locale('id')
        ->translatedFormat('d F Y');

    $monthPeriod = $today->startOfMonth()->format('d')
        .'-'.$today->locale('id')->translatedFormat('d M Y');

    $checkinRate = $overview['total_pengunjung'] > 0
        ? $overview['total_checkin']
            / $overview['total_pengunjung'] * 100
        : null;

    $revenueCaption = $revenueGrowth !== null
        ? ($revenueGrowth >= 0 ? '+' : '')
            .number_format($revenueGrowth, 1, ',', '.')
            .'% dibanding bulan lalu'
        : 'Pendapatan tiket dan reservasi lunas';

    $cards = [
        [
            'label' => 'Total Tiket Terjual',
            'value' => $number($overview['total_tiket']),
            'description' => 'Tiket dari pesanan lunas',
            'icon' => 'ticket',
        ],
        [
            'label' => 'Total Pengunjung',
            'value' => $number($overview['total_pengunjung']),
            'description' => 'Seluruh pengunjung tercatat',
            'icon' => 'users',
        ],
        [
            'label' => 'Total Transaksi',
            'value' => $number($overview['total_transaksi']),
            'description' => $number($paidOrders).' berhasil',
            'icon' => 'transaction',
        ],
        [
            'label' => 'Total Pendapatan',
            'value' => $money($overview['total_pendapatan']),
            'description' => $revenueCaption,
            'icon' => 'chart',
        ],
        [
            'label' => 'Total Reservasi',
            'value' => $number($overview['total_reservasi']),
            'description' => $number($paidReservations).' lunas',
            'icon' => 'calendar',
        ],
        [
            'label' => 'Pengunjung Check-in',
            'value' => $number($overview['total_checkin']),
            'description' => $checkinRate !== null
                ? number_format($checkinRate, 0, ',', '.').'% dari data pengunjung'
                : 'E-Tiket yang telah digunakan',
            'icon' => 'users',
        ],
    ];

    $dailyCards = [
        'Pengunjung Hari Ini' => $operations['pengunjung'],
        'Sudah Check-in' => $operations['checkin'],
        'Belum Digunakan' => $operations['unused'],
        'Cash Belum Bayar' => $operations['pending'],
        'Reservasi Hari Ini' => $operations['reservasi'],
    ];

    $dayLabels = array_map(
        static fn ($day) =>
            \Carbon\CarbonImmutable::parse(
                $day['date']
            )->format('d/m'),
        $monthlyReport['daily']
    );

    $monthLabels = array_map(
        static fn ($month) =>
            \Carbon\CarbonImmutable::parse(
                $month.'-01'
            )->locale('id')->translatedFormat('M'),
        array_keys($monthlyVisitors)
    );

    $paymentMethods = $monthlyReport['payment_methods'];

    $paymentCount = array_sum(
        array_column($paymentMethods, 'total_transaksi')
    );

    $paymentColors = [
        'qris' => '#174B38',
        'cash' => '#ACC3B3',
        'other' => '#DCE8DF',
    ];

    $paymentLabels = [
        'qris' => 'QRIS',
        'cash' => 'Cash',
        'other' => 'Lainnya',
    ];

@endphp

<div class="mx-auto max-w-[1800px]">

    {{-- PAGE HEADER --}}
    <header class="mb-4 border-b border-[#B8C9BD] pb-2">

        <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#527A65]">

            Administrasi Kebun Raya Mangrove

        </p>

        <h1 class="mt-2 text-[24px] font-medium leading-tight text-[#0B372D]">

            Dashboard

        </h1>

        <p class="mt-3 text-xs text-[#74877B]">

            Ringkasan operasional - {{ $dateLabel }}

        </p>

    </header>

    {{-- SIX OVERVIEW CARDS --}}
    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">

        @foreach ($cards as $card)

            <article class="relative min-h-[108px] min-w-0 border border-[#D8E2DA] bg-white p-4">

                <div class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center bg-[#EAF1EB] text-[#174B38]">

                    <x-admin-icon
                        :name="$card['icon']"
                        class="h-[18px] w-[18px]"
                    />

                </div>

                <p class="pr-10 text-[11px] text-[#74877B]">

                    {{ $card['label'] }}

                </p>

                <p class="mt-3 break-words pr-5 text-[24px] font-bold leading-tight text-[#0B372D]">

                    {{ $card['value'] }}

                </p>

                <p class="mt-2 text-[10px] leading-4 text-[#648C76]">

                    {{ $card['description'] }}

                </p>

            </article>

        @endforeach

    </section>

    {{-- DAILY OPERATIONS --}}
    <section class="mt-6">

        <div class="mb-3 flex flex-wrap items-end justify-between gap-2 border-b border-[#D5DFD7] pb-3">

            <div>

                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#527A65]">

                    Operasional Hari Ini

                </p>

                <h2 class="mt-2 text-base font-medium text-[#183D32]">

                    {{ $dateLabel }}

                </h2>

            </div>

            <p class="text-[10px] text-[#74877B]">

                Terakhir diperbarui {{ $today->format('H:i') }} WIB

            </p>

        </div>

        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">

            @foreach ($dailyCards as $label => $value)

                <article class="flex min-h-[84px] flex-col items-center justify-center border border-[#D8E2DA] bg-white p-3 text-center">

                    <p class="text-[23px] font-bold text-[#174B38]">

                        {{ $number($value) }}

                    </p>

                    <p class="mt-2 text-[10px] text-[#74877B]">

                        {{ $label }}

                    </p>

                </article>

            @endforeach

        </div>

    </section>

    {{-- GRAPHICS AND STATISTICS --}}
    <section class="mt-6">

        <div class="mb-3 border-b border-[#D5DFD7] pb-3">

            <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#527A65]">

                Grafik &amp; Statistik

            </p>

            <h2 class="mt-2 text-base font-medium text-[#183D32]">

                Performa layanan

            </h2>

        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">

            {{-- TOP LEFT --}}
            <x-admin-dashboard-chart
                title="Pengunjung per Hari"
                :period="$monthPeriod"
                :labels="$dayLabels"
                :values="array_column($monthlyReport['daily'], 'pengunjung_checkin')"
                type="bar"
            />

            {{-- TOP MIDDLE --}}
            <x-admin-dashboard-chart
                title="Pengunjung per Bulan"
                :period="$today->format('Y')"
                :labels="$monthLabels"
                :values="array_values($monthlyVisitors)"
                type="line"
            />

            {{-- TOP RIGHT --}}
            <x-admin-dashboard-chart
                title="Grafik Pendapatan"
                :period="$monthPeriod"
                :labels="$dayLabels"
                :values="array_column($monthlyReport['daily'], 'total_pendapatan')"
                type="line"
                :money="true"
            />

            {{-- BOTTOM LEFT --}}
            <section class="min-w-0 border border-[#D8E2DA] bg-white p-3.5">

                <div class="flex min-h-[30px] flex-wrap items-start justify-between gap-1">

                    <h3 class="text-[11px] font-semibold text-[#173D30]">

                        Metode Pembayaran

                    </h3>

                    <span class="text-[9px] text-[#819387]">

                        {{ $monthPeriod }}

                    </span>

                </div>

                <div class="flex aspect-[420/145] items-center justify-center gap-5">

                    @if ($paymentCount > 0)

                        <svg
                            viewBox="0 0 120 120"
                            class="h-[100px] w-[100px] shrink-0"
                            role="img"
                            aria-label="Distribusi metode pembayaran"
                        >

                            @php
                                $offset = 0;
                                $circumference = 2 * pi() * 42;
                            @endphp

                            @foreach ($paymentColors as $method => $color)

                                @php
                                    $value = $paymentMethods[$method]['total_transaksi'];

                                    $length = $value
                                        / $paymentCount
                                        * $circumference;
                                @endphp

                                @if ($length > 0)

                                    <circle
                                        cx="60"
                                        cy="60"
                                        r="42"
                                        fill="none"
                                        stroke="{{ $color }}"
                                        stroke-width="20"
                                        stroke-dasharray="{{ $length }} {{ $circumference - $length }}"
                                        stroke-dashoffset="{{ -$offset }}"
                                        transform="rotate(-90 60 60)"
                                    />

                                @endif

                                @php
                                    $offset += $length;
                                @endphp

                            @endforeach

                        </svg>

                    @endif

                    <div class="space-y-3">

                        @foreach ($paymentColors as $method => $color)

                            @php
                                $count = $paymentMethods[$method]['total_transaksi'];

                                $percentage = $paymentCount > 0
                                    ? $count / $paymentCount * 100
                                    : 0;
                            @endphp

                            @if ($method !== 'other' || $count > 0)

                                <div class="flex items-center gap-2 text-[10px] font-semibold">

                                    <span class="h-2.5 w-2.5"
                                          style="background: {{ $color }}"></span>

                                    {{ $paymentLabels[$method] }}

                                    {{ number_format($percentage, 0, ',', '.') }}%

                                </div>

                            @endif

                        @endforeach

                    </div>

                </div>

            </section>

            {{-- BOTTOM MIDDLE --}}
            <x-admin-dashboard-chart
                title="Jenis Tiket Terpopuler"
                period="Seluruh periode"
                :labels="$popularTickets->pluck('ticket_name')->all()"
                :values="$popularTickets->pluck('quantity')->all()"
                type="bar"
            />

            {{-- BOTTOM RIGHT --}}
            <x-admin-dashboard-chart
                title="Reservasi Fasilitas"
                period="Seluruh periode"
                :labels="$popularFacilities->pluck('facility_name')->all()"
                :values="$popularFacilities->pluck('quantity')->all()"
                type="bar"
            />

        </div>

    </section>

    {{-- BOTTOM NAVIGATION --}}
    <div class="mt-4 flex flex-wrap gap-2 pb-6">

        <span
            aria-disabled="true"
            title="Modul belum tersedia"
            class="inline-flex items-center gap-1 border border-[#527A65] bg-white px-3 py-2.5 text-[11px] font-semibold text-[#82998A]"
        >

            Manajemen Transaksi

            <x-admin-icon name="arrow" class="h-3 w-3"/>

        </span>

        <span
            aria-disabled="true"
            title="Modul belum tersedia"
            class="inline-flex items-center gap-1 border border-[#527A65] bg-white px-3 py-2.5 text-[11px] font-semibold text-[#82998A]"
        >

            Monitoring Pendapatan

            <x-admin-icon name="arrow" class="h-3 w-3"/>

        </span>

        <a
            href="{{ route('admin.reports.index') }}"
            class="inline-flex items-center gap-1 border border-[#174B38] bg-white px-3 py-2.5 text-[11px] font-semibold text-[#174B38] hover:bg-[#EAF1EB]"
        >

            Laporan

            <x-admin-icon name="arrow" class="h-3 w-3"/>

        </a>

    </div>

</div>

@endsection