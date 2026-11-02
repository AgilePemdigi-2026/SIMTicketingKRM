@extends('layouts.app')
@section('title', 'Pembayaran Tiket - Kebun Raya Mangrove Surabaya')
@push('styles') @include('partials.ticket-payment-styles') @endpush
@section('content')
@php($payment = $order->payment)
<div class="purchase-page krm-container">
    <nav class="purchase-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('ticket-purchase.orders') }}">Pesanan Saya</a><span>/</span><a href="{{ route('ticket-purchase.show', $order->code) }}">Detail Pesanan</a><span>/</span><span>Pembayaran</span></nav>
    <ol class="purchase-steps" aria-label="Tahapan pembelian"><li class="completed"><span>✓</span>Pilih Tiket</li><li class="completed"><span>✓</span>Data Pengunjung</li><li aria-current="step"><span>3</span>Pembayaran</li></ol>
    <div class="purchase-heading"><h1>Pembayaran</h1><p>Selesaikan pembayaran dan pantau hasil pemeriksaan Petugas.</p></div>
    @include('partials.ticket-payment-feedback')
    <div class="payment-grid">
        <div class="payment-main">
        <section class="krm-card payment-card payment-method-card" aria-labelledby="method-title">
            @if(!$payment && $order->status === 'pending_payment')
                <h2 id="method-title">Pilih metode pembayaran</h2>
                <p>Metode tidak dapat diganti setelah disimpan. Pastikan pilihanmu sudah sesuai.</p>
                <form method="POST" action="{{ route('ticket-payment.select', $order->code) }}" data-payment-form>
                    @csrf
                    <fieldset><legend class="sr-only">Metode pembayaran</legend><div class="payment-methods payment-method-choice">
                        <label><input type="radio" name="method" value="qris" required @checked(old('method') === 'qris')>QRIS<br><small>Bayar dengan aplikasi bank atau e-wallet.</small></label>
                        <label><input type="radio" name="method" value="cash" required @checked(old('method') === 'cash')>Cash<br><small>Bayar tunai kepada Petugas loket.</small></label>
                    </div></fieldset>
                    @error('method')<p class="payment-error">{{ $message }}</p>@enderror
                    <div class="purchase-actions"><button type="submit" class="krm-btn krm-btn-primary">Simpan Metode Pembayaran</button></div>
                </form>
            @elseif($payment)
                <h2 id="method-title">Metode pembayaran</h2>
                <div class="payment-methods payment-method-tabs" role="group" aria-label="Metode pembayaran tersimpan"><span class="{{ $payment?->method === 'qris' ? 'selected' : '' }}">QRIS</span><span class="{{ $payment?->method === 'cash' ? 'selected' : '' }}">CASH</span></div>
                <p class="payment-status-note">Metode sudah tersimpan dan tidak dapat diganti.</p>
            @else
                <h2 id="method-title">Metode belum dipilih</h2>
            @endif
        </section>
        <section class="krm-card payment-card payment-instructions" aria-labelledby="payment-title">
            @if(!$payment && $order->status === 'pending_payment')
                <h2 id="payment-title">Petunjuk pembayaran</h2><p>Pilih QRIS atau cash, lalu simpan metode pembayaran untuk melihat petunjuk berikutnya.</p>
            @elseif($payment?->status === 'paid')
                <h2 id="payment-title">Pembayaran terkonfirmasi</h2>
                <span class="payment-label">Lunas</span>
                <p>Petugas telah mengonfirmasi pembayaranmu. Data pembayaran tersedia untuk proses penerbitan E-Tiket.</p>
                <p class="payment-status-note">E-Tiket ditangani melalui modul penerbitan tiket.</p>
            @elseif($payment?->method === 'qris')
                <h2 id="payment-title">Bayar dengan QRIS</h2>
                <span class="payment-label">{{ $payment->statusLabel() }}</span>
                <div class="payment-notice" style="margin-top:20px">QRIS ini disediakan untuk demonstrasi proyek, bukan rekening resmi pengelola destinasi.</div>
                @if($payment->status !== 'under_review')
                    <img class="payment-qr" src="{{ asset('images/pembayaran/qris-code.png') }}" alt="Kode QRIS untuk demonstrasi proyek" width="814" height="823">
                    <ol class="purchase-visitors"><li>Pindai atau impor gambar QRIS melalui aplikasi bank/e-wallet.</li><li>Periksa nama penerima, lalu masukkan tepat <strong>Rp {{ number_format((float)$payment->amount, 2, ',', '.') }}</strong>.</li><li>Setelah pembayaran berhasil, unggah bukti di bawah.</li></ol>
                    <a class="krm-btn" href="{{ asset('images/pembayaran/qris-code.png') }}" download="QRIS-Merchant.png">Unduh Gambar QRIS</a>
                    @if($payment->status === 'rejected')
                        <p class="payment-issue"><strong>Alasan penolakan:</strong> {{ $payment->submissions->first()?->rejection_reason }}<br>Periksa alasan sebelum mengajukan ulang. Jangan membayar ulang jika dana sudah terkirim; cocokkan dahulu dengan Petugas.</p>
                    @endif
                    <form method="POST" enctype="multipart/form-data" action="{{ route('ticket-payment.submit', $order->code) }}" data-payment-form>
                        @csrf
                        <label for="proof">Bukti pembayaran</label><input id="proof" name="proof" type="file" accept="image/jpeg,image/png" required aria-describedby="proof-help proof-error">
                        <p id="proof-help" class="payment-status-note">JPG atau PNG, maksimal 5 MB. Bukti hanya dapat dilihat olehmu dan Petugas.</p>
                        @error('proof')<p class="payment-error" id="proof-error">{{ $message }}</p>@enderror
                        <button type="submit" class="krm-btn krm-btn-primary">Kirim Bukti Pembayaran</button>
                    </form>
                @else
                    <p>Bukti sudah dikirim. Petugas akan mencocokkan penerimaan melalui riwayat merchant. Mengunggah bukti tidak langsung membuat pesanan lunas.</p>
                @endif
            @elseif($payment?->method === 'cash')
                <h2 id="payment-title">Pembayaran cash di loket</h2><span class="payment-label">{{ $payment->statusLabel() }}</span>
                <p>Tunjukkan kode pesanan kepada Petugas dan bayarkan <strong>Rp {{ number_format((float)$payment->amount, 2, ',', '.') }}</strong> di loket.</p>
                <p>Pesanan akan menjadi lunas setelah Petugas menerima uang dan mengonfirmasinya.</p>
            @else
                <h2 id="payment-title">Pembayaran tidak tersedia</h2><p>Status pesanan saat ini tidak menerima pembayaran baru.</p>
            @endif
        </section>
        </div>
        <aside class="krm-card payment-card payment-summary"><h2>Ringkasan pembayaran</h2>
            @foreach($order->items as $item)
                <div class="payment-summary-item"><strong>{{ $item->ticket_name }}</strong><p>{{ $item->adult_quantity }} dewasa · {{ $item->child_quantity }} anak</p></div>
            @endforeach
            <dl class="payment-meta"><div><dt>Kategori wisatawan</dt><dd>{{ $order->visitor_type === 'domestik' ? 'Domestik (WNI)' : 'Mancanegara (WNA)' }}</dd></div><div><dt>Jumlah tiket</dt><dd>{{ $order->items->sum(fn ($item) => $item->adult_quantity + $item->child_quantity) }} tiket</dd></div><div><dt>Pemesan</dt><dd>{{ $order->booker_name }}</dd></div></dl>
            <dl class="payment-meta"><div><dt>Kode pesanan</dt><dd>{{ $order->code }}</dd></div><div><dt>Tanggal kunjungan</dt><dd>{{ $order->visit_date->format('d/m/Y') }}</dd></div><div><dt>Metode</dt><dd>{{ $payment ? strtoupper($payment->method) : 'Belum dipilih' }}</dd></div><div><dt>Total pembayaran</dt><dd class="payment-amount">Rp {{ number_format((float)$order->total, 2, ',', '.') }}</dd></div>
            @if($payment?->paid_at)<div><dt>Dikonfirmasi pada</dt><dd>{{ $payment->paid_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div>@endif</dl>
            @if($payment && $payment->submissions->isNotEmpty())
                <h2>Pengajuan bukti</h2><ul class="payment-history">@foreach($payment->submissions as $submission)<li><a class="payment-proof-link" href="{{ route('ticket-payment.proof', $submission) }}" target="_blank" rel="noopener">Lihat bukti #{{ $submission->id }}</a><time>{{ $submission->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</time><p>{{ match($submission->status) { 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Menunggu verifikasi' } }}</p>@if($submission->rejection_reason)<p class="payment-error">{{ $submission->rejection_reason }}</p>@endif</li>@endforeach</ul>
            @endif
            <a class="krm-btn" href="{{ route('ticket-purchase.show', $order->code) }}">Kembali ke Detail Pesanan</a>
        </aside>
    </div>
</div>
@endsection
@push('scripts') @include('partials.ticket-payment-script') @endpush
