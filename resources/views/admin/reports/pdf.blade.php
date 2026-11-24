<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <title>Laporan Operasional Kebun Raya Mangrove</title>

    <style>
        @page {
            margin: 25px 30px 42px 30px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #243D32;
            line-height: 1.35;
        }

        .header {
            border-bottom: 2px solid #174B38;
            padding-bottom: 9px;
            margin-bottom: 12px;
        }

        .institution {
            font-size: 15px;
            font-weight: bold;
            color: #174B38;
        }

        .subtitle {
            font-size: 10px;
            color: #527A65;
        }

        .system-name {
            font-size: 8px;
            color: #718079;
            margin-top: 3px;
        }

        .document-title {
            text-align: center;
            margin: 10px 0 4px;
            font-size: 17px;
            font-weight: bold;
            color: #174B38;
        }

        .document-period {
            text-align: center;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .document-meta {
            text-align: center;
            font-size: 8px;
            color: #718079;
            margin-bottom: 12px;
        }

        .revenue-box {
            background-color: #E9F0EB;
            border: 1px solid #CBD9CF;
            padding: 10px 14px;
            margin-bottom: 12px;
        }

        .revenue-label {
            font-size: 9px;
            font-weight: bold;
            color: #527A65;
            text-transform: uppercase;
        }

        .revenue-value {
            font-size: 22px;
            font-weight: bold;
            color: #174B38;
            margin-top: 2px;
        }

        .revenue-description {
            font-size: 8px;
            color: #527A65;
        }

        h2 {
            margin: 12px 0 6px;
            font-size: 11px;
            color: #174B38;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #CBD9CF;
            padding: 5px;
            vertical-align: middle;
            overflow-wrap: break-word;
        }

        th {
            background-color: #174B38;
            color: #FFFFFF;
            font-weight: bold;
            font-size: 8px;
            text-align: left;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        .alternate {
            background-color: #F4F8F4;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .metric-label {
            background-color: #F4F8F4;
            color: #527A65;
        }

        .metric-value {
            font-weight: bold;
            color: #174B38;
        }

        .total-row {
            background-color: #E9F0EB;
            color: #174B38;
            font-weight: bold;
        }

        .description {
            font-size: 8px;
            color: #718079;
            margin: 4px 0 6px;
        }

        .daily-table {
            font-size: 8px;
        }

        .daily-table th {
            font-size: 7.5px;
            padding: 5px 3px;
            text-align: center;
        }

        .daily-table td {
            padding: 4px 3px;
        }

        .notes {
            background-color: #F4F8F4;
            border: 1px solid #CBD9CF;
            padding: 8px 10px;
            margin-top: 12px;
            page-break-inside: avoid;
            font-size: 8px;
        }

        .notes-title {
            font-weight: bold;
            color: #174B38;
            margin-bottom: 4px;
        }

        .notes ul {
            margin: 0;
            padding-left: 14px;
        }

        .notes li {
            margin-bottom: 2px;
        }

        .empty-state {
            border: 1px solid #CBD9CF;
            background-color: #F4F8F4;
            color: #527A65;
            padding: 6px 10px;
            margin-bottom: 8px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

@php
    $period = $report['period'];
    $summary = $report['summary'];
    $paymentMethods = $report['payment_methods'];
    $salesChannels = $report['sales_channels'];
    $daily = $report['daily'];

    $formatNumber = static fn ($value) =>
        number_format($value, 0, ',', '.');

    $formatMoney = static function ($value) {
        [$whole, $fraction] = array_pad(
            explode('.', (string) $value, 2),
            2,
            '00'
        );

        return 'Rp '.preg_replace(
            '/\B(?=(\d{3})+(?!\d))/',
            '.',
            $whole
        ).','.str_pad($fraction, 2, '0');
    };

    $formatDate = static fn ($value) =>
        \Carbon\CarbonImmutable::parse(
            $value,
            'Asia/Jakarta'
        )->locale('id')->translatedFormat('d F Y');

    $generatedAt = \Carbon\CarbonImmutable::parse(
        $report['generated_at']
    )->timezone('Asia/Jakarta')->format('d/m/Y H:i:s');

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

    $dailyColumns = [
        'transaksi_tiket' => 'Pesanan Tiket',
        'pembayaran_lunas' => 'Tiket Lunas',
        'pendapatan_tiket' => 'Pendapatan Tiket',
        'tiket_terbit' => 'Tiket Terbit',
        'pengunjung_checkin' => 'Check-in',
        'reservasi_terjadwal' => 'Reservasi Terjadwal',
        'reservasi_lunas' => 'Reservasi Lunas',
        'pendapatan_reservasi' => 'Pendapatan Reservasi',
        'total_pendapatan' => 'Total Pendapatan',
    ];

    $moneyKeys = [
        'pendapatan_tiket',
        'pendapatan_reservasi',
        'total_pendapatan',
    ];

    $formatMetric = static fn ($key, $value) =>
        in_array($key, $moneyKeys, true)
            ? $formatMoney($value)
            : $formatNumber($value);

    $hasActivity =
        $summary['transaksi_tiket'] > 0
        || $summary['pembayaran_lunas'] > 0
        || $summary['tiket_terbit'] > 0
        || $summary['pengunjung_checkin'] > 0
        || $summary['reservasi_terjadwal'] > 0
        || $summary['reservasi_lunas'] > 0;

    $dailyPages = array_chunk($daily, 16);
@endphp

{{-- IDENTITAS LAPORAN --}}
<div class="header">
    <div class="institution">
        KEBUN RAYA MANGROVE SURABAYA
    </div>

    <div class="subtitle">
        BRIDA Kota Surabaya
    </div>

    <div class="system-name">
        Sistem Informasi Manajemen Ticketing dan Monitoring Kunjungan
    </div>
</div>

<div class="document-title">
    LAPORAN OPERASIONAL
</div>

<div class="document-period">
    Periode {{ $formatDate($period['from']) }}
    s.d. {{ $formatDate($period['until']) }}
</div>

<div class="document-meta">
    {{ $period['days'] }} hari |
    Zona waktu WIB |
    Data dihitung: {{ $generatedAt }} WIB
</div>

{{-- TOTAL PENDAPATAN --}}
<div class="revenue-box">

    <div class="revenue-label">
        Total Pendapatan Operasional
    </div>

    <div class="revenue-value">
        {{ $formatMoney($summary['total_pendapatan']) }}
    </div>

    <div class="revenue-description">
        Gabungan pendapatan tiket dan reservasi fasilitas
        dari pembayaran lunas dalam periode laporan.
    </div>

</div>

@if (! $hasActivity)
    <div class="empty-state">
        Belum ada aktivitas operasional pada periode ini.
        Seluruh indikator ditampilkan dengan nilai nol.
    </div>
@endif

{{-- RINGKASAN OPERASIONAL --}}
<h2>I. Ringkasan Operasional</h2>

<table>

    <colgroup>
        <col style="width: 28%">
        <col style="width: 22%">
        <col style="width: 28%">
        <col style="width: 22%">
    </colgroup>

    <tbody>

        <tr>
            <td class="metric-label">Pesanan Tiket</td>
            <td class="metric-value right">
                {{ $formatNumber($summary['transaksi_tiket']) }}
            </td>

            <td class="metric-label">Pengunjung Check-in</td>
            <td class="metric-value right">
                {{ $formatNumber($summary['pengunjung_checkin']) }}
            </td>
        </tr>

        <tr>
            <td class="metric-label">Pembayaran Tiket Lunas</td>
            <td class="metric-value right">
                {{ $formatNumber($summary['pembayaran_lunas']) }}
            </td>

            <td class="metric-label">Reservasi Terjadwal</td>
            <td class="metric-value right">
                {{ $formatNumber($summary['reservasi_terjadwal']) }}
            </td>
        </tr>

        <tr>
            <td class="metric-label">Pendapatan Tiket</td>
            <td class="metric-value right">
                {{ $formatMoney($summary['pendapatan_tiket']) }}
            </td>

            <td class="metric-label">Reservasi Lunas</td>
            <td class="metric-value right">
                {{ $formatNumber($summary['reservasi_lunas']) }}
            </td>
        </tr>

        <tr>
            <td class="metric-label">E-Tiket Terbit</td>
            <td class="metric-value right">
                {{ $formatNumber($summary['tiket_terbit']) }}
            </td>

            <td class="metric-label">Pendapatan Reservasi</td>
            <td class="metric-value right">
                {{ $formatMoney($summary['pendapatan_reservasi']) }}
            </td>
        </tr>

    </tbody>

</table>

{{-- METODE PEMBAYARAN --}}
<h2>II. Rincian Metode Pembayaran</h2>

<table>

    <colgroup>
        <col style="width: 13%">
        <col style="width: 12%">
        <col style="width: 12%">
        <col style="width: 12%">
        <col style="width: 17%">
        <col style="width: 17%">
        <col style="width: 17%">
    </colgroup>

    <thead>
        <tr>
            <th>Metode</th>
            <th class="right">Transaksi Tiket</th>
            <th class="right">Transaksi Reservasi</th>
            <th class="right">Total Transaksi</th>
            <th class="right">Pendapatan Tiket</th>
            <th class="right">Pendapatan Reservasi</th>
            <th class="right">Total Pendapatan</th>
        </tr>
    </thead>

    <tbody>

        @foreach ($paymentMethods as $method => $data)

            <tr class="{{ $loop->even ? 'alternate' : '' }}">

                <td>{{ $methodLabels[$method] ?? $method }}</td>

                <td class="right">
                    {{ $formatNumber($data['transaksi_tiket']) }}
                </td>

                <td class="right">
                    {{ $formatNumber($data['transaksi_reservasi']) }}
                </td>

                <td class="right">
                    {{ $formatNumber($data['total_transaksi']) }}
                </td>

                <td class="right">
                    {{ $formatMoney($data['pendapatan_tiket']) }}
                </td>

                <td class="right">
                    {{ $formatMoney($data['pendapatan_reservasi']) }}
                </td>

                <td class="right">
                    {{ $formatMoney($data['total_pendapatan']) }}
                </td>

            </tr>

        @endforeach

        <tr class="total-row">

            <td>Total</td>

            <td class="right">
                {{ $formatNumber($summary['pembayaran_lunas']) }}
            </td>

            <td class="right">
                {{ $formatNumber($summary['reservasi_lunas']) }}
            </td>

            <td class="right">
                {{ $formatNumber(
                    $summary['pembayaran_lunas']
                    + $summary['reservasi_lunas']
                ) }}
            </td>

            <td class="right">
                {{ $formatMoney($summary['pendapatan_tiket']) }}
            </td>

            <td class="right">
                {{ $formatMoney($summary['pendapatan_reservasi']) }}
            </td>

            <td class="right">
                {{ $formatMoney($summary['total_pendapatan']) }}
            </td>

        </tr>

    </tbody>

</table>

{{-- KANAL PENJUALAN --}}
<h2>III. Rincian Kanal Penjualan Tiket</h2>

<table>

    <colgroup>
        <col style="width: 40%">
        <col style="width: 25%">
        <col style="width: 35%">
    </colgroup>

    <thead>
        <tr>
            <th>Kanal Penjualan</th>
            <th class="right">Transaksi Lunas</th>
            <th class="right">Pendapatan Tiket</th>
        </tr>
    </thead>

    <tbody>

        @foreach ($salesChannels as $channel => $data)

            <tr class="{{ $loop->even ? 'alternate' : '' }}">

                <td>
                    {{ $channelLabels[$channel] ?? $channel }}
                </td>

                <td class="right">
                    {{ $formatNumber($data['transaksi_lunas']) }}
                </td>

                <td class="right">
                    {{ $formatMoney($data['pendapatan_tiket']) }}
                </td>

            </tr>

        @endforeach

        <tr class="total-row">

            <td>Total</td>

            <td class="right">
                {{ $formatNumber($summary['pembayaran_lunas']) }}
            </td>

            <td class="right">
                {{ $formatMoney($summary['pendapatan_tiket']) }}
            </td>

        </tr>

    </tbody>

</table>

{{-- REKAPITULASI HARIAN --}}
@foreach ($dailyPages as $days)

    <div class="page-break">

        <div class="header">

            <div class="institution">
                KEBUN RAYA MANGROVE SURABAYA
            </div>

            <div class="subtitle">
                Laporan Operasional |
                {{ $formatDate($period['from']) }}
                s.d. {{ $formatDate($period['until']) }}
            </div>

        </div>

        <h2>IV. Rekapitulasi Harian</h2>

        <div class="description">
            Bagian {{ $loop->iteration }} dari {{ count($dailyPages) }}.
            Seluruh tanggal menggunakan WIB.
            Tanggal tanpa aktivitas ditampilkan dengan nilai nol.
        </div>

        <table class="daily-table">

            <colgroup>
                <col style="width: 10%">
                <col style="width: 8%">
                <col style="width: 8%">
                <col style="width: 14%">
                <col style="width: 7%">
                <col style="width: 7%">
                <col style="width: 9%">
                <col style="width: 9%">
                <col style="width: 14%">
                <col style="width: 14%">
            </colgroup>

            <thead>

                <tr>

                    <th>Tanggal</th>

                    @foreach ($dailyColumns as $label)
                        <th>{{ $label }}</th>
                    @endforeach

                </tr>

            </thead>

            <tbody>

                @foreach ($days as $day)

                    <tr class="{{ $loop->even ? 'alternate' : '' }}">

                        <td class="center">
                            {{ \Carbon\CarbonImmutable::parse(
                                $day['date']
                            )->format('d/m/Y') }}
                        </td>

                        @foreach ($dailyColumns as $key => $label)

                            <td class="right">
                                {{ $formatMetric($key, $day[$key]) }}
                            </td>

                        @endforeach

                    </tr>

                @endforeach

                @if ($loop->last)

                    <tr class="total-row">

                        <td>Total</td>

                        @foreach ($dailyColumns as $key => $label)

                            <td class="right">
                                {{ $formatMetric($key, $summary[$key]) }}
                            </td>

                        @endforeach

                    </tr>

                @endif

            </tbody>

        </table>

        @if ($loop->last)

            <div class="notes">

                <div class="notes-title">
                    CATATAN PERHITUNGAN
                </div>

                <ul>
                    <li>
                        Pesanan tiket dihitung berdasarkan waktu
                        pembuatan pesanan.
                    </li>

                    <li>
                        Pendapatan dihitung berdasarkan waktu
                        pembayaran lunas, bukan tanggal kunjungan.
                    </li>

                    <li>
                        E-Tiket terbit dihitung berdasarkan waktu
                        penerbitan tiket.
                    </li>

                    <li>
                        Kunjungan aktual dihitung berdasarkan
                        E-Tiket yang digunakan untuk check-in.
                    </li>

                    <li>
                        Reservasi terjadwal dihitung berdasarkan
                        tanggal kunjungan dan mencakup seluruh
                        status administratif.
                    </li>

                    <li>
                        Pendapatan merupakan pendapatan bruto,
                        sebelum biaya gateway atau pengembalian dana.
                    </li>

                    <li>
                        Seluruh pengelompokan tanggal menggunakan WIB.
                    </li>
                </ul>

            </div>

        @endif

    </div>

@endforeach

</body>
</html>
