<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $reservasi->invoice_number }}</title>

    <style>
        @page {
            margin: 32px 38px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #193B31;
            line-height: 1.6;
        }

        .header {
            border-bottom: 3px solid #A5C969;
            padding-bottom: 18px;
            margin-bottom: 26px;
        }

        .brand {
            color: #07513D;
            font-size: 23px;
            font-weight: bold;
            margin: 0;
        }

        .subtitle {
            color: #74857E;
            font-size: 10px;
            margin: 3px 0 0;
        }

        h1 {
            font-size: 19px;
            margin: 0 0 6px;
            color: #193B31;
        }

        h2 {
            font-size: 12px;
            margin: 25px 0 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #D6E0D9;
            text-transform: uppercase;
        }

        .muted {
            color: #74857E;
        }

        .status {
            display: inline-block;
            color: #287149;
            background: #E8F3E6;
            font-size: 11px;
            font-weight: bold;
            padding: 5px 12px;
            margin-top: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            width: 50%;
            vertical-align: top;
            padding: 8px 12px 12px 0;
        }

        .field-label {
            color: #74857E;
            font-size: 10px;
            margin-bottom: 4px;
        }

        .field-value {
            font-weight: bold;
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        .total {
            border-top: 2px solid #A5C969;
            margin-top: 25px;
            padding-top: 18px;
        }

        .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #07513D;
            text-align: right;
        }

        .notice {
            margin-top: 30px;
            padding: 13px 16px;
            background: #F0F7F0;
            color: #557066;
            font-size: 10px;
        }

        .footer {
            border-top: 1px solid #D6E0D9;
            margin-top: 35px;
            padding-top: 12px;
            text-align: center;
            color: #74857E;
            font-size: 9px;
        }
    </style>
</head>

<body>
    @php
        $tanggalTerbit = (
            $reservasi->payment?->paid_at
            ?? $reservasi->created_at
        )->copy()->timezone('Asia/Jakarta')
            ->locale('id')
            ->translatedFormat('d F Y, H:i');

        $tanggalKunjungan = $reservasi->visit_date
            ->locale('id')
            ->translatedFormat('d F Y');

        $jamMulai = substr((string) $sesi->jam_mulai, 0, 5);
        $jamSelesai = substr((string) $sesi->jam_selesai, 0, 5);
    @endphp

    <div class="header">
        <p class="brand">MangroTix</p>
        <p class="subtitle">
            Kebun Raya Mangrove Surabaya
        </p>
    </div>

    <h1>INVOICE RESERVASI</h1>

    <p>
        <strong>{{ $reservasi->invoice_number }}</strong>
    </p>

    <p class="muted">
        Tanggal Pembayaran: {{ $tanggalTerbit }} WIB
    </p>

    <div class="status">
        PEMBAYARAN LUNAS
    </div>

    <h2>Informasi Pemesan</h2>

    <table class="details">
        <tr>
            <td>
                <div class="field-label">Nama Pemesan</div>
                <div class="field-value">
                    {{ $reservasi->customer_name }}
                </div>
            </td>

            <td>
                <div class="field-label">Nomor Invoice</div>
                <div class="field-value">
                    {{ $reservasi->invoice_number }}
                </div>
            </td>
        </tr>
    </table>

    <h2>Detail Reservasi</h2>

    <table class="details">
        <tr>
            <td>
                <div class="field-label">Fasilitas</div>
                <div class="field-value">
                    {{ $reservasi->facility_name }}
                </div>
            </td>

            <td>
                <div class="field-label">Tanggal Penggunaan</div>
                <div class="field-value">
                    {{ $tanggalKunjungan }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="field-label">Waktu Penggunaan</div>
                <div class="field-value">
                    {{ $jamMulai }} - {{ $jamSelesai }} WIB
                </div>
            </td>

            <td>
                <div class="field-label">Jumlah Peserta</div>
                <div class="field-value">
                    {{ $reservasi->participant_count }} peserta
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="field-label">Metode Pembayaran</div>
                <div class="field-value">
                    QRIS (Midtrans Sandbox)
                </div>
            </td>

            <td>
                <div class="field-label">Harga per Peserta</div>
                <div class="field-value">
                    Rp {{ number_format(
                        (float) $reservasi->unit_price,
                        0, ',', '.'
                    ) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="total">
        <table>
            <tr>
                <td>
                    <strong>Total Biaya Reservasi</strong>
                    <div class="muted">
                        {{ $reservasi->participant_count }} peserta
                        × Rp {{ number_format(
                            (float) $reservasi->unit_price,
                            0, ',', '.'
                        ) }}
                    </div>
                </td>

                <td class="total-amount">
                    Rp {{ number_format(
                        (float) $reservasi->total_amount,
                        0, ',', '.'
                    ) }}
                </td>
            </tr>
        </table>
    </div>

    <div class="notice">
        Pembayaran reservasi telah terverifikasi Lunas.
        Persetujuan administratif reservasi tetap mengikuti
        proses pengelolaan oleh Admin.
    </div>

    <div class="footer">
        Invoice ini diterbitkan berdasarkan data reservasi
        dan pembayaran yang tercatat dalam sistem MangroTix.
    </div>
</body>
</html>