@extends('layouts.app')

@section('title', 'Dashboard Petugas - SIMTicketingKRM')

@push('styles')
    @include('partials.ticket-payment-styles')
@endpush

@section('content')
<div class="purchase-page krm-container">

    <div class="purchase-heading">
        <h1>Dashboard Petugas</h1>
        <p>Kelola pembelian, pembayaran, dan pemeriksaan tiket pengunjung.</p>
    </div>

    {{-- FITUR 1: PEMBELIAN TIKET DI LOKET --}}
    <section class="krm-card payment-card">
        <h2>Pembelian Tiket di Loket</h2>

        <p class="mb-4">
            Layani pembelian tiket pengunjung secara langsung
            melalui loket.
        </p>

        <a
            href="{{ route('petugas.loket.index') }}"
            class="krm-btn krm-btn-primary"
        >
            Buka Loket Tiket
        </a>
    </section>

    {{-- FITUR 2: VERIFIKASI PEMBAYARAN --}}
    <section class="krm-card payment-card mt-6">
        <h2>Verifikasi Pembayaran</h2>

        <p class="mb-4">
            Periksa status pembayaran dan konfirmasikan
            penerimaan pembayaran Cash.
        </p>

        <a
            href="{{ route('ticket-payment.staff.index') }}"
            class="krm-btn krm-btn-primary"
        >
            Verifikasi Pembayaran
        </a>
    </section>

    {{-- FITUR 3: VALIDASI E-TIKET --}}
    <section class="krm-card payment-card mt-6">
        <h2>Validasi E-Tiket</h2>

        <p class="mb-4">
            Periksa kode tiket, status pembayaran, dan
            kesesuaian tanggal kunjungan pengunjung.
        </p>

        <a
            href="{{ route('petugas.validasi-tiket.index') }}"
            class="krm-btn krm-btn-primary"
        >
            Periksa E-Tiket
        </a>
    </section>

    <div class="purchase-actions">
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="krm-btn">
                Logout
            </button>
        </form>
    </div>

</div>
@endsection
