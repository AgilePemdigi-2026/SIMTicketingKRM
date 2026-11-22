<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Tiket {{ $order->code }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #173C30;
            margin: 0;
        }

        .ticket-page {
            width: 100%;
        }

        .ticket-card {
            width: 430px;
            margin: 15px auto;
            padding: 25px;
            border: 1px solid #BCD0C1;
        }

        .header {
            width: 100%;
            border-bottom: 1px dashed #BCD0C1;
            padding-bottom: 20px;
        }

        .brand {
            font-size: 17px;
            font-weight: bold;
            color: #07513D;
        }

        .brand-subtitle {
            font-size: 9px;
            color: #789084;
            letter-spacing: 1px;
        }

        .badge {
            background: #E3F0E3;
            color: #237246;
            padding: 7px 12px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }

        .qr-area {
            text-align: center;
            padding: 25px 0 15px;
        }

        .qr-area img {
            width: 220px;
            height: 220px;
        }

        .transaction {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px dashed #BCD0C1;
        }

        .label {
            color: #819187;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .value {
            font-size: 11px;
            font-weight: bold;
            word-wrap: break-word;
        }

        .details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .details td {
            width: 50%;
            vertical-align: top;
            padding: 10px 8px 10px 0;
        }

        .note {
            margin-top: 20px;
            padding: 15px 10px;
            background: #F0F7F0;
            text-align: center;
            font-size: 10px;
            color: #61796C;
            line-height: 1.7;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 9px;
            color: #819187;
        }
    </style>
</head>

<body>
@foreach ($order->issuedTickets as $ticket)
    @php
        $kategori = match (
            strtolower((string) $ticket->visitor->age_category)
        ) {
            'adult' => 'Dewasa',
            'child' => 'Anak',
            default => ucfirst(
                (string) $ticket->visitor->age_category
            ),
        };
    @endphp

    <div
        class="ticket-page"
        @if (!$loop->last)
            style="page-break-after: always;"
        @endif
    >
        <div class="ticket-card">

            <!-- Identitas MangroTix -->
            <table class="header">
                <tr>
                    <td>
                        <div class="brand">
                            KEBUN RAYA
                        </div>
                        <div class="brand-subtitle">
                            MANGROVE SURABAYA
                        </div>
                    </td>

                    <td style="text-align:right;">
                        <span class="badge">
                            {{ strtoupper($ticket->status) }}
                        </span>
                    </td>
                </tr>
            </table>

            <!-- QR Code Asli -->
            <div class="qr-area">
                <img
                    src="{{ $ticket->qr_code_data_uri }}"
                    alt="QR Code E-Tiket"
                >
            </div>

            <!-- Nomor Pesanan -->
            <div class="transaction">
                <div class="label">
                    Nomor Pesanan
                </div>

                <div class="value">
                    {{ $order->code }}
                </div>
            </div>

            <!-- Detail Pengunjung -->
            <table class="details">
                <tr>
                    <td>
                        <div class="label">
                            Nama Pengunjung
                        </div>
                        <div class="value">
                            {{ $ticket->visitor->name }}
                        </div>
                    </td>

                    <td>
                        <div class="label">
                            Kategori Pengunjung
                        </div>
                        <div class="value">
                            {{ $kategori }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="label">
                            Tanggal Kunjungan
                        </div>
                        <div class="value">
                            {{ $order->visit_date->format('d-m-Y') }}
                        </div>
                    </td>

                    <td>
                        <div class="label">
                            Jenis Tiket
                        </div>
                        <div class="value">
                            {{ $ticket->item->ticket_name }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="label">
                            Kode E-Tiket
                        </div>
                        <div class="value">
                            {{ $ticket->ticket_code }}
                        </div>
                    </td>

                    <td>
                        <div class="label">
                            Status Pembayaran
                        </div>
                        <div class="value">
                            Lunas
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="label">
                            Metode Pembayaran
                        </div>
                        <div class="value">
                            {{ strtoupper($order->payment?->method ?? '-') }}
                        </div>
                    </td>

                    <td>
                        <div class="label">
                            Tiket Ke
                        </div>
                        <div class="value">
                            {{ $loop->iteration }}
                            dari {{ $order->issuedTickets->count() }}
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Informasi Penggunaan -->
            <div class="note">
                Tunjukkan QR Code ini kepada petugas
                saat memasuki Kebun Raya Mangrove Surabaya.

                <div style="margin-top:6px;">
                    E-Tiket berlaku sesuai tanggal kunjungan
                    dan hanya dapat digunakan satu kali.
                </div>
            </div>

            <div class="footer">
                E-Tiket resmi sistem MangroTix
            </div>

        </div>
    </div>
@endforeach
</body>
</html>
