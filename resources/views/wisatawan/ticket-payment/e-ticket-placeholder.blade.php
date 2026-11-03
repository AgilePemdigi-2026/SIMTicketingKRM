@extends('layouts.app')

@section('title', 'E-Tiket Sementara - Kebun Raya Mangrove Surabaya')

@push('styles')
<style>
    .etp-page {
        max-width: 940px;
        margin: 0 auto;
        padding: 75px 20px 70px;
        color: #173c32;
    }

    .etp-title {
        width: fit-content;
        margin: 0 auto 28px;
        padding-bottom: 7px;
        border-bottom: 3px solid #a3cb76;
        font-size: 30px;
        font-weight: 700;
        line-height: 1.2;
    }

    .etp-card {
        max-width: 470px;
        margin: 0 auto;
        padding: 24px;
        background: #fff;
        border: 1px solid #cbd8d1;
    }

    .etp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding-bottom: 22px;
        border-bottom: 1px dashed #cbd8d1;
    }

    .etp-brand {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .etp-logo {
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border: 1px solid #78a68d;
        border-radius: 50%;
        flex: none;
    }

    .etp-brand strong, .etp-brand small {
        display: block;
        letter-spacing: .1em;
    }

    .etp-brand strong {
        font-size: 12px;
    }

    .etp-brand small {
        font-size: 9px;
        margin-top: 3px;
    }

    .etp-paid {
        padding: 7px 10px;
        background: #e3f1e1;
        color: #266344;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
    }

    .etp-qr-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 178px;
        height: 178px;
        margin: 22px auto 18px;
        padding: 20px;
        border: 1px solid #d9e3dd;
        background: #f7faf8;
        text-align: center;
        color: #657c71;
        line-height: 1.6;
        font-size: 12px;
    }

    .etp-transaction {
        text-align: center;
        padding: 0 0 18px;
        border-bottom: 1px dashed #cbd8d1;
    }

    .etp-transaction span {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        color: #677c72;
    }

    .etp-transaction strong {
        display: block;
        font-size: 14px;
        letter-spacing: .04em;
        overflow-wrap: anywhere;
    }

    .etp-details {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 20px 18px;
        margin: 18px 0;
    }

    .etp-details dt {
        font-size: 10px;
        color: #73857b;
        margin-bottom: 5px;
    }

    .etp-details dd {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .etp-note {
        padding-top: 13px;
        border-top: 1px dashed #cbd8d1;
        font-size: 11px;
        color: #6b7f74;
        line-height: 1.7;
    }

    .etp-actions {
        max-width: 470px;
        margin: 10px auto 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .etp-actions button {
        min-height: 45px;
        padding: 10px;
        border: 1px solid #a8bdb2;
        background: #eff3f0;
        color: #71867b;
        cursor: not-allowed;
        font-size: 12px;
        font-weight: 600;
    }

    .etp-back {
        margin-top: 23px;
        text-align: center;
        font-size: 12px;
    }

    .etp-back a {
        color: #0d4a38;
        font-weight: 700;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .etp-back a + a {
        margin-left: 16px;
    }

    @media (max-width: 520px) {
        .etp-page { padding: 42px 16px 55px; }
        .etp-card { padding: 18px; }
        .etp-title { font-size: 26px; }
        .etp-brand strong { font-size: 10px; }
        .etp-brand small { font-size: 8px; }
        .etp-details { gap: 15px 10px; }
        .etp-back a { display: inline-block; margin: 6px; }
        .etp-back a + a { margin-left: 6px; }
    }
</style>
@endpush

@section('content')
@php
    $ticketNames = $order->items->pluck('ticket_name')->filter()->unique()->implode(', ');
    $adultCount = (int) $order->items->sum('adult_quantity');
    $childCount = (int) $order->items->sum('child_quantity');
    $visitorType = $order->visitor_type === 'domestik' ? 'Domestik' : 'Mancanegara';
    $ticketCount = $adultCount + $childCount;
    $quantityText = collect([
        $adultCount > 0 ? $adultCount . ' Dewasa' : null,
        $childCount > 0 ? $childCount . ' Anak' : null,
    ])->filter()->implode(', ');
@endphp

<main class="etp-page">
    <h1 class="etp-title">E-Tiket</h1>

    <article class="etp-card" aria-label="Informasi pesanan dengan pembayaran lunas">
        <header class="etp-header">
            <div class="etp-brand">
                <span class="etp-logo" aria-hidden="true">
                    <svg width="27" height="27" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M25 6C15 7 9 13 8 21c4 5 13 4 17-3 2-4 1-8 0-12Z" stroke="#19503D" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M7 26c5-8 9-12 16-16" stroke="#19503D" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>
                    <strong>KEBUN RAYA</strong>
                    <small>MANGROVE SURABAYA</small>
                </span>
            </div>
            <span class="etp-paid">LUNAS</span>
        </header>

        <div class="etp-qr-placeholder" role="note">
            QR Code E-Tiket belum tersedia. Tiket akan diterbitkan setelah modul E-Tiket diintegrasikan.
        </div>

        <div class="etp-transaction">
            <span>No. Transaksi</span>
            <strong>{{ $order->code }}</strong>
        </div>

        <dl class="etp-details">
            <div><dt>Nama Pemesan</dt><dd>{{ $order->booker_name }}</dd></div>
            <div><dt>Jenis Wisatawan</dt><dd>{{ $visitorType }}</dd></div>
            <div><dt>Tanggal Kunjungan</dt><dd>{{ $order->visit_date->locale('id')->translatedFormat('d M Y') }}</dd></div>
            <div><dt>Jenis Tiket</dt><dd>{{ $ticketNames ?: 'Tiket' }}</dd></div>
            <div><dt>Jumlah Tiket</dt><dd>{{ $quantityText }} ({{ $ticketCount }} tiket)</dd></div>
            <div><dt>Total Pembayaran</dt><dd>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</dd></div>
            <div><dt>Metode Bayar</dt><dd>{{ strtoupper($order->payment->method) }}</dd></div>
            <div><dt>Status Pembayaran</dt><dd>Lunas</dd></div>
            <div><dt>Status Tiket</dt><dd>Belum diterbitkan</dd></div>
        </dl>

        <p class="etp-note">
            Ini adalah halaman informasi pesanan sementara, bukan tiket masuk yang dapat dipindai.
            QR Code untuk check-in dan unduhan PDF belum tersedia.
        </p>
    </article>

    <div class="etp-actions" aria-label="Fitur E-Tiket yang belum tersedia">
        <button type="button" disabled>Download E-Tiket (PDF)</button>
        <button type="button" disabled>Lihat QR Code</button>
    </div>

    <nav class="etp-back" aria-label="Navigasi setelah pembayaran">
        <a href="{{ route('ticket-purchase.orders') }}">Lihat Pesanan Saya</a>
        <a href="{{ route('wisatawan.beranda') }}">← Kembali ke Beranda</a>
    </nav>
</main>
@endsection