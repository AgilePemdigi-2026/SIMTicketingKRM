@extends('layouts.petugas')

@section('title', 'Pembayaran QRIS Loket - Petugas')

@push('styles')
<style>
    .counter-qris-page {
        max-width: 1190px;
        margin: 0 auto;
        padding: 42px 24px 80px;
        color: #153d31;
    }
    .counter-qris-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #a7bcaf;
        margin-bottom: 22px;
    }
    .counter-qris-header h1 { font-size: 30px; font-weight: 700; margin: 0 0 8px; }
    .counter-qris-header p { font-size: 14px; color: #75867c; margin: 0; }
    .counter-qris-grid { display: grid; grid-template-columns: 1fr 1.05fr; gap: 20px; align-items: start; }
    .counter-qris-stack { display: grid; gap: 18px; }
    .counter-qris-card { background: #fff; border: 1px solid #d2ded6; padding: 23px; }
    .counter-qris-card h2 {
        font-size: 15px;
        font-weight: 700;
        letter-spacing: .04em;
        border-bottom: 1px solid #d8e4dc;
        padding-bottom: 16px;
        margin: 0 0 14px;
    }
    .counter-qris-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: 18px;
        padding: 13px 0;
        border-bottom: 1px solid #ecf0ed;
        font-size: 14px;
    }
    .counter-qris-row span { color: #788981; }
    .counter-qris-row strong { text-align: right; font-weight: 600; overflow-wrap: anywhere; }
    .counter-qris-row.total { border: 0; padding-top: 18px; font-size: 18px; }
    .counter-qris-row.total strong { font-size: 23px; color: #07513d; }
    .counter-qris-status { display: grid; gap: 9px; }
    .counter-qris-status-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 13px;
        border: 1px solid #dce5de;
        font-size: 13px;
    }
    .counter-qris-status-item .dot {
        width: 9px; height: 9px; flex: 0 0 9px;
        border-radius: 50%; background: #a8b7ae; margin-top: 5px;
    }
    .counter-qris-status-item strong { display: block; font-weight: 600; margin-bottom: 4px; }
    .counter-qris-status-item small { display: block; color: #809087; line-height: 1.5; }
    .counter-qris-status-item.waiting { border-color: #e4c68b; background: #fff9ec; }
    .counter-qris-status-item.waiting .dot { background: #d79c28; }
    .counter-qris-status-item.paid { border-color: #b4d4bd; background: #e5f2e7; }
    .counter-qris-status-item.paid .dot { background: #07513d; }
    .counter-qris-status-item.expired { border-color: #e5b8b8; background: #fff1ef; }
    .counter-qris-status-item.expired .dot { background: #bd5149; }
    .counter-qris-checkout { text-align: center; }
    .counter-qris-reference {
        display: flex; justify-content: space-between; align-items: flex-start;
        gap: 15px; text-align: left; margin-bottom: 18px;
    }
    .counter-qris-reference small { display: block; color: #788981; margin-bottom: 6px; }
    .counter-qris-reference strong { font-size: 14px; overflow-wrap: anywhere; }
    .counter-qris-badge { font-size: 12px; padding: 7px 10px; border: 1px solid #edcf8d; background: #fff7e4; color: #8b620e; white-space: nowrap; }
    .counter-qris-badge.paid { border-color: #bcd6c4; background: #e5f2e7; color: #07513d; }
    .counter-qris-badge.expired { border-color: #e5b8b8; background: #fff1ef; color: #a93631; }
    .counter-qris-image {
        display: block; width: 280px; max-width: 100%; height: auto;
        margin: 22px auto 18px; padding: 12px; border: 1px solid #dce5de;
        background: #fff;
    }
    .counter-qris-message { padding: 30px 18px; margin: 18px 0; border: 1px dashed #c4d4c9; background: #f6f9f7; }
    .counter-qris-message .symbol { font-size: 43px; line-height: 1; margin-bottom: 14px; }
    .counter-qris-message strong { display: block; font-size: 17px; margin-bottom: 8px; }
    .counter-qris-message p { color: #74877b; font-size: 13px; line-height: 1.6; margin: 0; }
    .counter-qris-timer { font-size: 32px; font-weight: 700; letter-spacing: .06em; margin: 7px 0 2px; }
    .counter-qris-note { font-size: 13px; color: #778a7e; line-height: 1.6; margin: 12px 0 0; }
    .counter-qris-actions { display: grid; gap: 9px; margin-top: 18px; }
    .counter-qris-actions form { margin: 0; }
    .counter-qris-btn {
        width: 100%; display: flex; justify-content: center; align-items: center;
        min-height: 49px; text-align: center; padding: 12px 16px; gap: 9px;
        background: #fff; border: 1px solid #07513d; color: #07513d;
        font: inherit; font-size: 14px; font-weight: 600; text-decoration: none;
        cursor: pointer;
    }
    .counter-qris-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
    .counter-qris-btn:hover { background: #eef5ef; }
    .counter-qris-btn.primary { background: #07513d; color: #fff; }
    .counter-qris-btn.primary:hover { background: #043d2d; }
    .counter-qris-feedback {
        border: 1px solid #c3ddc9; background: #edf6ee; color: #22583e;
        padding: 13px 17px; margin-bottom: 18px; font-size: 14px;
    }
    .counter-qris-feedback.error { border-color: #e6b6b6; background: #fff2f1; color: #a82e2e; }
    @media (max-width: 800px) {
        .counter-qris-grid { grid-template-columns: 1fr; }
        .counter-qris-header { flex-direction: column; }
        .counter-qris-page { padding: 24px 15px 54px; }
        .counter-qris-header h1 { font-size: 25px; }
    }
</style>
@endpush

@section('content')
@php
    $order = $payment->order;
    $isPaid = $payment->status === 'paid' && $order->status === 'paid';
    $attemptStatus = $attempt?->status;
    $canRetry = in_array($attemptStatus, ['expired', 'cancelled', 'failed'], true);
    $isWaiting = $attemptStatus === 'pending';
    $needsReview = in_array($attemptStatus, ['initiating', 'initiation_unknown'], true);
    $isExpired = $canRetry;
    $ticketNames = $order->items->pluck('ticket_name')->unique()->implode(', ');
    $adultCount = $order->items->sum('adult_quantity');
    $childCount = $order->items->sum('child_quantity');
    $quantities = collect([
        $adultCount > 0 ? 'Dewasa x' . $adultCount : null,
        $childCount > 0 ? 'Anak x' . $childCount : null,
    ])->filter()->implode(', ');
@endphp

<div class="counter-qris-page">
    <header class="counter-qris-header">
        <div>
            <h1>Pembayaran QRIS</h1>
            <p>Minta pengunjung memindai QR Code Midtrans untuk menyelesaikan pembayaran.</p>
        </div>
        <a href="{{ route('petugas.loket.index') }}" class="counter-qris-btn" style="width:auto;min-width:115px;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg><span>Kembali ke Loket</span></a>
    </header>

    @if(session('payment_updated'))
        <div class="counter-qris-feedback" role="status">{{ session('payment_updated') }}</div>
    @endif

    @if($errors->any())
        <div class="counter-qris-feedback error" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="counter-qris-grid">
        <div class="counter-qris-stack">
            <section class="counter-qris-card">
                <h2>RINGKASAN TRANSAKSI</h2>
                <div class="counter-qris-row"><span>Pengunjung</span><strong>{{ $order->booker_name }}</strong></div>
                <div class="counter-qris-row"><span>Jenis Tiket</span><strong>{{ $ticketNames }}</strong></div>
                <div class="counter-qris-row"><span>Jumlah Tiket</span><strong>{{ $quantities }}</strong></div>
                <div class="counter-qris-row"><span>Tanggal Kunjungan</span><strong>{{ $order->visit_date->format('d M Y') }}</strong></div>
                <div class="counter-qris-row total"><span>Total Tagihan</span><strong>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</strong></div>
            </section>

            <section class="counter-qris-card">
                <h2>STATUS PEMBAYARAN</h2>
                <div class="counter-qris-status">
                    <div class="counter-qris-status-item {{ !$isPaid && !$isExpired ? 'waiting' : '' }}">
                        <span class="dot" aria-hidden="true"></span>
                        <div><strong>Menunggu Pembayaran</strong><small>QR ditampilkan kepada pengunjung untuk dipindai</small></div>
                    </div>
                    <div class="counter-qris-status-item {{ $isPaid ? 'paid' : '' }}">
                        <span class="dot" aria-hidden="true"></span>
                        <div><strong>Lunas</strong><small>Pembayaran terverifikasi oleh Midtrans</small></div>
                    </div>
                    <div class="counter-qris-status-item {{ $isExpired ? 'expired' : '' }}">
                        <span class="dot" aria-hidden="true"></span>
                        <div><strong>Kedaluwarsa / Gagal</strong><small>QR tidak dapat digunakan, buat QR baru setelah status dipastikan</small></div>
                    </div>
                </div>
            </section>
        </div>

        <div class="counter-qris-stack">
            <section class="counter-qris-card counter-qris-checkout">
                <div class="counter-qris-reference">
                    <div>
                        <small>No. Transaksi</small>
                        <strong>{{ $payment->code }}</strong>
                    </div>
                    <span class="counter-qris-badge {{ $isPaid ? 'paid' : ($isExpired ? 'expired' : '') }}">
                        {{ $isPaid ? 'Lunas' : ($isExpired ? 'Kedaluwarsa / Gagal' : 'Menunggu Pembayaran') }}
                    </span>
                </div>

                @if($isPaid)
                    <div class="counter-qris-message" role="status">
                        <div class="symbol" aria-hidden="true">✓</div>
                        <strong>Pembayaran Berhasil!</strong>
                        <p>Pembayaran tercatat Lunas. Lanjutkan ke halaman transaksi berhasil untuk mencetak E-Tiket.</p>
                    </div>
                    <div class="counter-qris-actions">
                        <a href="{{ route('petugas.loket.success', $payment->id) }}" class="counter-qris-btn primary"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span>Konfirmasi &amp; Cetak Tiket</span></a>
                        <a href="{{ route('petugas.validasi-tiket.index') }}" class="counter-qris-btn"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3H5a2 2 0 0 0-2 2v2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><path d="M7 12h10"/></svg><span>Scan Tiket untuk Check-In</span></a>
                    </div>
                @elseif($attempt === null)
                    <div class="counter-qris-message">
                        <div class="symbol" aria-hidden="true">▦</div>
                        <strong>QRIS Belum Dibuat</strong>
                        <p>Buat QR Midtrans untuk transaksi ini. Pengunjung dapat memindai QR setelah berhasil diterbitkan.</p>
                    </div>
                    <div class="counter-qris-actions">
                        <form action="{{ route('petugas.loket.qris.create', $payment->id) }}" method="POST">
                            @csrf
                            <button class="counter-qris-btn primary" type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM21 14v3M17 21h4M14 21v-2"/></svg><span>Buat QR Pembayaran</span></button>
                        </form>
                    </div>
                @elseif($canRetry)
                    <div class="counter-qris-message">
                        <div class="symbol" aria-hidden="true">×</div>
                        <strong>QR Tidak Berlaku</strong>
                        <p>Midtrans telah mencatat transaksi ini sebagai kedaluwarsa, dibatalkan, atau gagal. QR baru bisa dibuat untuk tagihan yang sama.</p>
                    </div>
                    <div class="counter-qris-actions">
                        <form action="{{ route('petugas.loket.qris.create', $payment->id) }}" method="POST">
                            @csrf
                            <button class="counter-qris-btn primary" type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 11a8 8 0 0 0-14-5L4 8"/><path d="M4 4v4h4"/><path d="M4 13a8 8 0 0 0 14 5l2-2"/><path d="M20 20v-4h-4"/></svg><span>Buat QR Baru</span></button>
                        </form>
                        <a href="{{ route('petugas.loket.index') }}" class="counter-qris-btn"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg><span>Kembali ke Pilih Tiket</span></a>
                    </div>
                @elseif($isWaiting && $attempt->qr_image_url)
                    <img
                        class="counter-qris-image"
                        src="{{ $attempt->qr_image_url }}"
                        alt="QR Code pembayaran Midtrans transaksi loket"
                        width="280"
                        height="280"
                    >
                    <p class="counter-qris-note">Batas Waktu Pembayaran</p>
                    <div class="counter-qris-timer" id="counter-qris-countdown" role="timer">--:--</div>
                    <p class="counter-qris-note" id="counter-qris-expiry-note">
                        Minta pengunjung membuka aplikasi pembayaran dan memindai QRIS di atas.
                    </p>
                    <div class="counter-qris-actions">
                        <form action="{{ route('petugas.loket.qris.sync', $payment->id) }}" method="POST">
                            @csrf
                            <button class="counter-qris-btn" type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 11a8 8 0 0 0-14-5L4 8"/><path d="M4 4v4h4"/><path d="M4 13a8 8 0 0 0 14 5l2-2"/><path d="M20 20v-4h-4"/></svg><span>Refresh Status Pembayaran</span></button>
                        </form>
                    </div>
                    <p class="counter-qris-note">Status Lunas hanya diperbarui melalui konfirmasi Midtrans. Jika waktu habis, refresh status untuk memeriksa kedaluwarsa sebelum membuat QR baru.</p>
                @else
                    <div class="counter-qris-message">
                        <div class="symbol" aria-hidden="true">…</div>
                        <strong>{{ $needsReview ? 'Status QR Perlu Diperiksa' : 'QR Belum Siap Ditampilkan' }}</strong>
                        <p>Jangan membuat pembayaran Midtrans baru sebelum status transaksi ini dipastikan. Periksa log dan status transaksi gateway bila diperlukan.</p>
                    </div>
                    @if($attempt?->provider_payment_id)
                        <div class="counter-qris-actions">
                            <form action="{{ route('petugas.loket.qris.sync', $payment->id) }}" method="POST">
                                @csrf
                                <button class="counter-qris-btn" type="submit"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 11a8 8 0 0 0-14-5L4 8"/><path d="M4 4v4h4"/><path d="M4 13a8 8 0 0 0 14 5l2-2"/><path d="M20 20v-4h-4"/></svg><span>Periksa Status Midtrans</span></button>
                            </form>
                        </div>
                    @endif
                @endif
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($isWaiting && $attempt?->qr_image_url)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const countdown = document.getElementById('counter-qris-countdown');
    const note = document.getElementById('counter-qris-expiry-note');
    const expiryIso = @json($attempt->expires_at?->toIso8601String());

    if (!countdown) return;
    if (!expiryIso) {
        countdown.textContent = 'Lihat batas waktu pada Midtrans';
        countdown.style.fontSize = '15px';
        return;
    }

    const expiryMs = Date.parse(expiryIso);
    if (!Number.isFinite(expiryMs)) {
        countdown.textContent = '--:--';
        return;
    }

    function renderRemaining() {
        const remaining = Math.max(0, Math.ceil((expiryMs - Date.now()) / 1000));
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        countdown.textContent = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

        if (remaining === 0 && note) {
            note.textContent = 'Batas waktu QR terlewati. Klik Refresh Status Pembayaran agar sistem memeriksa status sebenarnya di Midtrans.';
        }
    }

    renderRemaining();
    setInterval(renderRemaining, 1000);
});
</script>
@endif
@endpush
