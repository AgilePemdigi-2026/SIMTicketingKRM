@extends('layouts.app')

@section('content')
<style>
    .et-page {
        max-width: 540px;
        margin: 0 auto;
        padding: 65px 20px 85px;
        color: #173C30;
    }

    .et-title {
        text-align: center;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .et-title::after {
        content: "";
        display: block;
        width: 85px;
        height: 3px;
        background: #A5C969;
        margin: 8px auto 0;
    }

    .et-subtitle {
        text-align: center;
        color: #71847B;
        font-size: 13px;
        margin-bottom: 24px;
    }

    .et-tabs {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
        margin-bottom: 20px;
    }

    .et-tab {
        border: 1px solid #CBDAD0;
        padding: 9px 15px;
        background: white;
        color: #07513D;
        font-size: 12px;
        cursor: pointer;
    }

    .et-tab[aria-pressed="true"] {
        background: #07513D;
        color: white;
        border-color: #07513D;
    }

    .et-card {
        border: 1px solid #C8D6CB;
        background: white;
        padding: 25px;
    }

    .et-card[hidden] {
        display: none !important;
    }

    .et-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 20px;
        border-bottom: 1px dashed #CBDAD0;
    }

    .et-brand {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .et-brand-name {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1.3px;
    }

    .et-brand-subtitle {
        font-size: 9px;
        letter-spacing: 1px;
        color: #70857B;
    }

    .et-badge {
        padding: 6px 10px;
        background: #E3F0E3;
        color: #237246;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .et-qr-area {
        text-align: center;
        padding: 22px 0 16px;
    }

    .et-qr {
        display: block;
        width: 175px;
        height: 175px;
        margin: 0 auto;
        object-fit: contain;
    }

    .et-transaction {
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 1px dashed #CBDAD0;
    }

    .et-transaction-label {
        font-size: 12px;
        color: #647A70;
        margin-bottom: 5px;
    }

    .et-transaction-code {
        font-size: 13px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .et-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px 22px;
        padding: 22px 0;
    }

    .et-field-label {
        color: #819187;
        font-size: 11px;
        margin-bottom: 5px;
    }

    .et-field-value {
        font-size: 12px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .et-note {
        border-top: 1px dashed #CBDAD0;
        padding-top: 16px;
        color: #809087;
        font-size: 11px;
        line-height: 1.7;
        text-align: center;
    }

    .et-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 12px;
    }

    .et-button {
        padding: 13px 10px;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .et-button-primary {
        border: 1px solid #07513D;
        background: #07513D;
        color: white;
    }

    .et-button-outline {
        border: 1px solid #07513D;
        background: transparent;
        color: #07513D;
    }

    .et-back {
        display: block;
        text-align: center;
        margin-top: 24px;
        color: #07513D;
        font-size: 12px;
        font-weight: 700;
        text-decoration: underline;
    }

    .et-dialog {
        border: 1px solid #C8D6CB;
        padding: 25px;
        margin: auto;
        max-width: 90vw;
        background: white;
        text-align: center;
    }

    .et-dialog::backdrop {
        background: rgba(0, 0, 0, .65);
    }

    .et-dialog img {
        width: 280px;
        max-width: 100%;
        margin: 15px auto;
    }

    @media (max-width: 480px) {
        .et-page {
            padding: 35px 14px 55px;
        }

        .et-card {
            padding: 18px;
        }

        .et-details {
            gap: 16px 12px;
        }

        .et-actions {
            grid-template-columns: 1fr;
        }
    }

    @media print {
        @page {
            size: A4;
            margin: 12mm;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            min-height: 0 !important;
        }

        /* Sembunyikan elemen layout lain tanpa menyisakan ruang */
        body *:not(.et-page):not(.et-page *):not(:has(.et-page)) {
            display: none !important;
        }

        .et-page {
            position: static !important;
            width: 100% !important;
            max-width: none !important;
            height: auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Sembunyikan judul, tab, dan tombol */
        .et-page > :not(.et-card) {
            display: none !important;
        }

        .et-card[hidden] {
            display: none !important;
        }

        /* Cetak hanya E-Tiket pengunjung yang dipilih */
        .et-card:not([hidden]) {
            position: static !important;
            display: block !important;
            visibility: visible !important;
            width: 480px !important;
            max-width: 100% !important;
            margin: 0 auto !important;
            padding: 22px !important;
            transform: none !important;
            box-shadow: none !important;
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        .et-card:not([hidden]) * {
            visibility: visible !important;
        }
    }

</style>

<div class="et-page">

    <h1 class="et-title">E-Tiket</h1>

    <p class="et-subtitle">
        Pilih pengunjung untuk melihat E-Tiket masing-masing.
    </p>

    @if ($order->issuedTickets->isNotEmpty())

        <!-- Pilihan E-Tiket Pengunjung -->
        <div class="et-tabs">
            @foreach ($order->issuedTickets as $ticket)
                <button
                    type="button"
                    class="et-tab"
                    data-ticket-tab="{{ $loop->index }}"
                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                >
                    Pengunjung {{ $loop->iteration }}
                </button>
            @endforeach
        </div>

        @foreach ($order->issuedTickets as $ticket)

            <article
                class="et-card"
                data-ticket-card="{{ $loop->index }}"
                @if (!$loop->first) hidden @endif
            >

                <!-- Header Kartu -->
                <div class="et-card-header">

                    <div class="et-brand">
                        <svg width="42" height="42"
                             viewBox="0 0 48 48"
                             fill="none"
                             aria-hidden="true">
                            <circle cx="24" cy="24" r="22"
                                    stroke="#A4C5B1"
                                    stroke-width="1.5"/>
                            <path d="M35 12C20 12 13 20 13 31
                                     C13 35 16 37 20 37
                                     C30 37 35 28 35 12Z"
                                  stroke="#07513D"
                                  stroke-width="2"/>
                            <path d="M14 36C20 26 26 21 32 17"
                                  stroke="#07513D"
                                  stroke-width="2"/>
                        </svg>

                        <div>
                            <div class="et-brand-name">
                                KEBUN RAYA
                            </div>
                            <div class="et-brand-subtitle">
                                MANGROVE SURABAYA
                            </div>
                        </div>
                    </div>

                    <span class="et-badge">
                        {{ $order->payment?->status === 'paid'
                            ? 'LUNAS'
                            : strtoupper($ticket->status) }}
                    </span>

                </div>

                <!-- QR Code Asli -->
                <div class="et-qr-area">
                    <img
                        src="{{ $ticket->qr_code_data_uri }}"
                        alt="QR Code E-Tiket {{ $ticket->ticket_code }}"
                        class="et-qr"
                    >
                </div>

                <!-- Nomor Transaksi -->
                <div class="et-transaction">
                    <div class="et-transaction-label">
                        No. Transaksi
                    </div>

                    <div class="et-transaction-code">
                        {{ $order->code }}
                    </div>
                </div>

                <!-- Informasi Tiket -->
                <div class="et-details">

                    <div>
                        <div class="et-field-label">
                            Nama Pengunjung
                        </div>
                        <div class="et-field-value">
                            {{ $ticket->visitor->name }}
                        </div>
                    </div>

                    <div>
                        <div class="et-field-label">
                            Kategori Pengunjung
                        </div>
                        <div class="et-field-value">
                            {{ ucfirst($ticket->visitor->age_category) }}
                        </div>
                    </div>

                    <div>
                        <div class="et-field-label">
                            Tanggal Kunjungan
                        </div>
                        <div class="et-field-value">
                            {{ $order->visit_date->format('d M Y') }}
                        </div>
                    </div>

                    <div>
                        <div class="et-field-label">
                            Jenis Tiket
                        </div>
                        <div class="et-field-value">
                            {{ $ticket->item->ticket_name }}
                        </div>
                    </div>

                    <div>
                        <div class="et-field-label">
                            Jumlah dalam Pesanan
                        </div>
                        <div class="et-field-value">
                            {{ $order->issuedTickets->count() }} Tiket
                        </div>
                    </div>

                    <div>
                        <div class="et-field-label">
                            Total Pembayaran Pesanan
                        </div>
                        <div class="et-field-value">
                            Rp {{ number_format(
                                (float) $order->total,
                                0, ',', '.'
                            ) }}
                        </div>
                    </div>

                    <div>
                        <div class="et-field-label">
                            Metode Bayar
                        </div>
                        <div class="et-field-value">
                            {{ strtoupper($order->payment?->method ?? '-') }}
                        </div>
                    </div>

                    <div>
                        <div class="et-field-label">
                            Status E-Tiket
                        </div>
                        <div class="et-field-value">
                            {{ strtoupper($ticket->status) }}
                        </div>
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <div class="et-field-label">
                            Kode Tiket
                        </div>
                        <div class="et-field-value">
                            {{ $ticket->ticket_code }}
                        </div>
                    </div>

                </div>

                <p class="et-note">
                    Tunjukkan QR Code ini kepada petugas saat tiba
                    di lokasi wisata. QR Code hanya berlaku
                    untuk satu kali penggunaan.
                </p>

            </article>

        @endforeach

        <!-- Tombol Aksi -->
        <div class="et-actions">

            <button
                type="button"
                class="et-button et-button-primary"
                id="printEticket"
            >
                Cetak / Simpan PDF
            </button>

            <button
                type="button"
                class="et-button et-button-outline"
                id="viewEticketQr"
            >
                Lihat QR Code
            </button>

        </div>

    @else
        <div class="et-card">
            <p>
                E-Tiket belum diterbitkan.
                Silakan hubungi petugas jika pembayaran sudah lunas.
            </p>
        </div>
    @endif

    <a href="{{ route('wisatawan.beranda') }}"
       class="et-back">
        ← Kembali ke Beranda
    </a>

</div>

<!-- Dialog Perbesar QR Code -->
<dialog id="etQrDialog" class="et-dialog">
    <h2 style="font-weight:700; font-size:18px;">
        QR Code E-Tiket
    </h2>

    <img id="etQrLarge" alt="QR Code E-Tiket diperbesar">

    <p style="font-size:12px; margin-bottom:15px;">
        Tunjukkan kepada petugas saat check-in.
    </p>

    <button
        type="button"
        id="closeEtQr"
        class="et-button et-button-primary"
    >
        Tutup
    </button>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('[data-ticket-tab]');
    const cards = document.querySelectorAll('[data-ticket-card]');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const selected = tab.dataset.ticketTab;

            tabs.forEach(function (item) {
                item.setAttribute(
                    'aria-pressed',
                    String(item.dataset.ticketTab === selected)
                );
            });

            cards.forEach(function (card) {
                card.hidden = card.dataset.ticketCard !== selected;
            });
        });
    });

    const printButton = document.getElementById('printEticket');
    const viewButton = document.getElementById('viewEticketQr');
    const dialog = document.getElementById('etQrDialog');
    const largeQr = document.getElementById('etQrLarge');
    const closeButton = document.getElementById('closeEtQr');

    printButton?.addEventListener('click', function () {
        window.print();
    });

    viewButton?.addEventListener('click', function () {
        const activeCard = document.querySelector(
            '[data-ticket-card]:not([hidden])'
        );

        const qr = activeCard?.querySelector('.et-qr');

        if (qr && dialog) {
            largeQr.src = qr.src;
            dialog.showModal();
        }
    });

    closeButton?.addEventListener('click', function () {
        dialog.close();
    });
});
</script>
@endsection
