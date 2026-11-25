<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Pendapatan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #26352d; }
        h2 { margin-bottom: 5px; }
        p { color: #66756c; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #dce5df; padding: 8px; text-align: left; }
        th { background: #edf5ef; }
        .summary { width: 100%; }
        .summary td { width: 33.33%; }
    </style>
</head>
<body>
    <h2>Monitoring Pendapatan</h2>
    <p>Periode: {{ $period['from'] ?? '-' }} sampai {{ $period['until'] ?? '-' }}</p>

    <table class="summary">
        <tr>
            <th>Total Transaksi</th>
            <th>Total Tiket</th>
            <th>Total Pendapatan</th>
        </tr>
        <tr>
            <td>{{ number_format($summary['total_transaksi'] ?? 0, 0, ',', '.') }}</td>
            <td>{{ number_format($summary['total_tiket'] ?? 0, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Transaksi Batal</th>
            <th>Belum Dibayar</th>
            <th>Metode Pembayaran</th>
        </tr>
        <tr>
            <td>{{ $summary['transaksi_batal'] ?? $summary['transaksi_dibatalkan'] ?? 0 }}</td>
            <td>{{ $summary['transaksi_belum_dibayar'] ?? $summary['transaksi_unpaid'] ?? 0 }}</td>
            <td>QRIS dan Cash</td>
        </tr>
    </table>

    <h3>Tren Pendapatan dan Pengunjung</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pendapatan</th>
                <th>Pengunjung</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daily_revenue ?? [] as $index => $item)
                <tr>
                    <td>{{ $item['date'] }}</td>
                    <td>Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
                    <td>{{ $daily_visitors[$index]['visitors'] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
