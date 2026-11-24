@extends('layouts.petugas')

@section('title', 'Bayar Cash Booking - Petugas KRM')

@push('styles')
<style>
    /* Lingkup khusus halaman Cash Booking, tidak mengubah modul loket/QRIS. */
    .bcb-page, .bcb-page * { box-sizing: border-box; }
    .bcb-page { width: min(calc(100% - 36px), 1500px); margin: 38px auto 80px; color: #173e31; font-family: 'Plus Jakarta Sans', sans-serif; }
    .bcb-header { padding-bottom: 17px; border-bottom: 1px solid #b8cbbf; margin-bottom: 19px; }
    .bcb-header h1 { font-size: clamp(23px, 2.4vw, 29px); line-height: 1.28; font-weight: 800; color: #0a3c2f; margin: 0 0 8px; }
    .bcb-header p { font-size: 13px; color: #74867d; margin: 0; }
    .bcb-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 17px; align-items: start; }
    .bcb-left, .bcb-right-filled { display: grid; align-content: start; gap: 15px; min-width: 0; }
    .bcb-panel { padding: 19px; background: #fff; border: 1px solid #d3dfd7; min-width: 0; }
    .bcb-title-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-bottom: 1px solid #d6e1d8; padding-bottom: 14px; }
    .bcb-panel-title { color: #143d30; font-size: 13px; font-weight: 800; letter-spacing: .015em; margin: 0; }
    .bcb-form { padding-top: 15px; }
    .bcb-field { margin-bottom: 12px; }
    .bcb-field label, .bcb-cash-form label.bcb-field-label { display: block; font-size: 11px; font-weight: 650; color: #345447; margin-bottom: 7px; }
    .bcb-input, .bcb-select { display: block; width: 100%; height: 44px; padding: 10px 12px; border: 1px solid #c7d5cc; background: #fff; border-radius: 0; color: #234538; font: inherit; font-size: 13px; }
    .bcb-input:focus, .bcb-select:focus { outline: 2px solid #9cbe94; outline-offset: 1px; border-color: #4e846c; }
    .bcb-input::placeholder { color: #a1aea6; }
    .bcb-input[readonly] { background: #f7f9f7; color: #315749; }
    .bcb-search-row { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 8px; align-items: stretch; }
    .bcb-search-btn, .bcb-primary { min-height: 44px; display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 10px 19px; border: 1px solid #07513d; background: #07513d; color: #fff; cursor: pointer; font: inherit; font-size: 12px; font-weight: 750; text-decoration: none; white-space: nowrap; }
    .bcb-search-btn:hover, .bcb-primary:hover { background: #06402f; color: #fff; }
    .bcb-primary:disabled { opacity: .5; cursor: not-allowed; }
    .bcb-search-btn svg, .bcb-primary svg { width: 17px; height: 17px; flex-shrink: 0; }
    .bcb-flow { margin-top: 15px; }
    .bcb-step { min-height: 46px; display: flex; align-items: center; gap: 11px; border: 1px solid #d1ddd4; padding: 8px 10px; color: #6b7d73; background: #fff; font-size: 11px; }
    .bcb-step.is-current { background: #e7f1e7; border-color: #92bba0; color: #113f30; font-weight: 750; }
    .bcb-number { display: grid; place-items: center; width: 25px; height: 25px; border: 1px solid #b7cbc0; background: #fff; color: #547063; font-size: 11px; font-weight: 700; flex-shrink: 0; }
    .bcb-step.is-current .bcb-number { background: #2b7659; border-color: #2b7659; color: #fff; }
    .bcb-step-arrow { height: 14px; display: flex; align-items: center; padding-left: 15px; color: #91a79a; font-size: 13px; }
    .bcb-right-empty { min-height: 510px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 25px; background: #fff; border: 1px dashed #b7ccbe; }
    .bcb-right-empty svg { width: 37px; height: 37px; margin-bottom: 15px; color: #9fb3a5; }
    .bcb-right-empty h2 { margin: 0 0 9px; font-size: 13px; font-weight: 500; color: #6b8274; }
    .bcb-right-empty p { max-width: 310px; margin: 0; color: #a2b1a7; font-size: 11px; line-height: 1.65; }
    .bcb-badge { display: inline-block; padding: 5px 8px; font-weight: 700; font-size: 10px; white-space: nowrap; color: #855f09; background: #fff0d0; border: 1px solid #ead6a9; }
    .bcb-badge.is-paid { color: #286044; background: #e4f1e7; border-color: #bfdcca; }
    .bcb-badge.is-other { color: #5c7064; background: #edf1ee; border-color: #d6e1d9; }
    .bcb-meta { margin: 14px 0 0; }
    .bcb-meta-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; padding: 11px 0; border-bottom: 1px solid #e6ede7; font-size: 12px; }
    .bcb-meta-row dt { color: #7b8d81; flex: 1 1 46%; }
    .bcb-meta-row dd { margin: 0; flex: 1 1 54%; text-align: right; font-weight: 700; color: #214936; overflow-wrap: anywhere; }
    .bcb-info { margin: 13px 0 0; color: #708577; font-size: 11px; line-height: 1.5; }
    .bcb-bill { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 13px 12px; background: #e9f2e9; margin-top: 12px; }
    .bcb-bill span { color: #526e5b; font-size: 12px; }
    .bcb-bill strong { font-weight: 800; font-size: 19px; color: #103c2f; }
    .bcb-cash-form .bcb-field { margin-top: 13px; }
    .bcb-change { background: #e9f2e9; border-left: 4px solid #2d8060; padding: 13px 12px 15px; margin: 12px 0 0; }
    .bcb-change span { display: block; color: #839389; font-size: 11px; letter-spacing: .04em; }
    .bcb-change strong { display: block; color: #123f30; font-size: 22px; margin-top: 5px; line-height: 1.2; }
    .bcb-warning { min-height: 15px; margin: 5px 0 0; color: #a72f2f; font-size: 11px; }
    .bcb-check { display: flex; align-items: flex-start; gap: 9px; margin: 12px 0; font-size: 11px; color: #3f6050; cursor: pointer; }
    .bcb-check input { accent-color: #116044; margin-top: 1px; flex-shrink: 0; }
    .bcb-primary.is-wide { width: 100%; margin-top: 8px; }
    .bcb-flash { padding: 12px 15px; margin-bottom: 15px; font-size: 12px; line-height: 1.6; background: #edf6ef; border: 1px solid #bfdac7; color: #216042; }
    .bcb-flash.is-error { background: #fff7f5; border-color: #ebc8c2; color: #992f2f; }
    .bcb-field-error { color: #a52d2d; font-size: 11px; margin: 5px 0 0; }
    .bcb-back-result { display: inline-flex; align-items: center; gap: 7px; margin: 0; color: #21644c; font-size: 11px; font-weight: 650; text-decoration: underline; }
    .bcb-results-count { margin: 0; font-size: 11px; color: #7a9081; }
    .bcb-result-list { display: grid; gap: 11px; margin-top: 16px; }
    .bcb-result { border: 1px solid #d2ded5; padding: 15px; background: #fff; }
    .bcb-result-title { margin: 0 0 8px; font-size: 13px; font-weight: 800; overflow-wrap: anywhere; color: #123f30; }
    .bcb-result-meta { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: 5px 12px; align-items: baseline; }
    .bcb-result-name { margin: 0; font-size: 12px; font-weight: 700; color: #214b39; }
    .bcb-result-date { margin: 3px 0 0; color: #718679; font-size: 11px; }
    .bcb-result-price { margin: 0; font-size: 15px; color: #0b4735; font-weight: 800; text-align: right; white-space: nowrap; }
    .bcb-result-footer { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding-top: 13px; margin-top: 12px; border-top: 1px solid #e2eae3; }
    .bcb-result-link { min-height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; text-decoration: none; background: #07513d; border: 1px solid #07513d; color: #fff; font-size: 11px; font-weight: 700; padding: 8px 12px; }
    .bcb-result-link:hover { color: #fff; background: #06402f; }
    .bcb-result-link svg { width: 15px; height: 15px; }
    .bcb-no-match { padding: 90px 20px; text-align: center; color: #657e70; }
    .bcb-no-match h2 { margin: 0 0 7px; color: #315443; font-size: 15px; font-weight: 700; }
    .bcb-no-match p { margin: 0; font-size: 12px; line-height: 1.6; }
    .bcb-pagination { margin-top: 20px; }
    @media(max-width: 800px) { .bcb-page { width: min(calc(100% - 28px), 1500px); margin: 25px auto 60px; } .bcb-layout { grid-template-columns: 1fr; } .bcb-right-empty { min-height: 280px; } }
    @media(max-width: 440px) { .bcb-panel { padding: 15px; } .bcb-search-row { grid-template-columns: 1fr; } .bcb-search-btn { width: 100%; } .bcb-result-meta { grid-template-columns: 1fr; } .bcb-result-price { text-align: left; } .bcb-meta-row { gap: 8px; } }
</style>
@endpush

@section('content')
@php
    $activeOrder = $selectedPayment?->order;
    // Sama persis dengan kode CASH yang ditampilkan di halaman pembayaran Wisatawan.
    $cashDisplayCode = $activeOrder ? 'CASH-' . str_pad((string) $activeOrder->id, 4, '0', STR_PAD_LEFT) : null;
    $adultCount = $activeOrder ? (int) $activeOrder->items->sum('adult_quantity') : 0;
    $childCount = $activeOrder ? (int) $activeOrder->items->sum('child_quantity') : 0;
    $ticketCount = $adultCount + $childCount;
    $ticketNames = $activeOrder ? $activeOrder->items->pluck('ticket_name')->filter()->unique()->implode(', ') : '';
    $visitorName = $activeOrder ? ($activeOrder->items->flatMap(fn ($item) => $item->visitors)->first()?->name ?? $activeOrder->booker_name) : null;
    $price = $selectedPayment ? (int) round((float) $selectedPayment->amount) : 0;
    $selectedStatus = $selectedPayment?->status;
    $resultsUrl = route('ticket-payment.staff.index', ['q' => $search, 'by' => $searchBy]);
@endphp
<section class="bcb-page" aria-labelledby="bcb-title">
    <header class="bcb-header">
        <h1 id="bcb-title">Bayar Cash Booking</h1>
        <p>Booking online, bayar tunai di loket</p>
    </header>

    @if(session('payment_updated'))
        <div class="bcb-flash" role="status">{{ session('payment_updated') }}</div>
    @endif
    @if($errors->any())
        <div class="bcb-flash is-error" role="alert">
            Pembayaran belum berhasil diproses. Periksa nominal uang, konfirmasi penerimaan, dan coba lagi.
            <ul style="margin: 7px 0 0; padding-left: 17px">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bcb-layout">
        <div class="bcb-left">
            <section class="bcb-panel" aria-labelledby="bcb-search-title">
                <div class="bcb-title-row"><h2 class="bcb-panel-title" id="bcb-search-title">CARI TRANSAKSI</h2></div>
                <form class="bcb-form" action="{{ route('ticket-payment.staff.index') }}" method="GET" role="search">
                    <div class="bcb-field">
                        <label for="bcb-by">Cari Berdasarkan</label>
                        <select id="bcb-by" name="by" class="bcb-select">
                            <option value="name" @selected($searchBy === 'name')>Nama Pengunjung</option>
                            <option value="code" @selected($searchBy === 'code')>Kode Pembayaran / Booking</option>
                        </select>
                    </div>
                    <div class="bcb-field" style="margin-bottom: 0">
                        <label for="bcb-query">Masukkan Kata Kunci</label>
                        <div class="bcb-search-row">
                            <input id="bcb-query" name="q" type="search" class="bcb-input" placeholder="Nama pengunjung, CASH-xxxx, atau kode booking" value="{{ $search }}" maxlength="100" autocomplete="off" required>
                            <button class="bcb-search-btn" type="submit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7.5"/><path d="m20.5 20.5-4.1-4.1"/></svg>
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            <section class="bcb-panel" aria-labelledby="bcb-flow-title">
                <div class="bcb-title-row"><h2 class="bcb-panel-title" id="bcb-flow-title">ALUR BAYAR CASH BOOKING</h2></div>
                <div class="bcb-flow" aria-label="Tahapan pembayaran tiket booking online">
                    <div class="bcb-step {{ $selectedStatus === null || $selectedStatus === 'pending' ? 'is-current' : '' }}"><span class="bcb-number">1</span><span>Menunggu Pembayaran</span></div>
                    <span class="bcb-step-arrow" aria-hidden="true">↓</span>
                    <div class="bcb-step {{ $selectedStatus === 'paid' ? 'is-current' : '' }}"><span class="bcb-number">2</span><span>Lunas</span></div>
                    <span class="bcb-step-arrow" aria-hidden="true">↓</span>
                    <div class="bcb-step"><span class="bcb-number">3</span><span>Check-In</span></div>
                </div>
            </section>
        </div>

        @if(!$hasSearched)
            <section class="bcb-right-empty" aria-live="polite" aria-label="Hasil pencarian transaksi">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="7"/><path d="m16 16 5 5"/></svg>
                <h2>Belum ada transaksi dipilih</h2>
                <p>Masukkan kode pembayaran cash atau nama pengunjung untuk mencari transaksi.</p>
            </section>
        @elseif($selectedPayment && $activeOrder)
            <div class="bcb-right-filled" aria-live="polite">
                <section class="bcb-panel" aria-labelledby="bcb-booking-title">
                    <div class="bcb-title-row">
                        <h2 class="bcb-panel-title" id="bcb-booking-title">DATA BOOKING DITEMUKAN</h2>
                        <span class="bcb-badge {{ $selectedPayment->status === 'paid' ? 'is-paid' : ($selectedPayment->status === 'pending' ? '' : 'is-other') }}">{{ $selectedPayment->statusLabel() }}</span>
                    </div>
                    <dl class="bcb-meta">
                        <div class="bcb-meta-row"><dt>No. Transaksi</dt><dd>{{ strtoupper((string) $activeOrder->code) }}</dd></div>
                        <div class="bcb-meta-row"><dt>Nama Pengunjung</dt><dd>{{ $visitorName ?? '-' }}</dd></div>
                        @if($visitorName !== $activeOrder->booker_name)
                            <div class="bcb-meta-row"><dt>Nama Pemesan</dt><dd>{{ $activeOrder->booker_name }}</dd></div>
                        @endif
                        <div class="bcb-meta-row"><dt>Kode Pembayaran Cash</dt><dd>{{ $cashDisplayCode }}</dd></div>
                        <div class="bcb-meta-row"><dt>Tanggal Kunjungan</dt><dd>{{ $activeOrder->visit_date?->format('d M Y') ?? '-' }}</dd></div>
                        <div class="bcb-meta-row"><dt>Tanggal Booking</dt><dd>{{ $activeOrder->created_at?->timezone('Asia/Jakarta')->format('d M Y') ?? '-' }}</dd></div>
                        <div class="bcb-meta-row"><dt>Jenis Tiket</dt><dd>{{ $ticketNames !== '' ? $ticketNames : '-' }}</dd></div>
                        <div class="bcb-meta-row"><dt>Jumlah Tiket</dt><dd>{{ $ticketCount }} tiket @if($ticketCount > 0)({{ collect([$adultCount > 0 ? "Dewasa x{$adultCount}" : null, $childCount > 0 ? "Anak x{$childCount}" : null])->filter()->implode(', ') }})@endif</dd></div>
                        <div class="bcb-meta-row"><dt>Total Pembayaran</dt><dd>Rp {{ number_format((float) $selectedPayment->amount, 0, ',', '.') }}</dd></div>
                        <div class="bcb-meta-row"><dt>Status Pembayaran</dt><dd>{{ $selectedPayment->statusLabel() }}</dd></div>
                    </dl>
                    @if($payments->total() > 1)
                        <p style="margin: 15px 0 0"><a class="bcb-back-result" href="{{ $resultsUrl }}">← Kembali ke {{ $payments->total() }} hasil pencarian</a></p>
                    @endif
                </section>

                <section class="bcb-panel" aria-labelledby="bcb-confirm-title">
                    <div class="bcb-title-row"><h2 class="bcb-panel-title" id="bcb-confirm-title">KONFIRMASI PEMBAYARAN</h2></div>
                    <div class="bcb-bill"><span>Total Tagihan</span><strong>Rp {{ number_format((float) $selectedPayment->amount, 0, ',', '.') }}</strong></div>
                    @if($selectedPayment->status === 'pending')
                        <form method="POST" action="{{ route('ticket-payment.staff.confirm', $selectedPayment) }}" class="bcb-cash-form" data-bcb-confirm-form data-total="{{ $price }}">
                            @csrf
                            <input type="hidden" name="booking_inline" value="1">
                            <input type="hidden" name="booking_search" value="{{ $search }}">
                            <input type="hidden" name="booking_by" value="{{ $searchBy }}">
                            <div class="bcb-field">
                                <label class="bcb-field-label" for="bcb-cash-received">Uang Diterima (Rp)</label>
                                <input id="bcb-cash-received" name="cash_received_amount" type="number" step="1" min="{{ $price }}" max="999999999999" inputmode="numeric" autocomplete="off" class="bcb-input" placeholder="Contoh: {{ $price + 50000 }}" value="{{ old('cash_received_amount') }}" required aria-describedby="bcb-cash-warning">
                                @error('cash_received_amount')<p class="bcb-field-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="bcb-change" aria-live="polite"><span>KEMBALIAN</span><strong id="bcb-cash-change">Rp 0</strong></div>
                            <p id="bcb-cash-warning" class="bcb-warning" role="status"></p>
                            <div class="bcb-field">
                                <label class="bcb-field-label" for="bcb-cash-staff">Nama Petugas</label>
                                <input id="bcb-cash-staff" type="text" class="bcb-input" value="{{ auth()->user()?->name ?? '-' }}" readonly>
                                <p class="bcb-info">Diambil dari akun Petugas yang sedang login.</p>
                            </div>
                            <label class="bcb-check" for="bcb-receipt-checked"><input id="bcb-receipt-checked" name="receipt_checked" type="checkbox" value="1" @checked(old('receipt_checked')) required><span>Saya sudah menerima dan memeriksa uang tunai sesuai total tagihan.</span></label>
                            @error('receipt_checked')<p class="bcb-field-error">{{ $message }}</p>@enderror
                            <button type="submit" class="bcb-primary is-wide" data-bcb-submit>Konfirmasi Pembayaran</button>
                        </form>
                    @elseif($selectedPayment->status === 'paid')
                        <div class="bcb-flash" style="margin: 13px 0 0">Pembayaran sudah Lunas. Konfirmasi ulang tidak diperlukan.</div>
                        @if($selectedPayment->cash_received_amount !== null)
                            <dl class="bcb-meta">
                                <div class="bcb-meta-row"><dt>Uang Diterima</dt><dd>Rp {{ number_format((float) $selectedPayment->cash_received_amount, 0, ',', '.') }}</dd></div>
                                <div class="bcb-meta-row"><dt>Kembalian</dt><dd>Rp {{ number_format((float) $selectedPayment->cash_change_amount, 0, ',', '.') }}</dd></div>
                            </dl>
                        @endif
                        @if($selectedPayment->paid_at)
                            <p class="bcb-info">Dikonfirmasi pada {{ $selectedPayment->paid_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB.</p>
                        @endif
                        <a href="{{ route('ticket-payment.staff.booking-success', $selectedPayment->id) }}" class="bcb-primary is-wide" style="margin-top: 15px">Lihat Bukti Pembayaran</a>
                    @else
                        <p class="bcb-info">Transaksi dalam status {{ $selectedPayment->statusLabel() }} dan belum dapat dikonfirmasi dari halaman ini.</p>
                    @endif
                </section>
            </div>
        @else
            <section class="bcb-panel" aria-label="Hasil pencarian transaksi">
                <div class="bcb-title-row">
                    <h2 class="bcb-panel-title">{{ $payments->count() > 0 ? 'HASIL PENCARIAN' : 'TRANSAKSI TIDAK DITEMUKAN' }}</h2>
                    <p class="bcb-results-count">{{ $payments->total() }} transaksi ditemukan</p>
                </div>
                @if($payments->count() > 0)
                    <div class="bcb-result-list">
                        @foreach($payments as $payment)
                            <article class="bcb-result">
                                <h3 class="bcb-result-title">CASH-{{ str_pad((string) $payment->order->id, 4, '0', STR_PAD_LEFT) }}</h3>
                                <p class="bcb-result-date">No. Transaksi: {{ strtoupper((string) $payment->order->code) }}</p>
                                <div class="bcb-result-meta">
                                    <div>
                                        <p class="bcb-result-name">{{ $payment->order?->booker_name ?? 'Pemesan tidak tersedia' }}</p>
                                        <p class="bcb-result-date">Tanggal kunjungan: {{ $payment->order?->visit_date?->format('d/m/Y') ?? '-' }}</p>
                                    </div>
                                    <p class="bcb-result-price">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                                </div>
                                <div class="bcb-result-footer">
                                    <span class="bcb-badge {{ $payment->status === 'paid' ? 'is-paid' : ($payment->status === 'pending' ? '' : 'is-other') }}">{{ $payment->statusLabel() }}</span>
                                    <a class="bcb-result-link" href="{{ $payment->status === 'paid' ? route('ticket-payment.staff.booking-success', $payment->id) : route('ticket-payment.staff.index', ['q' => $search, 'by' => $searchBy, 'selected' => $payment->id]) }}">
                                        {{ $payment->status === 'pending' ? 'Proses Pembayaran' : ($payment->status === 'paid' ? 'Lihat Bukti Pembayaran' : 'Lihat Detail') }}
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if($payments->hasPages())<div class="bcb-pagination">{{ $payments->links() }}</div>@endif
                @else
                    <div class="bcb-no-match"><h2>Transaksi tidak ditemukan</h2><p>Periksa kata kunci atau gunakan jenis pencarian lain.</p></div>
                @endif
            </section>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-bcb-confirm-form]');
    if (!form) return;
    const input = document.getElementById('bcb-cash-received');
    const change = document.getElementById('bcb-cash-change');
    const warning = document.getElementById('bcb-cash-warning');
    const submit = form.querySelector('[data-bcb-submit]');
    const receipt = document.getElementById('bcb-receipt-checked');
    const total = Number(form.dataset.total);
    const formatRupiah = value => 'Rp ' + value.toLocaleString('id-ID');
    function update() {
        const received = input.value === '' ? NaN : Number(input.value);
        const valid = Number.isSafeInteger(received) && received >= total && received <= 999999999999;
        change.textContent = valid ? formatRupiah(received - total) : 'Rp 0';
        warning.textContent = input.value !== '' && !valid ? 'Uang diterima harus berupa rupiah bulat dan minimal sebesar total tagihan.' : '';
        submit.disabled = !valid || !receipt.checked;
        return valid;
    }
    input.addEventListener('input', update);
    receipt.addEventListener('change', update);
    form.addEventListener('submit', function (event) {
        if (!update()) { event.preventDefault(); input.focus(); return; }
        if (!receipt.checked) { event.preventDefault(); receipt.focus(); return; }
        if (!window.confirm('Uang tunai sudah benar-benar diterima? Pembayaran akan dinyatakan Lunas.')) event.preventDefault();
    });
    update();
});
</script>
@endpush
