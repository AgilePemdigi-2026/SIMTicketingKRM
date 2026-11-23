@extends('layouts.petugas')

@section('title', 'Pembayaran Dikonfirmasi - Bayar Cash Booking')

@push('styles')
<style>
    /* Sukses Cash Booking, isolated CSS agar loket/QRIS tidak berubah. */
    .bcb-success, .bcb-success * { box-sizing: border-box; }
    .bcb-success {
        width: min(calc(100% - 32px), 720px);
        margin: 0 auto;
        padding: 62px 0 92px;
        color: #153d31;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .bcb-success-heading { text-align: center; margin-bottom: 24px; }
    .bcb-success-icon {
        display: grid; place-items: center;
        width: 82px; height: 82px; margin: 0 auto 26px;
        border-radius: 50%; background: #e0f0e6; color: #2b7955;
    }
    .bcb-success-icon svg { width: 39px; height: 39px; }
    .bcb-success-heading h1 { margin: 0 0 8px; font-size: 29px; font-weight: 800; line-height: 1.3; }
    .bcb-success-heading p { margin: 0; font-size: 13px; color: #87978d; }
    .bcb-success-card { padding: 22px; background: #fff; border: 1px solid #d2ded5; margin-bottom: 16px; }
    .bcb-success-card-title { margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .015em; }
    .bcb-success-steps { margin-top: 14px; }
    .bcb-success-step {
        min-height: 55px; padding: 9px 12px;
        display: flex; align-items: center; gap: 12px;
        border: 1px solid #d4e0d6;
        color: #73897b; font-size: 12px; background: #fff;
    }
    .bcb-success-step.current { background: #e8f2e7; border-color: #91bca0; color: #163f31; font-weight: 800; }
    .bcb-success-step.done { color: #6d8677; }
    .bcb-success-step-number {
        width: 29px; height: 29px; flex: 0 0 29px;
        display: grid; place-items: center;
        border: 1px solid #becfc2; color: #567265; font-weight: 750;
    }
    .bcb-success-step.current .bcb-success-step-number,
    .bcb-success-step.done .bcb-success-step-number {
        border-color: #2a795a; background: #2a795a; color: #fff;
    }
    .bcb-success-step-number svg { width: 18px; height: 18px; }
    .bcb-success-arrow { display: block; height: 16px; padding-left: 17px; font-size: 13px; line-height: 16px; color: #a2b7a8; }
    .bcb-success-status {
        padding-bottom: 16px; border-bottom: 1px solid #d8e4dc;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .bcb-success-status small { display: block; color: #83958a; font-size: 12px; margin-bottom: 5px; }
    .bcb-success-status strong { color: #153c30; font-size: 17px; letter-spacing: .03em; }
    .bcb-success-badge { padding: 7px 11px; color: #2a6848; border: 1px solid #bedec8; background: #e3f2e7; font-size: 11px; font-weight: 800; }
    .bcb-success-details { margin: 5px 0 0; }
    .bcb-success-row {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 20px;
        padding: 11px 0; border-bottom: 1px solid #edf1ee; font-size: 12px;
    }
    .bcb-success-row dt { color: #83958a; flex: 0 0 41%; }
    .bcb-success-row dd { margin: 0; color: #214c39; flex: 1; text-align: right; font-weight: 750; overflow-wrap: anywhere; }
    .bcb-success-actions { display: grid; gap: 9px; margin: 24px 0 0; }
    .bcb-success-button {
        min-height: 50px; padding: 12px 17px;
        border: 1px solid #07513d; background: #fff; color: #07513d;
        display: inline-flex; align-items: center; justify-content: center; gap: 9px;
        font: inherit; font-size: 13px; font-weight: 750; text-align: center; text-decoration: none;
    }
    .bcb-success-button:hover { background: #f0f6f0; color: #07513d; }
    .bcb-success-button.primary { background: #07513d; color: #fff; }
    .bcb-success-button.primary:hover { background: #063e30; color: #fff; }
    .bcb-success-button svg { width: 18px; height: 18px; flex-shrink: 0; }
    .bcb-success-button.disabled { color: #819288; border-color: #d3ded5; background: #f4f6f3; cursor: not-allowed; }
    .bcb-success-back { display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 26px; color: #07513d; text-decoration: underline; font-size: 13px; font-weight: 700; }
    .bcb-success-back:hover { color: #063e30; }
    .bcb-success-back svg { width: 15px; height: 15px; }
    .bcb-success-hint { color: #738579; font-size: 11px; text-align: center; margin: 6px 0 0; }
    @media (max-width: 640px) {
        .bcb-success { padding: 36px 0 55px; }
        .bcb-success-heading h1 { font-size: 24px; }
        .bcb-success-icon { width: 68px; height: 68px; margin-bottom: 20px; }
        .bcb-success-card { padding: 16px; }
        .bcb-success-row { gap: 12px; font-size: 11px; }
        .bcb-success-row dt { flex-basis: 38%; }
    }
</style>
@endpush

@section('content')
@php
    $order = $payment->order;
    $adultCount = (int) $order->items->sum('adult_quantity');
    $childCount = (int) $order->items->sum('child_quantity');
    $quantity = $adultCount + $childCount;
    $quantityText = collect([
        $adultCount > 0 ? "Dewasa x{$adultCount}" : null,
        $childCount > 0 ? "Anak x{$childCount}" : null,
    ])->filter()->implode(', ');
    $visitorName = $order->items->flatMap(fn ($item) => $item->visitors)->first()?->name ?? $order->booker_name;
    $ticketNames = $order->items->pluck('ticket_name')->filter()->unique()->implode(', ');
    $cashCode = 'CASH-' . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
    $allCheckedIn = $order->issuedTickets->isNotEmpty()
        && $order->issuedTickets->every(fn ($ticket) => $ticket->status === 'used');
    $hasIssuedTickets = $order->issuedTickets->isNotEmpty();
@endphp
<main class="bcb-success" aria-labelledby="bcb-success-heading">
    <header class="bcb-success-heading">
        <div class="bcb-success-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m4 12 5 5L20 6"/></svg>
        </div>
        <h1 id="bcb-success-heading">Pembayaran Dikonfirmasi!</h1>
        <p>Booking cash telah berhasil dibayar.</p>
    </header>

    <section class="bcb-success-card" aria-labelledby="bcb-success-flow">
        <h2 id="bcb-success-flow" class="bcb-success-card-title">ALUR BAYAR CASH BOOKING</h2>
        <div class="bcb-success-steps">
            <div class="bcb-success-step done">
                <span class="bcb-success-step-number" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg>
                </span>
                <span>Menunggu Pembayaran</span>
            </div>
            <span class="bcb-success-arrow" aria-hidden="true">↓</span>
            <div class="bcb-success-step {{ $allCheckedIn ? 'done' : 'current' }}">
                <span class="bcb-success-step-number" aria-hidden="true">2</span>
                <span>Lunas</span>
            </div>
            <span class="bcb-success-arrow" aria-hidden="true">↓</span>
            <div class="bcb-success-step {{ $allCheckedIn ? 'current' : '' }}">
                <span class="bcb-success-step-number" aria-hidden="true">3</span>
                <span>Check-In</span>
            </div>
        </div>
    </section>

    <section class="bcb-success-card" aria-label="Bukti konfirmasi pembayaran">
        <div class="bcb-success-status">
            <div>
                <small>Status</small>
                <strong>Pembayaran Berhasil</strong>
            </div>
            <span class="bcb-success-badge">LUNAS</span>
        </div>
        <dl class="bcb-success-details">
            <div class="bcb-success-row"><dt>No. Transaksi</dt><dd>{{ strtoupper((string) $order->code) }}</dd></div>
            <div class="bcb-success-row"><dt>Pengunjung</dt><dd>{{ $visitorName }}</dd></div>
            <div class="bcb-success-row"><dt>Kode Pembayaran Cash</dt><dd>{{ $cashCode }}</dd></div>
            <div class="bcb-success-row"><dt>Jenis Tiket</dt><dd>{{ $ticketNames !== '' ? $ticketNames : '-' }}</dd></div>
            <div class="bcb-success-row"><dt>Jumlah Tiket</dt><dd>{{ $quantity }} tiket{{ $quantityText !== '' ? ' (' . $quantityText . ')' : '' }}</dd></div>
            <div class="bcb-success-row"><dt>Total Bayar</dt><dd>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</dd></div>
            <div class="bcb-success-row"><dt>Uang Diterima</dt><dd>{{ $payment->cash_received_amount !== null ? 'Rp ' . number_format((float) $payment->cash_received_amount, 0, ',', '.') : '-' }}</dd></div>
            <div class="bcb-success-row"><dt>Kembalian</dt><dd>{{ $payment->cash_change_amount !== null ? 'Rp ' . number_format((float) $payment->cash_change_amount, 0, ',', '.') : '-' }}</dd></div>
            <div class="bcb-success-row"><dt>Waktu Bayar</dt><dd>{{ $payment->paid_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') ?? '-' }} WIB</dd></div>
            <div class="bcb-success-row"><dt>Petugas Kasir</dt><dd>{{ $cashierName ?? '-' }}</dd></div>
        </dl>
    </section>

    <div class="bcb-success-actions">
        <a href="{{ route('petugas.validasi-tiket.index') }}" class="bcb-success-button primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3H5a2 2 0 0 0-2 2v2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 12h10"/></svg>
            <span>Lanjut Scan Tiket &amp; Check-In</span>
        </a>
        @if($hasIssuedTickets)
            <a href="{{ route('ticket-payment.staff.booking-print', $payment->id) }}" target="_blank" rel="noopener noreferrer" class="bcb-success-button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5h-2M6 14h12v8H6z"/></svg>
                <span>Print Tiket</span>
            </a>
        @else
            <span class="bcb-success-button disabled" aria-disabled="true" title="E-Tiket belum terbit">Print Tiket (E-Tiket belum tersedia)</span>
            <p class="bcb-success-hint">Pembayaran sudah Lunas, tetapi E-Tiket belum ditemukan. Silakan periksa proses penerbitan.</p>
        @endif
    </div>
    <a href="{{ route('petugas.dashboard') }}" class="bcb-success-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
        <span>Kembali ke Dashboard</span>
    </a>
</main>
@endsection
