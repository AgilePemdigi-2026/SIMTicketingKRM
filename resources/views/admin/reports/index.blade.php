@extends('layouts.admin')

@section('title', 'Laporan Operasional - Kebun Raya Mangrove')

@section('content')

@php
    $formatNumber = static fn ($value) =>
        number_format($value, 0, ',', '.');

    $formatMoney = static function ($value) {
        [$whole, $fraction] = array_pad(
            explode('.', (string) $value, 2),
            2,
            '00'
        );

        $formatted = preg_replace(
            '/\B(?=(\d{3})+(?!\d))/',
            '.',
            $whole
        );

        return 'Rp '.$formatted
            .($fraction !== '00' ? ','.$fraction : '');
    };

    $formatDate = static fn ($value) =>
        \Carbon\CarbonImmutable::parse(
            $value,
            'Asia/Jakarta'
        )->locale('id')->translatedFormat('d F Y');

    $methodLabels = [
        'cash' => 'Cash',
        'qris' => 'QRIS',
        'other' => 'Lainnya',
    ];

    $channelLabels = [
        'online' => 'Online',
        'counter' => 'Loket',
        'other' => 'Lainnya',
    ];

    $metrics = [
        [
            'label' => 'Pesanan Tiket',
            'key' => 'transaksi_tiket',
            'money' => false,
            'description' => 'Pesanan dibuat dalam periode laporan.',
        ],
        [
            'label' => 'Pembayaran Tiket Lunas',
            'key' => 'pembayaran_lunas',
            'money' => false,
            'description' => 'Pembayaran tiket yang telah lunas.',
        ],
        [
            'label' => 'Pendapatan Tiket',
            'key' => 'pendapatan_tiket',
            'money' => true,
            'description' => 'Nominal pembayaran tiket lunas.',
        ],
        [
            'label' => 'E-Tiket Terbit',
            'key' => 'tiket_terbit',
            'money' => false,
            'description' => 'E-Tiket diterbitkan dalam periode.',
        ],
        [
            'label' => 'Pengunjung Check-in',
            'key' => 'pengunjung_checkin',
            'money' => false,
            'description' => 'E-Tiket yang telah digunakan.',
        ],
        [
            'label' => 'Reservasi Terjadwal',
            'key' => 'reservasi_terjadwal',
            'money' => false,
            'description' => 'Reservasi berdasarkan tanggal kunjungan, semua status.',
        ],
        [
            'label' => 'Reservasi Lunas',
            'key' => 'reservasi_lunas',
            'money' => false,
            'description' => 'Pembayaran reservasi yang telah lunas.',
        ],
        [
            'label' => 'Pendapatan Reservasi',
            'key' => 'pendapatan_reservasi',
            'money' => true,
            'description' => 'Nominal pembayaran reservasi lunas.',
        ],
    ];

    $dailyColumns = [
        ['key' => 'transaksi_tiket', 'label' => 'Pesanan Tiket', 'money' => false],
        ['key' => 'pembayaran_lunas', 'label' => 'Tiket Lunas', 'money' => false],
        ['key' => 'pendapatan_tiket', 'label' => 'Pendapatan Tiket', 'money' => true],
        ['key' => 'tiket_terbit', 'label' => 'Tiket Terbit', 'money' => false],
        ['key' => 'pengunjung_checkin', 'label' => 'Check-in', 'money' => false],
        ['key' => 'reservasi_terjadwal', 'label' => 'Reservasi Terjadwal', 'money' => false],
        ['key' => 'reservasi_lunas', 'label' => 'Reservasi Lunas', 'money' => false],
        ['key' => 'pendapatan_reservasi', 'label' => 'Pendapatan Reservasi', 'money' => true],
        ['key' => 'total_pendapatan', 'label' => 'Total Pendapatan', 'money' => true],
    ];

    $hasActivity =
        $summary['transaksi_tiket'] > 0
        || $summary['pembayaran_lunas'] > 0
        || $summary['tiket_terbit'] > 0
        || $summary['pengunjung_checkin'] > 0
        || $summary['reservasi_terjadwal'] > 0
        || $summary['reservasi_lunas'] > 0;
@endphp

<div class="mx-auto max-w-[1500px]">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6 border-b border-[#D4DED7] pb-6">

        <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
            Administrasi Kebun Raya Mangrove
        </p>

        <h1 class="text-2xl font-semibold text-[#173D30]">
            Laporan Operasional
        </h1>

        <p class="mt-2 text-sm leading-6 text-[#74877B]">
            Rekapitulasi transaksi, pendapatan, dan kunjungan
            Kebun Raya Mangrove Surabaya.
        </p>

    </div>

    {{-- PERIODE LAPORAN --}}
    <section class="mb-6 rounded-lg border border-[#DCE5DE] bg-white p-5">

        <div class="flex flex-wrap items-center justify-between gap-4">

            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-[#74877B]">
                    Periode Laporan
                </p>

                <p class="mt-2 text-lg font-bold text-[#173D30]">
                    {{ $formatDate($period['from']) }}
                    -
                    {{ $formatDate($period['until']) }}
                </p>

                <p class="mt-2 text-xs text-[#819187]">
                    {{ $formatNumber($period['days']) }} hari
                    | Zona waktu WIB
                </p>
            </div>

            <span class="rounded-sm bg-[#E9F4EC] px-3 py-2 text-xs font-semibold text-[#355D47]">
                Bulan Berjalan
            </span>

        </div>

        <div class="mt-4 border-t border-[#E2E9E4] pt-3 text-xs text-[#819187]">
            Data dihitung pada
            {{ \Carbon\CarbonImmutable::parse($generatedAt)->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
            WIB.
        </div>

    </section>

    {{-- TOTAL PENDAPATAN --}}
    <section class="mb-6 rounded-lg bg-[#174B38] p-6 text-white">

        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-white/75">
            Total Pendapatan Operasional
        </p>

        <p class="mt-3 break-words text-3xl font-bold sm:text-4xl">
            {{ $formatMoney($summary['total_pendapatan']) }}
        </p>

        <p class="mt-3 text-xs leading-6 text-white/75">
            Gabungan pendapatan tiket dan reservasi fasilitas
            dari pembayaran berstatus lunas dalam periode laporan.
        </p>

        <div class="mt-6 grid grid-cols-1 gap-4 border-t border-white/20 pt-5 sm:grid-cols-2">

            <div>
                <p class="text-xs text-white/70">Pendapatan Tiket</p>
                <p class="mt-2 break-words text-xl font-bold">
                    {{ $formatMoney($summary['pendapatan_tiket']) }}
                </p>
            </div>

            <div>
                <p class="text-xs text-white/70">Pendapatan Reservasi</p>
                <p class="mt-2 break-words text-xl font-bold">
                    {{ $formatMoney($summary['pendapatan_reservasi']) }}
                </p>
            </div>

        </div>

    </section>

    {{-- RINGKASAN OPERASIONAL --}}
    <section class="mb-6">

        <h2 class="mb-4 text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
            Ringkasan Operasional
        </h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            @foreach ($metrics as $metric)

                <div class="min-w-0 rounded-lg border border-[#DCE5DE] bg-white p-5">

                    <p class="text-xs font-medium text-[#74877B]">
                        {{ $metric['label'] }}
                    </p>

                    <p class="mt-3 break-words text-2xl font-bold text-[#173D30]">

                        @if ($metric['money'])
                            {{ $formatMoney($summary[$metric['key']]) }}
                        @else
                            {{ $formatNumber($summary[$metric['key']]) }}
                        @endif

                    </p>

                    <p class="mt-3 text-[11px] leading-5 text-[#819187]">
                        {{ $metric['description'] }}
                    </p>

                </div>

            @endforeach

        </div>

    </section>

    @if (! $hasActivity)

        <div class="mb-6 border border-[#D5E5DA] bg-[#EFF6F0] px-5 py-4 text-sm text-[#355D47]">
            <p class="font-semibold">
                Belum ada aktivitas operasional pada periode ini.
            </p>

            <p class="mt-2 text-xs leading-5">
                Seluruh indikator tetap ditampilkan dengan nilai nol.
            </p>
        </div>

    @endif

    {{-- METODE PEMBAYARAN --}}
    <section class="mb-6 overflow-hidden border border-[#DCE5DE] bg-white">

        <div class="border-b border-[#E2E9E4] px-5 py-5">

            <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                Rincian Metode Pembayaran
            </h2>

            <p class="mt-1 text-xs leading-5 text-[#819187]">
                Pembayaran tiket dan reservasi berdasarkan metode pembayaran.
            </p>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[950px] border-collapse text-xs">

                <thead class="bg-[#E9F0EB] text-[#4A695A]">
                    <tr>
                        <th scope="col" class="px-4 py-4 text-left font-bold">Metode</th>
                        <th scope="col" class="px-4 py-4 text-right font-bold">Transaksi Tiket</th>
                        <th scope="col" class="px-4 py-4 text-right font-bold">Transaksi Reservasi</th>
                        <th scope="col" class="px-4 py-4 text-right font-bold">Total Transaksi</th>
                        <th scope="col" class="px-4 py-4 text-right font-bold">Pendapatan Tiket</th>
                        <th scope="col" class="px-4 py-4 text-right font-bold">Pendapatan Reservasi</th>
                        <th scope="col" class="px-4 py-4 text-right font-bold">Total Pendapatan</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">

                    @foreach ($paymentMethods as $method => $data)

                        <tr class="hover:bg-[#F8FAF8]">

                            <th scope="row" class="px-4 py-4 text-left font-semibold text-[#173D30]">
                                {{ $methodLabels[$method] ?? ucfirst($method) }}
                            </th>

                            <td class="px-4 py-4 text-right">
                                {{ $formatNumber($data['transaksi_tiket']) }}
                            </td>

                            <td class="px-4 py-4 text-right">
                                {{ $formatNumber($data['transaksi_reservasi']) }}
                            </td>

                            <td class="px-4 py-4 text-right">
                                {{ $formatNumber($data['total_transaksi']) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right">
                                {{ $formatMoney($data['pendapatan_tiket']) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right">
                                {{ $formatMoney($data['pendapatan_reservasi']) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right font-semibold text-[#173D30]">
                                {{ $formatMoney($data['total_pendapatan']) }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

                <tfoot class="border-t border-[#CBD9CF] bg-[#EFF6F0] font-bold text-[#173D30]">

                    <tr>
                        <th scope="row" class="px-4 py-4 text-left">Total</th>

                        <td class="px-4 py-4 text-right">
                            {{ $formatNumber($summary['pembayaran_lunas']) }}
                        </td>

                        <td class="px-4 py-4 text-right">
                            {{ $formatNumber($summary['reservasi_lunas']) }}
                        </td>

                        <td class="px-4 py-4 text-right">
                            {{ $formatNumber($summary['pembayaran_lunas'] + $summary['reservasi_lunas']) }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right">
                            {{ $formatMoney($summary['pendapatan_tiket']) }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right">
                            {{ $formatMoney($summary['pendapatan_reservasi']) }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right">
                            {{ $formatMoney($summary['total_pendapatan']) }}
                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

    </section>

    {{-- KANAL PENJUALAN --}}
    <section class="mb-6 overflow-hidden border border-[#DCE5DE] bg-white">

        <div class="border-b border-[#E2E9E4] px-5 py-5">

            <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                Rincian Kanal Penjualan Tiket
            </h2>

            <p class="mt-1 text-xs leading-5 text-[#819187]">
                Penjualan tiket lunas berdasarkan kanal Online dan Loket.
                Reservasi fasilitas tidak termasuk dalam tabel ini.
            </p>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[500px] border-collapse text-xs">

                <thead class="bg-[#E9F0EB] text-[#4A695A]">
                    <tr>
                        <th scope="col" class="px-4 py-4 text-left font-bold">Kanal Penjualan</th>
                        <th scope="col" class="px-4 py-4 text-right font-bold">Transaksi Lunas</th>
                        <th scope="col" class="px-4 py-4 text-right font-bold">Pendapatan Tiket</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">

                    @foreach ($salesChannels as $channel => $data)

                        <tr class="hover:bg-[#F8FAF8]">

                            <th scope="row" class="px-4 py-4 text-left font-semibold text-[#173D30]">
                                {{ $channelLabels[$channel] ?? ucfirst($channel) }}
                            </th>

                            <td class="px-4 py-4 text-right">
                                {{ $formatNumber($data['transaksi_lunas']) }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-right">
                                {{ $formatMoney($data['pendapatan_tiket']) }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

                <tfoot class="border-t border-[#CBD9CF] bg-[#EFF6F0] font-bold text-[#173D30]">

                    <tr>
                        <th scope="row" class="px-4 py-4 text-left">Total</th>

                        <td class="px-4 py-4 text-right">
                            {{ $formatNumber($summary['pembayaran_lunas']) }}
                        </td>

                        <td class="whitespace-nowrap px-4 py-4 text-right">
                            {{ $formatMoney($summary['pendapatan_tiket']) }}
                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

    </section>

    {{-- REKAP HARIAN --}}
    <section class="mb-6 overflow-hidden border border-[#DCE5DE] bg-white">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#E2E9E4] px-5 py-5">

            <div>

                <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                    Rekapitulasi Harian
                </h2>

                <p class="mt-1 text-xs leading-5 text-[#819187]">
                    Data dikelompokkan berdasarkan tanggal WIB.
                    Tanggal tanpa aktivitas tetap ditampilkan.
                </p>

            </div>

            <span class="rounded-sm bg-[#E9F0EB] px-3 py-2 text-xs font-medium text-[#355D47]">
                {{ $formatNumber(count($daily)) }} hari
            </span>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full min-w-[1450px] border-collapse text-xs">

                <thead class="bg-[#E9F0EB] text-[#4A695A]">

                    <tr>

                        <th scope="col" class="whitespace-nowrap px-4 py-4 text-left font-bold">
                            Tanggal
                        </th>

                        @foreach ($dailyColumns as $column)

                            <th scope="col" class="px-4 py-4 text-right font-bold">
                                {{ $column['label'] }}
                            </th>

                        @endforeach

                    </tr>

                </thead>

                <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">

                    @foreach ($daily as $day)

                        <tr class="hover:bg-[#F8FAF8]">

                            <th scope="row" class="whitespace-nowrap px-4 py-4 text-left font-semibold text-[#173D30]">
                                {{ $formatDate($day['date']) }}
                            </th>

                            @foreach ($dailyColumns as $column)

                                <td class="whitespace-nowrap px-4 py-4 text-right {{ $column['key'] === 'total_pendapatan' ? 'font-semibold text-[#173D30]' : '' }}">

                                    @if ($column['money'])
                                        {{ $formatMoney($day[$column['key']]) }}
                                    @else
                                        {{ $formatNumber($day[$column['key']]) }}
                                    @endif

                                </td>

                            @endforeach

                        </tr>

                    @endforeach

                </tbody>

                <tfoot class="border-t border-[#CBD9CF] bg-[#EFF6F0] font-bold text-[#173D30]">

                    <tr>

                        <th scope="row" class="px-4 py-4 text-left">
                            Total
                        </th>

                        @foreach ($dailyColumns as $column)

                            <td class="whitespace-nowrap px-4 py-4 text-right">

                                @if ($column['money'])
                                    {{ $formatMoney($summary[$column['key']]) }}
                                @else
                                    {{ $formatNumber($summary[$column['key']]) }}
                                @endif

                            </td>

                        @endforeach

                    </tr>

                </tfoot>

            </table>

        </div>

    </section>

    {{-- CATATAN LAPORAN --}}
    <section class="border border-[#D5E5DA] bg-[#EFF6F0] p-5">

        <h2 class="text-xs font-bold uppercase tracking-wider text-[#355D47]">
            Catatan Perhitungan
        </h2>

        <ul class="mt-3 list-disc space-y-2 pl-4 text-xs leading-6 text-[#527A65]">
            <li>Pesanan tiket dihitung berdasarkan waktu pembuatan pesanan.</li>
            <li>Pendapatan dihitung berdasarkan waktu pembayaran lunas, bukan tanggal kunjungan.</li>
            <li>E-Tiket terbit dihitung berdasarkan waktu penerbitan tiket.</li>
            <li>Kunjungan aktual dihitung berdasarkan E-Tiket yang telah digunakan untuk check-in.</li>
            <li>Reservasi terjadwal dihitung berdasarkan tanggal kunjungan dan mencakup semua status administratif.</li>
            <li>Total pendapatan merupakan pendapatan bruto, sebelum biaya gateway atau pengembalian dana.</li>
            <li>Seluruh pengelompokan tanggal menggunakan zona waktu WIB.</li>
        </ul>

    </section>

</div>

@endsection
