@extends('layouts.app')
@section('title', 'Verifikasi Pembayaran - Petugas')
@push('styles') @include('partials.ticket-payment-styles') @endpush
@section('content')
<div class="purchase-page krm-container">
    <nav class="purchase-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('petugas.dashboard') }}">Dashboard Petugas</a><span>/</span><span>Pembayaran</span></nav>
    <div class="purchase-heading"><h1>Verifikasi Pembayaran</h1><p>Periksa bukti QRIS atau konfirmasikan penerimaan cash di loket.</p></div>
    <div class="payment-list">
        @forelse($payments as $payment)
            <article class="krm-card payment-card payment-row"><div><h2>{{ $payment->order->code }}</h2><p>{{ $payment->order->booker_name }} · {{ strtoupper($payment->method) }}</p><p>Rp {{ number_format((float)$payment->amount, 2, ',', '.') }}</p><span class="payment-label">{{ $payment->statusLabel() }}</span></div><a class="krm-btn krm-btn-primary" href="{{ route('ticket-payment.staff.show', $payment) }}">Periksa Pembayaran</a></article>
        @empty
            <div class="krm-card payment-card"><h2>Belum ada pembayaran</h2><p>Pembayaran akan muncul setelah Wisatawan memilih metode.</p></div>
        @endforelse
    </div>
    <div class="purchase-pagination">{{ $payments->links() }}</div>
</div>
@endsection
