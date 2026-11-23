@extends('layouts.petugas')

@section('title', 'Check-In Berhasil - Petugas')

@push('styles')
<style>
    .checkin-page { width: min(100% - 32px, 830px); margin: 0 auto; padding: 65px 0 85px; color: #153e32; font-family: 'Plus Jakarta Sans', sans-serif; }
    .checkin-heading { display: grid; justify-items: center; gap: 13px; text-align: center; margin-bottom: 27px; }
    .checkin-icon { width: 87px; height: 87px; border-radius: 50%; display: grid; place-items: center; color: #258356; background: #e0f2e8; }
    .checkin-heading h1 { margin: 7px 0 0; font-size: clamp(26px, 2.8vw, 34px); font-weight: 800; letter-spacing: -.6px; }
    .checkin-heading p { color: #879e8e; font-size: 13px; margin: 0; }
    .checkin-status { display: inline-block; color: #237d50; background: #e3f2e8; border: 1px solid #bfd9c7; padding: 7px 13px; font-size: 11px; font-weight: 700; }
    .checkin-card { border: 1px solid #d6e4d9; background: #fff; padding: 24px 25px; margin: 0 0 24px; }
    .checkin-number { text-align: center; padding: 2px 0 17px; border-bottom: 1px solid #d7e2da; }
    .checkin-number span { display: block; color: #869a8c; font-size: 12px; margin-bottom: 7px; }
    .checkin-number strong { font-size: 19px; font-weight: 800; overflow-wrap: anywhere; }
    .checkin-details { margin: 8px 0 0; }
    .checkin-details div { display: flex; justify-content: space-between; align-items: start; gap: 16px; padding: 14px 0; border-bottom: 1px solid #eaf0eb; }
    .checkin-details dt { font-size: 13px; color: #81968a; }
    .checkin-details dd { font-size: 13px; text-align: right; font-weight: 750; margin: 0; overflow-wrap: anywhere; }
    .checkin-note { margin: 15px 0 0; padding: 13px 16px; background: #f6faf6; border: 1px dashed #cadbd0; color: #789181; font-size: 12px; }
    .checkin-actions { display: grid; gap: 10px; }
    .checkin-btn { display: flex; justify-content: center; align-items: center; min-height: 51px; text-decoration: none; font-family: inherit; font-size: 13px; font-weight: 800; color: #fff; background: #07513d; border: 1px solid #07513d; padding: 14px; }
    .checkin-btn.secondary { background: #fff; color: #07513d; }
    .checkin-btn:hover { background: #063d2f; color: #fff; }
    .checkin-btn.secondary:hover { background: #eef6ef; color: #07513d; }
    @media (max-width: 600px) { .checkin-page { padding-top: 34px; } .checkin-card { padding: 17px; } .checkin-number strong { font-size: 15px; } }
</style>
@endpush

@section('content')
<div class="checkin-page">
    <header class="checkin-heading">
        <div class="checkin-icon" aria-hidden="true">
            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m4 12 5 5L20 6"/></svg>
        </div>
        <h1>Check-In Berhasil!</h1>
        <p>Pengunjung telah berhasil divalidasi.</p>
        <span class="checkin-status">SUDAH CHECK-IN</span>
    </header>

    <section class="checkin-card" aria-label="Bukti Check-In">
        <div class="checkin-number">
            <span>No. Transaksi</span>
            <strong>{{ $order?->code ?? '-' }}</strong>
        </div>
        <dl class="checkin-details">
            <div><dt>Nama Pengunjung</dt><dd>{{ $ticket->visitor?->name ?? '-' }}</dd></div>
            <div><dt>Jenis Tiket</dt><dd>{{ $ticket->item?->ticket_name ?? '-' }}</dd></div>
            <div><dt>Jumlah Tiket</dt><dd>1 tiket dari {{ $order?->issuedTickets?->count() ?? 1 }} ({{ $ticket->visitor?->age_category === 'adult' ? 'Dewasa' : ($ticket->visitor?->age_category === 'child' ? 'Anak' : 'Pengunjung') }})</dd></div>
            <div><dt>Waktu Check-In</dt><dd>{{ $ticket->used_at?->timezone('Asia/Jakarta')->format('d M Y, H:i') ?? '-' }} WIB</dd></div>
            <div><dt>Nama Petugas</dt><dd>{{ $staffName }}</dd></div>
            <div><dt>Status</dt><dd>Sudah Check-In</dd></div>
        </dl>
        <p class="checkin-note">E-Tiket ini telah digunakan dan tidak dapat dipakai untuk check-in kedua kalinya.</p>
    </section>

    <nav class="checkin-actions" aria-label="Aksi selanjutnya">
        <a class="checkin-btn" href="{{ route('petugas.validasi-tiket.index') }}">Kembali ke Scanner</a>
        <a class="checkin-btn secondary" href="{{ route('petugas.dashboard') }}">Kembali ke Dashboard</a>
    </nav>
</div>
@endsection
