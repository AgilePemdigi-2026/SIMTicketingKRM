@extends('layouts.app')

@section('title', 'Ringkasan Pesanan - Kebun Raya Mangrove Surabaya')

@push('styles')
    @include('wisatawan.ticket-purchase.styles')
@endpush

@section('content')
<div class="purchase-page krm-container">
    <nav class="purchase-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('wisatawan.beranda') }}">Beranda</a><span>/</span><a href="{{ route('ticket-purchase.index') }}">Beli Tiket</a><span>/</span><span>Ringkasan Pesanan</span></nav>
    <ol class="purchase-steps" aria-label="Tahapan pembelian"><li class="completed"><span>✓</span>Pilih Tiket</li><li class="completed"><span>✓</span>Data Pengunjung</li><li aria-current="step"><span>3</span>Ringkasan Pesanan</li></ol>
    @if(session('purchase_created'))
        <div class="purchase-success" role="status"><strong>Pesanan berhasil disimpan.</strong><br>Pesananmu menunggu pembayaran.</div>
    @endif
    <div class="purchase-heading"><h1>Ringkasan Pesanan</h1><p>{{ $order->status === 'paid' ? 'Pembayaran terkonfirmasi. Data pesanan tersedia untuk penerbitan E-Tiket.' : 'Pesanan tersimpan. Lanjutkan pembayaran untuk proses berikutnya.' }}</p></div>
    <div class="krm-card">
        <dl class="purchase-order-meta">
            <div><dt>Kode pesanan</dt><dd>{{ $order->code }}</dd></div>
            <div><dt>Status pesanan</dt><dd><span class="purchase-status">{{ $order->payment?->statusLabel() ?? ($order->status === 'paid' ? 'Lunas' : ($order->status === 'pending_payment' ? 'Menunggu pembayaran' : $order->status)) }}</span></dd></div>
            <div><dt>Tanggal kunjungan</dt><dd>{{ $order->visit_date->format('d/m/Y') }}</dd></div>
            <div><dt>Jenis wisatawan</dt><dd>{{ $order->visitor_type === 'domestik' ? 'Domestik (WNI)' : 'Mancanegara (WNA)' }}</dd></div>
        </dl>
        <h2>Identitas Pemesan</h2>
        <dl class="purchase-order-meta">
            <div><dt>Nama lengkap</dt><dd>{{ $order->booker_name }}</dd></div>
            <div><dt>Nomor identitas</dt><dd>{{ $order->booker_identity_number }}</dd></div>
            <div><dt>Nomor telepon</dt><dd>{{ $order->booker_phone }}</dd></div>
            <div><dt>Email</dt><dd>{{ $order->booker_email }}</dd></div>
        </dl>
        <h2>Detail Tiket & Pengunjung</h2>
        @foreach($order->items as $item)
            <div class="purchase-order-item">
                <h3>{{ $item->ticket_name }}</h3>
                <p class="purchase-muted">{{ $item->ticket_category }}</p>
                @if($item->adult_quantity)
                    <div class="purchase-summary-row"><span>{{ $item->adult_quantity }} dewasa × Rp {{ number_format((float) $item->adult_unit_price, 2, ',', '.') }}</span></div>
                @endif
                @if($item->child_quantity)
                    <div class="purchase-summary-row"><span>{{ $item->child_quantity }} anak × Rp {{ number_format((float) $item->child_unit_price, 2, ',', '.') }}</span></div>
                @endif
                <div class="purchase-summary-row"><strong>Subtotal</strong><span>Rp {{ number_format((float) $item->subtotal, 2, ',', '.') }}</span></div>
                <ul class="purchase-visitors" aria-label="Daftar pengunjung">
                    @foreach($item->visitors as $visitor)
                        <li>{{ $visitor->name }} — {{ $visitor->age_category === 'adult' ? 'Dewasa' : 'Anak' }} · {{ $visitor->identity_number }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
        <div class="purchase-total"><strong>Total pesanan</strong><strong>Rp {{ number_format((float) $order->total, 2, ',', '.') }}</strong></div>
    </div>
    <div class="purchase-actions"><a class="krm-btn krm-btn-primary" href="{{ route('ticket-payment.show', $order->code) }}">{{ $order->status === 'paid' ? 'Lihat Pembayaran' : 'Lanjut ke Pembayaran' }}</a><a class="krm-btn" href="{{ route('ticket-purchase.orders') }}">Pesanan Saya</a><a class="krm-btn krm-btn-primary" href="{{ route('ticket-purchase.index') }}">Beli Tiket Lagi</a></div>
</div>
@endsection
