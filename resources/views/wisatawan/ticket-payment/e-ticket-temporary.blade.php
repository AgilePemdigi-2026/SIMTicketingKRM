<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Tiket Sementara - Kebun Raya Mangrove Surabaya</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px 20px;
            background: #FAFBF7;
            color: #173F34;
            font-family: Arial, sans-serif;
        }

        .page {
            max-width: 580px;
            margin: 0 auto;
        }

        .page-title {
            text-align: center;
            font-size: 28px;
            margin: 0;
        }

        .title-line {
            width: 75px;
            height: 3px;
            background: #A3CC76;
            margin: 15px auto;
        }

        .page-subtitle {
            color: #72887E;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .ticket-card {
            padding: 28px;
            border: 1px solid #C9D9CD;
            background: #FFFFFF;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding-bottom: 22px;
            border-bottom: 1px dashed #C9D9CD;
        }

        .brand-name {
            font-weight: bold;
            letter-spacing: 2px;
            font-size: 15px;
        }

        .brand-subtitle {
            font-size: 10px;
            letter-spacing: 1px;
            color: #81988B;
            margin-top: 5px;
        }

        .status-badge {
            padding: 9px 11px;
            background: #FFF1D5;
            color: #96600E;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
        }

        .qr-section {
            text-align: center;
            padding: 25px 0;
            border-bottom: 1px dashed #C9D9CD;
        }

        .qr-section img {
            width: 215px;
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 15px;
        }

        .field-label {
            font-size: 12px;
            color: #81988B;
            margin-bottom: 7px;
        }

        .field-value {
            font-size: 14px;
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .cash-code {
            margin: 12px 0;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 4px;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            padding: 24px 0;
        }

        .visitor-section {
            border-top: 1px dashed #C9D9CD;
            padding-top: 20px;
        }

        .visitor-title {
            font-size: 15px;
            font-weight: bold;
            margin: 0 0 18px;
        }

        .visitor-item {
            padding: 13px 0;
            border-bottom: 1px solid #E8EFE9;
        }

        .visitor-item:last-child {
            border-bottom: none;
        }

        .visitor-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .visitor-meta {
            font-size: 12px;
            color: #778B81;
        }

        .notice {
            background: #FFF5E5;
            border: 1px solid #F0DEB8;
            color: #805516;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            line-height: 1.8;
            margin-top: 22px;
        }

        .actions {
            display: flex;
            gap: 12px;
            margin-top: 18px;
        }

        .action {
            flex: 1;
            padding: 14px 10px;
            text-decoration: none;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            border: 1px solid #07513D;
            color: #07513D;
            background: #FFFFFF;
            cursor: pointer;
        }

        .action.primary {
            background: #07513D;
            color: white;
        }

        .bottom-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: #07513D;
            font-size: 13px;
        }

        @media (max-width: 480px) {
            body {
                padding: 24px 12px;
            }

            .ticket-card {
                padding: 18px;
            }

            .details {
                gap: 16px;
            }

            .actions {
                flex-direction: column;
            }

            .cash-code {
                font-size: 20px;
            }
        }

        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }

            body {
                padding: 0;
                background: white;
            }

            .page {
                max-width: 100%;
            }

            .page-title,
            .title-line,
            .page-subtitle,
            .actions,
            .bottom-link {
                display: none !important;
            }

            .ticket-card {
                width: 100%;
                max-width: 520px;
                margin: 0 auto;
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    @php
        $totalTickets = $order->items->sum(
            fn ($item) => $item->adult_quantity + $item->child_quantity
        );
    @endphp

    <main class="page">
        <h1 class="page-title">E-Tiket Sementara</h1>
        <div class="title-line"></div>

        <p class="page-subtitle">
            Bukti pemesanan tiket dengan metode pembayaran Cash.
        </p>

        <div class="ticket-card">

            <header class="ticket-header">
                <div>
                    <div class="brand-name">
                        KEBUN RAYA
                    </div>
                    <div class="brand-subtitle">
                        MANGROVE SURABAYA
                    </div>
                </div>

                <span class="status-badge">
                    MENUNGGU PEMBAYARAN
                </span>
            </header>

            <section class="qr-section">
                <img
                    src="{{ $qrCodeDataUri }}"
                    alt="QR Code bukti pemesanan Cash"
                >

                <div class="field-label">
                    Kode Pembayaran Cash
                </div>

                <div class="cash-code">
                    {{ $cashDisplayCode }}
                </div>

                <div class="field-label">
                    Nomor Pesanan
                </div>

                <div class="field-value">
                    {{ $order->code }}
                </div>
            </section>

            <section class="details">
                <div>
                    <div class="field-label">
                        Tanggal Kunjungan
                    </div>
                    <div class="field-value">
                        {{ $order->visit_date->locale('id')->translatedFormat('d M Y') }}
                    </div>
                </div>

                <div>
                    <div class="field-label">
                        Metode Pembayaran
                    </div>
                    <div class="field-value">
                        Cash / Tunai
                    </div>
                </div>

                <div>
                    <div class="field-label">
                        Jumlah Tiket
                    </div>
                    <div class="field-value">
                        {{ $totalTickets }} Tiket
                    </div>
                </div>

                <div>
                    <div class="field-label">
                        Total yang Harus Dibayar
                    </div>
                    <div class="field-value">
                        Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                    </div>
                </div>
            </section>

            <section class="visitor-section">
                <h2 class="visitor-title">
                    Data Pengunjung
                </h2>

                @foreach ($order->items as $item)
                    @foreach ($item->visitors as $visitor)
                        <div class="visitor-item">
                            <div class="visitor-name">
                                {{ $visitor->name }}
                            </div>

                            <div class="visitor-meta">
                                {{ $item->ticket_name }}
                                &bull;
                                {{ match (strtolower((string) $visitor->age_category)) {
                                    'adult' => 'Dewasa',
                                    'child' => 'Anak',
                                    default => ucfirst((string) $visitor->age_category)
                                } }}
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </section>

            <div class="notice">
                <strong>BUKAN TIKET MASUK</strong>

                <div>
                    Tunjukkan bukti pemesanan ini kepada
                    Petugas Loket pada tanggal kunjungan
                    dan lakukan pembayaran secara tunai.
                </div>

                <div>
                    QR Code ini hanya untuk identifikasi pesanan,
                    bukan untuk check-in.
                    E-Tiket final diterbitkan setelah
                    pembayaran dikonfirmasi Lunas.
                </div>
            </div>

        </div>

        <div class="actions">
            <button
                type="button"
                class="action primary"
                onclick="window.print()"
            >
                Cetak / Simpan PDF
            </button>

            <a
                class="action"
                href="{{ route('ticket-payment.show', ['code' => $order->code]) }}"
            >
                Kembali ke Pembayaran
            </a>
        </div>

        <a
            class="bottom-link"
            href="{{ route('ticket-purchase.orders') }}"
        >
            &larr; Kembali ke Riwayat Pesanan
        </a>

    </main>
</body>
</html>
