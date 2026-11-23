@extends('layouts.petugas')

@section('title', 'Transaksi Berhasil - Petugas')

@push('styles')
<style>
    .counter-success {
        max-width: 690px;
        margin: 0 auto;
        padding: 65px 20px 80px;
        color: #153d31;
    }
    .counter-success-heading { text-align: center; margin-bottom: 24px; }
    .counter-success-check {
        width: 80px;
        height: 80px;
        margin: 0 auto 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #e1f0e6;
        color: #257753;
    }
    .counter-success-check svg { width: 43px; height: 43px; }
    .counter-success-heading h1 { font-size: 30px; font-weight: 700; margin-bottom: 6px; }
    .counter-success-heading p { color: #788980; font-size: 14px; }
    .counter-success-card { background: #fff; border: 1px solid #d3dfd7; padding: 22px; }
    .counter-success-reference {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #d9e4dc;
    }
    .counter-success-reference small { display: block; color: #788980; margin-bottom: 7px; }
    .counter-success-reference strong { font-size: 16px; overflow-wrap: anywhere; }
    .counter-success-badge {
        background: #e1f0e6;
        border: 1px solid #bfd9c7;
        padding: 6px 11px;
        color: #326c4c;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }
    .counter-success-details { margin-top: 8px; }
    .counter-success-row {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        padding: 12px 0;
        border-bottom: 1px solid #edf1ee;
        font-size: 14px;
    }
    .counter-success-row span { color: #788980; }
    .counter-success-row strong { text-align: right; font-weight: 600; overflow-wrap: anywhere; }
    .counter-success-actions { display: grid; gap: 9px; margin-top: 24px; }
    .counter-success-button {
        display: inline-flex;
        gap: 10px;
        align-items: center;
        justify-content: center;
        min-height: 47px;
        padding: 12px 18px;
        border: 1px solid #07513d;
        color: #07513d;
        background: #fff;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: background 0.2s;
    }
    .counter-success-button svg { width: 19px; height: 19px; flex-shrink: 0; }
    .counter-success-button:hover { background: #edf5ef; }
    .counter-success-button.primary { background: #07513d; color: #fff; }
    .counter-success-button.primary:hover { background: #063f30; }
    .counter-success-back {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-align: center;
        margin-top: 22px;
        color: #07513d;
        font-weight: 600;
        text-decoration: underline;
    }
    .counter-success-back svg { width: 17px; height: 17px; flex-shrink: 0; }
    @media (max-width: 640px) {
        .counter-success { padding: 35px 15px 60px; }
        .counter-success-heading h1 { font-size: 25px; }
        .counter-success-row { gap: 12px; font-size: 13px; }
        .counter-success-card { padding: 16px; }
    }
</style>
@endpush

@section('content')
@php
    $order = $payment->order;

    $adultCount = $order->items->sum('adult_quantity');
    $childCount = $order->items->sum('child_quantity');

    $ticketNames = $order->items
        ->pluck('ticket_name')
        ->unique()
        ->implode(', ');

    $quantities = collect([
        $adultCount > 0 ? 'Dewasa x' . $adultCount : null,
        $childCount > 0 ? 'Anak x' . $childCount : null,
    ])->filter()->implode(', ');
@endphp

<main class="counter-success">
    <div class="counter-success-heading">
        <div class="counter-success-check" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6 9 17l-5-5"/>
            </svg>
        </div>
        <h1>Transaksi Berhasil!</h1>
        <p>Pembayaran telah dikonfirmasi dan E-Tiket berhasil diterbitkan.</p>
    </div>

    <section class="counter-success-card">
        <div class="counter-success-reference">
            <div>
                <small>No. Transaksi</small>
                <strong>{{ $payment->code }}</strong>
            </div>
            <span class="counter-success-badge">LUNAS</span>
        </div>

        <div class="counter-success-details">
            <div class="counter-success-row">
                <span>Pengunjung</span>
                <strong>{{ $order->booker_name }}</strong>
            </div>
            <div class="counter-success-row">
                <span>Jenis Tiket</span>
                <strong>{{ $ticketNames }}</strong>
            </div>
            <div class="counter-success-row">
                <span>Jumlah Tiket</span>
                <strong>{{ $quantities }}</strong>
            </div>
            <div class="counter-success-row">
                <span>Tanggal Kunjungan</span>
                <strong>{{ $order->visit_date->format('d/m/Y') }}</strong>
            </div>
            <div class="counter-success-row">
                <span>Total Bayar</span>
                <strong>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</strong>
            </div>

            @if($payment->method === 'cash')
                <div class="counter-success-row">
                    <span>Uang Diterima</span>
                    <strong>
                        @if($payment->cash_received_amount !== null)
                            Rp {{ number_format((float) $payment->cash_received_amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </strong>
                </div>
                <div class="counter-success-row">
                    <span>Kembalian</span>
                    <strong>
                        @if($payment->cash_change_amount !== null)
                            Rp {{ number_format((float) $payment->cash_change_amount, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </strong>
                </div>
            @endif

            <div class="counter-success-row">
                <span>Metode</span>
                <strong>{{ strtoupper($payment->method) }}</strong>
            </div>
            <div class="counter-success-row">
                <span>Petugas Kasir</span>
                <strong>{{ $order->staff?->name ?? '-' }}</strong>
            </div>
            <div class="counter-success-row">
                <span>Waktu</span>
                <strong>{{ $payment->paid_at?->timezone('Asia/Jakarta')->format('d/m/Y, H:i') }} WIB</strong>
            </div>
        </div>
    </section>

    <div class="counter-success-actions">
        <a href="{{ route('petugas.loket.print', $payment->id) }}" target="_blank" rel="noopener noreferrer" class="counter-success-button primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 9V2h12v7"/>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                <path d="M6 14h12v8H6z"/>
            </svg>
            <span>Print Tiket</span>
        </a>

        <a href="{{ route('petugas.validasi-tiket.index') }}" class="counter-success-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M7 3H5a2 2 0 0 0-2 2v2"/>
                <path d="M17 3h2a2 2 0 0 1 2 2v2"/>
                <path d="M21 17v2a2 2 0 0 1-2 2h-2"/>
                <path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                <path d="M7 12h10"/>
            </svg>
            <span>Scan &amp; Check-In Sekarang</span>
        </a>

        <a href="{{ route('petugas.loket.index') }}" class="counter-success-button">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14"/>
                <path d="M5 12h14"/>
            </svg>
            <span>Transaksi Baru</span>
        </a>
    </div>

    <a href="{{ route('petugas.dashboard') }}" class="counter-success-back">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m15 18-6-6 6-6"/>
        </svg>
        <span>Kembali ke Dashboard</span>
    </a>
</main>
@endsection
