@extends('layouts.app')

@section('title', 'Pembayaran Tiket - Kebun Raya Mangrove Surabaya')

@push('styles')
    @include('partials.ticket-payment-styles')

    <style>
        .qr-cash-code {
            margin: 0 0 12px;
            color: var(--color-primary);
            font-size: clamp(27px, 3.5vw, 36px);
            font-weight: 500;
            letter-spacing: 0.16em;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .qr-cash-description {
            max-width: 620px;
            margin: 0 auto 20px;
            line-height: 1.8;
        }

        .qr-cash-temporary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            min-height: 100px;
            width: 100%;
            margin: 18px auto 24px;
            background: #eaf2e8;
            color: #183d32;
            font-size: 17px;
            font-weight: 500;
        }

        .qr-cash-temporary span:first-child {
            font-size: 25px;
        }

        .qr-cash-view {
            margin: 0 auto 8px;
        }

        .qr-cash-payment-status {
            margin-top: 15px;
        }

        .qr-cash-full-code {
            overflow-wrap: anywhere;
            font-family: monospace;
            font-size: 12px;
        }
        /* ===== HALAMAN PEMBAYARAN SPRINT 2 ===== */

        .qr-page {
            max-width: 1100px;
            padding-top: 70px;
            padding-bottom: 65px;
        }

        .qr-page .purchase-steps {
            margin-bottom: 52px;
        }

        .qr-page .purchase-heading {
            margin-bottom: 38px;
        }

        .qr-page .purchase-heading h1 {
            font-size: 27px;
        }

        .qr-layout {
            display: grid;
            grid-template-columns: minmax(0, 2.2fr) minmax(280px, 1fr);
            gap: 20px;
            align-items: stretch;
        }

        .qr-main {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-width: 0;
        }

        .qr-box {
            padding: 24px;
            background: white;
            border: 1px solid var(--color-border);
            min-width: 0;
        }

        .qr-box-title {
            margin: 0 0 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--color-border);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        /* ===== PILIHAN METODE ===== */

        .qr-method-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            width: 100%;
        }

        .qr-method-tab {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            min-height: 52px;
            border: 1px solid var(--color-border);
            color: var(--color-primary);
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            background: white;
        }

        .qr-method-tab + .qr-method-tab {
            border-left: 0;
        }

        .qr-method-tab.is-active,
        .qr-method-tab:has(input:checked) {
            background: var(--color-primary);
            color: white;
        }

        .qr-method-tab.is-choice {
            cursor: pointer;
        }

        .qr-method-tab input {
            position: absolute;
            width: 1px !important;
            height: 1px !important;
            min-height: 0 !important;
            opacity: 0;
        }

        .qr-method-tab:focus-within {
            outline: 2px solid var(--color-secondary);
            outline-offset: 3px;
        }

        .qr-method-help {
            margin-top: 12px;
            font-size: 12px;
            line-height: 1.6;
            color: var(--color-muted);
        }

        .qr-method-submit {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
        }

        /* ===== BAGIAN QRIS ===== */

        .qr-payment-box {
            flex: 1;
            text-align: center;
        }

        .qr-payment-title {
            margin: 4px 0 24px;
            font-size: 15px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .qr-image-frame {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 190px;
            height: 190px;
            padding: 10px;
            margin: 0 auto 24px;
            background: white;
            border: 1px solid var(--color-border);
        }

        .qr-image-frame img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .qr-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: #f3f7f3;
            color: var(--color-muted);
            font-size: 12px;
            line-height: 1.6;
            text-align: center;
            padding: 15px;
        }

        .qr-expiry-label {
            font-size: 13px;
            color: var(--color-text);
        }

        .qr-countdown {
            display: block;
            margin: 8px 0;
            font-size: 17px;
            font-weight: 500;
            font-variant-numeric: tabular-nums;
        }

        .qr-status {
            display: inline-block;
            padding: 7px 12px;
            margin-top: 6px;
            background: #fff3d8;
            color: #967126;
            font-size: 11px;
            font-weight: 500;
        }

        .qr-status.is-paid {
            background: #e9f4e9;
            color: #32764b;
        }

        .qr-info {
            margin-top: 17px;
            color: var(--color-muted);
            font-size: 12px;
            line-height: 1.8;
        }

        .qr-test-notice {
            max-width: 450px;
            margin: 16px auto;
            padding: 12px;
            border: 1px solid #eadfbd;
            background: #fff9e9;
            color: #795e2d;
            font-size: 12px;
            line-height: 1.7;
            text-align: center;
        }

        .qr-pay-button {
            margin: 18px auto 0;
        }

        .qr-simulator-tools {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .qr-simulator-details {
            margin: 14px auto;
            max-width: 470px;
            text-align: left;
            font-size: 12px;
        }

        .qr-simulator-details summary {
            cursor: pointer;
            color: var(--color-primary);
            font-weight: 600;
        }

        .qr-simulator-details input {
            margin-top: 10px;
            width: 100%;
            font-size: 11px;
        }

        /* ===== RINGKASAN PEMBAYARAN ===== */

        .qr-summary {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .qr-summary-meta {
            display: grid;
            gap: 18px;
        }

        .qr-summary-meta dt {
            margin-bottom: 5px;
            color: var(--color-muted);
            font-size: 11px;
            font-weight: 400;
        }

        .qr-summary-meta dd {
            margin: 0;
            color: var(--color-text);
            font-size: 13px;
            font-weight: 600;
            line-height: 1.6;
            overflow-wrap: anywhere;
        }

        .qr-summary-total {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid var(--color-border);
        }

        .qr-summary-total span {
            display: block;
            margin-bottom: 7px;
            color: var(--color-muted);
            font-size: 11px;
        }

        .qr-summary-total strong {
            display: block;
            color: var(--color-primary);
            font-size: 24px;
            font-weight: 700;
        }

        .qr-summary-history {
            margin-top: 25px;
            border-top: 1px solid var(--color-border);
            padding-top: 18px;
        }

        .qr-summary-history h3 {
            margin-bottom: 12px;
            font-size: 12px;
            font-weight: 700;
        }

        .qr-summary-history a {
            text-decoration: underline;
            font-size: 12px;
        }

        /* ===== TOMBOL BAWAH ===== */

        .qr-bottom-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .qr-bottom-actions .krm-btn {
            min-height: 44px;
        }

        .qr-footer-note {
            margin-top: 10px;
            color: var(--color-muted);
            font-size: 11px;
            text-align: right;
            line-height: 1.6;
        }

        /* ===== RESPONSIVE ===== */

        @media (max-width: 800px) {
            .qr-layout {
                grid-template-columns: 1fr;
            }

            .qr-page {
                padding-top: 35px;
            }

            .qr-summary {
                min-height: 0;
            }
        }

        @media (max-width: 640px) {
            .qr-page {
                width: calc(100% - 32px);
            }

            .qr-box {
                padding: 18px;
            }

            .qr-page .purchase-steps {
                gap: 10px;
            }

            .qr-method-tab {
                min-height: 48px;
                font-size: 13px;
            }

            .qr-bottom-actions {
                flex-direction: column;
            }

            .qr-bottom-actions .krm-btn {
                width: 100%;
            }

            .qr-footer-note {
                text-align: center;
            }
        }

        /* ===== POPUP MODAL PEMBAYARAN BERHASIL ===== */
        .payment-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 30, 24, 0.68);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .payment-modal-backdrop.is-visible {
            opacity: 1;
            visibility: visible;
        }

        .payment-modal-dialog {
            background: #ffffff;
            border: 2px solid var(--color-primary, #183d32);
            width: 100%;
            max-width: 460px;
            padding: 34px 28px 28px;
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.24);
            text-align: center;
            transform: translateY(20px) scale(0.96);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .payment-modal-backdrop.is-visible .payment-modal-dialog {
            transform: translateY(0) scale(1);
        }

        .payment-modal-icon-wrap {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            background: #eaf2e8;
            border-radius: 50%;
            display: grid;
            place-items: center;
            color: var(--color-primary, #183d32);
            animation: popIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .payment-modal-icon-wrap svg {
            width: 36px;
            height: 36px;
        }

        .payment-modal-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--color-primary, #183d32);
            margin: 0 0 10px;
        }

        .payment-modal-desc {
            font-size: 14px;
            color: #526359;
            line-height: 1.6;
            margin: 0 0 20px;
        }

        .payment-modal-summary {
            background: #f7faf8;
            border: 1px solid #dce8e0;
            padding: 14px 18px;
            margin-bottom: 22px;
            text-align: left;
            font-size: 13px;
        }

        .payment-modal-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            overflow-wrap: anywhere;
        }

        .payment-modal-row span:first-child {
            color: #6a7c73;
            font-size: 12px;
        }

        .payment-modal-row span:last-child {
            font-weight: 600;
            color: #183d32;
        }

        .payment-modal-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .payment-modal-actions .krm-btn {
            flex: 1;
            text-align: center;
            padding: 12px 18px;
            font-size: 14px;
            text-decoration: none;
        }

        @keyframes popIn {
            0% { transform: scale(0.6); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
@endpush

@section('content')

@php
    $payment = $order->payment;

    $attempt = $payment?->gatewayAttempts?->first();

    $isPaid = $order->status === 'paid'
        && $payment?->status === 'paid';

    $ticketCount = $order->items->sum('adult_quantity')
        + $order->items->sum('child_quantity');

    $adultCount = $order->items->sum('adult_quantity');
    $childCount = $order->items->sum('child_quantity');

    $visitorType = $order->visitor_type === 'domestik'
        ? 'Domestik'
        : 'Mancanegara';

    $ageType = $adultCount > 0 && $childCount > 0
        ? 'Dewasa & Anak'
        : ($adultCount > 0 ? 'Dewasa' : 'Anak');

    $ticketNames = $order->items
        ->pluck('ticket_name')
        ->filter()
        ->unique()
        ->implode(', ');

    $totalFormatted = number_format(
        (float) $order->total,
        0,
        ',',
        '.'
    );
@endphp

<div class="purchase-page krm-container qr-page">

    {{-- ===== TAHAPAN PEMBELIAN ===== --}}
    <ol class="purchase-steps" aria-label="Tahapan pembelian">

        <li class="completed">
            <span aria-hidden="true">&#10003;</span>
            Pilih Tiket
        </li>

        <li class="completed">
            <span aria-hidden="true">&#10003;</span>
            Data Pengunjung
        </li>

        <li aria-current="step">
            <span>3</span>
            Pembayaran
        </li>

    </ol>

    {{-- ===== JUDUL ===== --}}
    <div class="purchase-heading">
        <h1>Pembayaran</h1>
    </div>

    @include('partials.ticket-payment-feedback')

    <div class="qr-layout">

        {{-- ===== KOLOM KIRI ===== --}}
        <div class="qr-main">

            {{-- PILIH METODE --}}
            <section class="qr-box" aria-labelledby="method-title">

                <h2 id="method-title" class="qr-box-title">
                    Metode Pembayaran
                </h2>

                @if(!$payment && $order->status === 'pending_payment')

                    <form
                        method="POST"
                        action="{{ route('ticket-payment.select', $order->code) }}"
                        data-payment-form
                    >
                        @csrf

                        <fieldset>
                            <legend class="sr-only">
                                Pilih metode pembayaran
                            </legend>

                            <div class="qr-method-tabs">

                                <label class="qr-method-tab is-choice">
                                    <input
                                        type="radio"
                                        name="method"
                                        value="qris"
                                        required
                                        @checked(old('method') === 'qris')
                                    >
                                    QRIS
                                </label>

                                <label class="qr-method-tab is-choice">
                                    <input
                                        type="radio"
                                        name="method"
                                        value="cash"
                                        required
                                        @checked(old('method') === 'cash')
                                    >
                                    CASH
                                </label>

                            </div>
                        </fieldset>

                        @error('method')
                            <p class="payment-error">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="qr-method-help">
                            Pilih metode pembayaran.
                            Metode tidak dapat diubah setelah disimpan.
                        </p>

                        <div class="qr-method-submit">
                            <button
                                type="submit"
                                class="krm-btn krm-btn-primary"
                            >
                                Simpan Metode Pembayaran
                            </button>
                        </div>

                    </form>

                @elseif($payment)

                    <div class="qr-method-tabs" role="group"
                         aria-label="Metode pembayaran tersimpan">

                        <span class="qr-method-tab
                            {{ $payment->method === 'qris' ? 'is-active' : '' }}">
                            QRIS
                        </span>

                        <span class="qr-method-tab
                            {{ $payment->method === 'cash' ? 'is-active' : '' }}">
                            CASH
                        </span>

                    </div>

                    <p class="qr-method-help">
                        Metode pembayaran sudah tersimpan.
                    </p>

                @else

                    <p class="qr-method-help">
                        Metode pembayaran tidak tersedia
                        untuk status pesanan saat ini.
                    </p>

                @endif

            </section>

            {{-- ===== INSTRUKSI PEMBAYARAN ===== --}}
            <section class="qr-box qr-payment-box"
                     aria-labelledby="payment-title">

                {{-- PESANAN SUDAH LUNAS --}}
                @if($isPaid)

                    <h2 id="payment-title" class="qr-payment-title">
                        PEMBAYARAN BERHASIL
                    </h2>

                    <div class="qr-image-frame">
                        <div class="qr-placeholder">
                            Pembayaran sudah dikonfirmasi
                        </div>
                    </div>

                    <span class="qr-status is-paid">
                        Lunas
                    </span>

                    <p class="qr-info">
                        Pembayaran telah dikonfirmasi.
                        Pesananmu siap diproses oleh
                        modul penerbitan E-Tiket.
                    </p>

                    @if($payment->paid_at)
                        <p class="qr-info">
                            Dikonfirmasi:
                            {{ $payment->paid_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                            WIB
                        </p>
                    @endif

                {{-- QRIS MIDTRANS --}}
                @elseif($payment?->method === 'qris')

                    <h2 id="payment-title" class="qr-payment-title">
                        SCAN QR UNTUK MEMBAYAR
                    </h2>

                    <div class="qr-test-notice">
                        <strong>MIDTRANS SANDBOX — MODE PENGUJIAN</strong>
                        <div>
                            Tidak menggunakan uang sungguhan.
                            Gunakan simulator Midtrans,
                            bukan aplikasi bank atau e-wallet asli.
                        </div>
                    </div>

                    {{-- QRIS BELUM DIBUAT --}}
                    @if(!$attempt && $payment->status === 'pending')

                        <div class="qr-image-frame">
                            <div class="qr-placeholder">
                                QRIS belum dibuat
                            </div>
                        </div>

                        <p class="qr-info">
                            Buat QRIS sebesar
                            <strong>Rp {{ $totalFormatted }}</strong>
                            melalui Midtrans Sandbox.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('ticket-payment.qris.create', $order->code) }}"
                            data-payment-form
                        >
                            @csrf

                            <button
                                type="submit"
                                class="krm-btn krm-btn-primary qr-pay-button"
                            >
                                Buat QRIS Midtrans Sandbox
                            </button>
                        </form>

                    {{-- QRIS SUDAH BERHASIL DIBUAT --}}
                    @elseif(
                        $attempt
                        && $attempt->status === 'pending'
                        && $attempt->qr_image_url
                    )

                        <div class="qr-image-frame">
                            <img
                                src="{{ $attempt->qr_image_url }}"
                                alt="QRIS pengujian dari Midtrans Sandbox"
                                referrerpolicy="no-referrer"
                                width="168"
                                height="168"
                            >
                        </div>

                        @if($attempt->expires_at)

                            <p class="qr-expiry-label">
                                Batas waktu pembayaran
                            </p>

                            <strong
                                class="qr-countdown"
                                data-midtrans-expiry="{{ $attempt->expires_at->toIso8601String() }}"
                                role="timer"
                            >
                                --:--
                            </strong>

                        @else

                            <p class="qr-expiry-label">
                                Batas waktu pembayaran
                            </p>

                            <p class="qr-info">
                                Belum tersedia dari gateway.
                                Periksa status transaksi sebelum
                                melakukan pembayaran.
                            </p>

                        @endif

                        <span class="qr-status">
                            Menunggu pembayaran
                        </span>

                        <p class="qr-info">
                            Untuk pengujian, buka simulator Midtrans
                            dan masukkan URL gambar QRIS.
                        </p>

                        <div class="qr-simulator-tools">

                            <a
                                href="https://simulator.sandbox.midtrans.com/v2/qris/index"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="krm-btn"
                            >
                                Buka Simulator
                            </a>

                            <form
                                method="POST"
                                action="{{ route('ticket-payment.qris.sync', $order->code) }}"
                                data-payment-form
                                style="margin: 0;"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="krm-btn"
                                >
                                    Perbarui Status
                                </button>
                            </form>

                        </div>

                        <details class="qr-simulator-details">
                            <summary>
                                Lihat URL QRIS untuk simulator
                            </summary>

                            <input
                                type="text"
                                readonly
                                value="{{ $attempt->qr_image_url }}"
                                aria-label="URL gambar QRIS"
                                onclick="this.select()"
                            >
                        </details>

                        <p class="qr-info">
                            Status Lunas hanya akan diberikan setelah
                            pembayaran terverifikasi oleh backend.
                        </p>

                        {{-- QRIS KEDALUWARSA / GAGAL --}}
                        @elseif(
                            $attempt &&
                            in_array(
                                $attempt->status,
                                ['expired', 'failed', 'cancelled'],
                                true
                            )
                        )

                            <div class="qr-image-frame">
                                <div class="qr-placeholder">
                                    QRIS sudah tidak berlaku
                                </div>
                            </div>

                            <span
                                class="qr-status"
                                style="background:#fff0ec;color:#9e3220"
                            >
                                {{ match($attempt->status) {
                                    'expired' => 'Pembayaran Kedaluwarsa',
                                    'failed' => 'Pembayaran Gagal',
                                    'cancelled' => 'Pembayaran Dibatalkan',
                                } }}
                            </span>

                            <p class="qr-info">
                                Transaksi QRIS ini sudah tidak aktif.
                                Jangan menggunakan QR Code sebelumnya.
                            </p>

                            <p class="qr-info">
                                Pesanan belum dinyatakan Lunas.
                                Pembuatan QRIS ulang akan tersedia setelah
                                mekanisme percobaan ulang pembayaran disiapkan.
                            </p>

                        {{-- STATUS TRANSAKSI BELUM PASTI --}}
                        @else

                        <div class="qr-image-frame">
                            <div class="qr-placeholder">
                                Transaksi QRIS sedang diperiksa
                            </div>
                        </div>

                        <span class="qr-status">
                            {{ $payment->statusLabel() }}
                        </span>

                        <p class="qr-info">
                            Permintaan pembayaran belum memiliki QRIS
                            yang siap digunakan, atau status gateway
                            masih perlu diverifikasi.
                        </p>

                        <a
                            href="{{ route('ticket-payment.show', $order->code) }}"
                            class="krm-btn qr-pay-button"
                        >
                            Perbarui Halaman
                        </a>

                    @endif

                    {{-- CASH --}}
                    @elseif($payment?->method === 'cash')

                        @php
                            // Kode tampilan yang unik berdasarkan ID pesanan.
                            // Kode UUID asli tetap tersedia untuk verifikasi Petugas.
                            $cashDisplayCode = 'CASH-' . str_pad(
                                (string) $order->id,
                                4,
                                '0',
                                STR_PAD_LEFT
                            );
                        @endphp

                        <h2 id="payment-title" class="qr-payment-title">
                            KODE PEMBAYARAN CASH
                        </h2>

                        <div class="qr-cash-code">
                            {{ $cashDisplayCode }}
                        </div>

                        <p class="qr-info qr-cash-description">
                            Tunjukkan kode pembayaran kepada petugas saat tiba
                            di lokasi pada tanggal kunjungan. Lakukan pembayaran
                            secara tunai kepada petugas.
                        </p>

                        <div class="qr-cash-temporary">
                            <span aria-hidden="true">🎟</span>
                            <span>E-Tiket Sementara</span>
                        </div>

                        <button
                            type="button"
                            class="krm-btn qr-cash-view"
                            disabled
                            title="Modul E-Tiket sementara belum diintegrasikan"
                        >
                            Lihat E-Tiket Sementara
                        </button>

                        <p class="qr-info">
                            E-Tiket sementara belum tersedia.
                            Pesanan tetap tersimpan dan dapat dilihat di Pesanan Saya.
                        </p>

                        <div class="qr-cash-payment-status">
                            <span class="qr-status">
                                {{ $payment->statusLabel() }}
                            </span>
                        </div>

                        <details class="qr-simulator-details">
                            <summary>Kode pesanan lengkap untuk verifikasi Petugas</summary>
                            <p class="qr-cash-full-code">{{ $order->code }}</p>
                        </details>

                {{-- METODE BELUM DIPILIH --}}
                @else

                    <h2 id="payment-title" class="qr-payment-title">
                        PETUNJUK PEMBAYARAN
                    </h2>

                    <div class="qr-image-frame">
                        <div class="qr-placeholder">
                            Pilih QRIS atau Cash
                        </div>
                    </div>

                    <p class="qr-info">
                        Pilih dan simpan metode pembayaran
                        terlebih dahulu untuk melanjutkan.
                    </p>

                @endif

            </section>

        </div>

        {{-- ===== KOLOM KANAN: RINGKASAN ===== --}}
        <aside class="qr-box qr-summary"
               aria-labelledby="summary-title">

            <h2 id="summary-title" class="qr-box-title">
                Ringkasan
            </h2>

            <dl class="qr-summary-meta">

                <div>
                    <dt>Tiket</dt>
                    <dd>{{ $ticketNames ?: 'Tiket' }}</dd>
                </div>

                <div>
                    <dt>Kategori</dt>
                    <dd>{{ $ageType }} {{ $visitorType }}</dd>
                </div>

                <div>
                    <dt>Jumlah</dt>
                    <dd>{{ $ticketCount }} Tiket</dd>
                </div>

                <div>
                    <dt>Tanggal Kunjungan</dt>
                    <dd>
                        {{ $order->visit_date->locale('id')->translatedFormat('d M Y') }}
                    </dd>
                </div>

                <div>
                    <dt>Pemesan</dt>
                    <dd>{{ $order->booker_name }}</dd>
                </div>

            </dl>

            <div class="qr-summary-total">
                <span>Total Pembayaran</span>

                <strong>
                    Rp {{ $totalFormatted }}
                </strong>
            </div>

            {{-- RIWAYAT BUKTI DARI ALUR MANUAL LAMA --}}
            @if($payment && $payment->submissions->isNotEmpty())

                <div class="qr-summary-history">

                    <h3>Riwayat Bukti Pembayaran</h3>

                    <ul class="payment-history">
                        @foreach($payment->submissions as $submission)
                            <li>
                                <a
                                    href="{{ route('ticket-payment.proof', $submission) }}"
                                    target="_blank"
                                    rel="noopener"
                                >
                                    Lihat bukti #{{ $submission->id }}
                                </a>

                                <p>
                                    {{ match($submission->status) {
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        default => 'Menunggu verifikasi'
                                    } }}
                                </p>
                            </li>
                        @endforeach
                    </ul>

                </div>

            @endif

        </aside>

    </div>

    {{-- ===== TOMBOL NAVIGASI ===== --}}
    <div class="qr-bottom-actions">

        <a
            href="{{ route('ticket-purchase.show', $order->code) }}"
            class="krm-btn"
        >
            &larr; Kembali
        </a>

        <a
            href="{{ $isPaid
                ? route('ticket-payment.e-ticket-placeholder', $order->code)
                : route('ticket-purchase.orders') }}"
            class="krm-btn krm-btn-primary"
        >
            @if($isPaid)
                Selesai — Lihat E-Tiket
            @elseif($payment?->method === 'cash')
                Simpan Pesanan
            @else
                Lihat Pesanan Saya
            @endif
        </a>

    </div>

    <p class="qr-footer-note">
        E-Tiket dapat diunduh setelah pembayaran terkonfirmasi
        dan tiket selesai diterbitkan.
    </p>

    {{-- ===== MODAL POP-UP PEMBAYARAN BERHASIL ===== --}}
    <div
        id="payment-success-modal"
        class="payment-modal-backdrop"
        role="dialog"
        aria-modal="true"
        aria-labelledby="payment-modal-title"
        aria-describedby="payment-modal-desc"
        style="display: none;"
    >
        <div class="payment-modal-dialog">
            <div class="payment-modal-icon-wrap" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>

            <h3 id="payment-modal-title" class="payment-modal-title">
                Pembayaran Berhasil!
            </h3>

            <p id="payment-modal-desc" class="payment-modal-desc">
                Pembayaran tiket Anda telah terkonfirmasi. Status pesanan Anda kini telah berubah menjadi <strong>Lunas</strong>.
            </p>

            <div class="payment-modal-summary">
                <div class="payment-modal-row">
                    <span>Kode Pesanan</span>
                    <span>{{ $order->code }}</span>
                </div>
                <div class="payment-modal-row">
                    <span>Metode Pembayaran</span>
                    <span>{{ strtoupper($payment?->method ?? 'QRIS') }}</span>
                </div>
                <div class="payment-modal-row">
                    <span>Total Pembayaran</span>
                    <span>Rp {{ $totalFormatted }}</span>
                </div>
                <div class="payment-modal-row">
                    <span>Status</span>
                    <span style="color: #1e704a;">LUNAS</span>
                </div>
            </div>

            <div class="payment-modal-actions">
                <button
                    type="button"
                    class="krm-btn"
                    id="close-success-modal-btn"
                >
                    Tutup
                </button>
                <a
                    href="{{ route('ticket-payment.e-ticket-placeholder', $order->code) }}"
                    class="krm-btn krm-btn-primary"
                >
                    Lihat E-Tiket
                </a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
    @include('partials.ticket-payment-script')

    <script>
        document.querySelectorAll('[data-midtrans-expiry]').forEach(
            function (element) {

                const expiry = new Date(
                    element.dataset.midtransExpiry
                ).getTime();

                if (Number.isNaN(expiry)) {
                    element.textContent = 'Tidak tersedia';
                    return;
                }

                function updateCountdown() {
                    const remaining = Math.max(
                        0,
                        Math.floor((expiry - Date.now()) / 1000)
                    );

                    const minutes = Math.floor(remaining / 60);
                    const seconds = remaining % 60;

                    element.textContent =
                        String(minutes).padStart(2, '0') +
                        ':' +
                        String(seconds).padStart(2, '0');

                    if (remaining === 0) {
                        element.textContent = 'Waktu habis';
                    }
                }

                updateCountdown();
                setInterval(updateCountdown, 1000);
            }
        );

        // Logika Pop-up Pembayaran Berhasil & Auto-Refresh
        (function () {
            const modal = document.getElementById('payment-success-modal');
            const closeBtn = document.getElementById('close-success-modal-btn');
            const isInitiallyPaid = {{ $isPaid ? 'true' : 'false' }};
            const orderCode = @json($order->code);
            const seenKey = 'krm_payment_success_seen_' + orderCode;

            function openSuccessModal() {
                if (!modal) return;
                modal.style.display = 'flex';
                void modal.offsetWidth;
                modal.classList.add('is-visible');
                try {
                    sessionStorage.setItem(seenKey, '1');
                } catch (e) {}
            }

            function closeSuccessModal() {
                if (!modal) return;
                modal.classList.remove('is-visible');
                setTimeout(() => {
                    modal.style.display = 'none';
                }, 250);
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', closeSuccessModal);
            }

            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        closeSuccessModal();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && modal.classList.contains('is-visible')) {
                        closeSuccessModal();
                    }
                });
            }

            // Jika pesanan sudah lunas:
            // Tampilkan pop-up jika baru saja di-redirect dari proses sinkronisasi/update status,
            // atau jika belum pernah dilihat pada sesi browser saat ini.
            if (isInitiallyPaid) {
                @if(session('payment_updated'))
                    openSuccessModal();
                @else
                    try {
                        if (sessionStorage.getItem(seenKey) !== '1') {
                            openSuccessModal();
                        }
                    } catch (e) {
                        openSuccessModal();
                    }
                @endif
            } else {
                // Jika pembayaran masih pending (menunggu scan QRIS):
                // Lakukan pengecekan status secara berkala (setiap 4 detik).
                let pollTimer = null;
                const checkStatus = () => {
                    fetch(window.location.href, {
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && (data.order_status === 'paid' || data.payment?.status === 'paid')) {
                            if (pollTimer) clearInterval(pollTimer);
                            openSuccessModal();
                            if (closeBtn) {
                                closeBtn.onclick = () => {
                                    window.location.reload();
                                };
                            }
                        }
                    })
                    .catch(() => {});
                };

                pollTimer = setInterval(checkStatus, 4000);
            }
        })();
    </script>
@endpush
