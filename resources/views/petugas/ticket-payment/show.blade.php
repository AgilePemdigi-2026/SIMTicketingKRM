@extends('layouts.app')
@section('title', 'Detail Pembayaran - Petugas')
@push('styles') @include('partials.ticket-payment-styles') @endpush
@section('content')
@php($latest = $payment->submissions->first())
<div class="purchase-page krm-container">
    <nav class="purchase-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('ticket-payment.staff.index') }}">Verifikasi Pembayaran</a><span>/</span><span>Detail</span></nav>
    <div class="purchase-heading"><h1>Detail Pembayaran</h1><p>Pastikan uang benar-benar diterima sebelum mengonfirmasi.</p></div>
    @include('partials.ticket-payment-feedback')
    <div class="payment-grid">
        <section class="krm-card payment-card"><h2>Pemeriksaan {{ strtoupper($payment->method) }}</h2><span class="payment-label">{{ $payment->statusLabel() }}</span>
            @if($payment->method === 'qris')
                <p>Cocokkan bukti dengan penerimaan di riwayat GoPay Merchant: nama penerima, nominal, waktu, dan referensi transaksi. Bukti gambar saja tidak membuktikan dana diterima.</p>
                <ul class="payment-history">@forelse($payment->submissions as $submission)<li><a class="payment-proof-link" target="_blank" rel="noopener" href="{{ route('ticket-payment.proof', $submission) }}">Buka bukti #{{ $submission->id }}</a><time>{{ $submission->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</time><p>{{ match($submission->status) {'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Menunggu verifikasi'} }}</p>@if($submission->rejection_reason)<p class="payment-error">{{ $submission->rejection_reason }}</p>@endif</li>@empty<li>Belum ada bukti pembayaran.</li>@endforelse</ul>
            @else
                <p>Terima uang cash sesuai total pesanan, lalu lakukan konfirmasi. Jangan konfirmasi sebelum uang diterima.</p>
            @endif
            @if(($payment->method === 'cash' && $payment->status === 'pending') || ($payment->method === 'qris' && $payment->status === 'under_review'))
                <form method="POST" action="{{ route('ticket-payment.staff.confirm', $payment) }}" data-payment-form>
                    @csrf
                    @if($payment->method === 'qris')
                        <input type="hidden" name="submission_id" value="{{ $latest?->id }}">
                        <label for="merchant-reference">Referensi transaksi merchant</label><input id="merchant-reference" name="merchant_reference" maxlength="100" value="{{ old('merchant_reference') }}" required aria-describedby="reference-error">
                        @error('merchant_reference')<p class="payment-error" id="reference-error">{{ $message }}</p>@enderror
                    @endif
                    <label class="payment-check"><input type="checkbox" name="receipt_checked" value="1" required><span>{{ $payment->method === 'qris' ? 'Saya sudah mencocokkan penerimaan dan referensi di riwayat merchant.' : 'Saya sudah menerima uang cash sesuai nominal pesanan.' }}</span></label>
                    @error('receipt_checked')<p class="payment-error">{{ $message }}</p>@enderror
                    <button type="submit" class="krm-btn krm-btn-primary">{{ $payment->method === 'cash' ? 'Konfirmasi Cash Diterima' : 'Konfirmasi Pembayaran QRIS' }}</button>
                </form>
                @if($payment->method === 'qris')
                    <form method="POST" action="{{ route('ticket-payment.staff.reject', $payment) }}" data-payment-form>
                        @csrf<input type="hidden" name="submission_id" value="{{ $latest?->id }}">
                        <label for="reason">Alasan penolakan bukti</label><textarea id="reason" name="reason" maxlength="1000" required>{{ old('reason') }}</textarea>
                        @error('reason')<p class="payment-error">{{ $message }}</p>@enderror
                        <label class="payment-check"><input type="checkbox" name="receipt_checked" value="1" required><span>Saya sudah memeriksa bukti dan riwayat merchant.</span></label>
                        <button type="submit" class="krm-btn">Tolak Bukti Pembayaran</button>
                    </form>
                @endif
            @endif
        </section>
        <aside class="krm-card payment-card"><h2>Ringkasan pesanan</h2><dl class="payment-meta"><div><dt>Kode pesanan</dt><dd>{{ $payment->order->code }}</dd></div><div><dt>Pemesan</dt><dd>{{ $payment->order->booker_name }}</dd></div><div><dt>Tanggal kunjungan</dt><dd>{{ $payment->order->visit_date->format('d/m/Y') }}</dd></div><div><dt>Total</dt><dd class="payment-amount">Rp {{ number_format((float)$payment->amount, 2, ',', '.') }}</dd></div>
        @if($payment->merchant_reference)<div><dt>Referensi merchant</dt><dd>{{ $payment->merchant_reference }}</dd></div>@endif
        @if($payment->paid_at)<div><dt>Dikonfirmasi</dt><dd>{{ $payment->paid_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB · Petugas #{{ $payment->confirmed_by }}</dd></div>@endif</dl>
        <h2>Riwayat pembayaran</h2><ul class="payment-history">@foreach($payment->events as $event)<li>{{ match($event->action) {'method_selected' => 'Metode dipilih', 'proof_submitted' => 'Bukti dikirim', 'proof_rejected' => 'Bukti ditolak', 'payment_confirmed' => 'Pembayaran dikonfirmasi', default => $event->action} }}<time>{{ $event->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB · Pengguna #{{ $event->actor_id }}</time>@if($event->reason)<p>{{ $event->reason }}</p>@endif</li>@endforeach</ul>
        <a class="krm-btn" href="{{ route('ticket-payment.staff.index') }}">Kembali ke Daftar Pembayaran</a></aside>
    </div>
</div>
@endsection
@push('scripts') @include('partials.ticket-payment-script') @endpush
