@extends('layouts.app')

@section('title', 'Pesanan Saya - Kebun Raya Mangrove Surabaya')

@push('styles')
    @include('wisatawan.ticket-purchase.styles')
@endpush

@section('content')
<div class="purchase-page krm-container">
    <nav class="purchase-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('wisatawan.beranda') }}">Beranda</a><span>/</span><span>Pesanan Saya</span></nav>
    <div class="purchase-heading"><h1>Pesanan Saya</h1><p>Lihat kembali detail pembelian dan status pesananmu.</p></div>
    <div class="purchase-actions"><a class="krm-btn krm-btn-primary" href="{{ route('ticket-purchase.index') }}">Beli Tiket</a></div>
    @forelse ($orders as $order)
        <article class="krm-card purchase-order-card">
            <div class="purchase-order-card-header"><h2>{{ $order->code }}</h2><span class="purchase-status">{{ $order->status === 'pending_payment' ? 'Menunggu pembayaran' : $order->status }}</span></div>
            <dl class="purchase-order-meta">
                <div><dt>Tanggal kunjungan</dt><dd>{{ $order->visit_date->format('d/m/Y') }}</dd></div>
                <div><dt>Total pesanan</dt><dd>Rp {{ number_format((float) $order->total, 2, ',', '.') }}</dd></div>
            </dl>
            <a class="krm-btn" href="{{ route('ticket-purchase.show', $order->code) }}">Lihat Detail Pesanan</a>
        </article>
    @empty
        <div class="krm-card purchase-empty"><h2>Belum ada pesanan</h2><p>Pesanan yang kamu simpan akan muncul di sini.</p></div>
    @endforelse
    <div class="purchase-pagination">{{ $orders->links() }}</div>
</div>
@endsection
