@extends('layouts.app')

@section('title', 'Beli Tiket - Kebun Raya Mangrove Surabaya')

@push('styles')
    @include('wisatawan.ticket-purchase.styles')
@endpush

@section('content')
@php
    $oldText = static function (string $key, mixed $default = ''): string {
        $value = old($key, $default);
        return is_string($value) ? $value : '';
    };
@endphp
<div class="purchase-page krm-container">
    <nav class="purchase-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('wisatawan.beranda') }}">Beranda</a><span aria-hidden="true">/</span><span>Beli Tiket</span></nav>
    <ol class="purchase-steps" aria-label="Tahapan pembelian">
        <li data-step="1" aria-current="step"><span>1</span>Pilih Tiket</li>
        <li data-step="2"><span>2</span>Data Pengunjung</li>
        <li data-step="3"><span>3</span>Pembayaran</li>
    </ol>

    @if ($errors->any())
        <div class="purchase-alert" role="alert" tabindex="-1" id="server-errors">
            <strong>Pesanan belum disimpan. Periksa kembali data berikut:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div class="purchase-alert" id="purchase-error" role="alert" tabindex="-1" hidden></div>
    <noscript><p class="purchase-alert">Aktifkan JavaScript untuk memilih tiket dan mengisi data pengunjung.</p></noscript>

    <form id="ticket-purchase-form" action="{{ route('ticket-purchase.store') }}" method="POST">
        @csrf
        <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
        <section id="ticket-step" aria-labelledby="ticket-heading">
            <div class="purchase-heading"><h1 id="ticket-heading" tabindex="-1">Pilih Jenis Tiket</h1><p>Rencanakan kunjunganmu ke Kebun Raya Mangrove Surabaya.</p></div>
            <div class="purchase-ticket-types" role="group" aria-label="Kelompok tiket">
                <button type="button" class="krm-card purchase-type" data-group="masuk" aria-pressed="true"><h2>Tiket Masuk</h2><ul id="entry-ticket-names" class="purchase-muted"></ul></button>
                <button type="button" class="krm-card purchase-type" data-group="wahana" aria-pressed="false"><h2>Tiket Wahana</h2><ul id="ride-ticket-names" class="purchase-muted"></ul></button>
                <button type="button" class="krm-card purchase-type" data-group="lainnya" aria-pressed="false" hidden><h2>Lainnya</h2><ul id="other-ticket-names" class="purchase-muted"></ul></button>
            </div>
            <div class="krm-card purchase-fields">
                <h2>Detail Kunjungan</h2>
                <div class="purchase-grid">
                    <label>Jenis wisatawan<select name="visitor_type" aria-label="Jenis wisatawan" id="visitor-type" required><option value="domestik" @selected(old('visitor_type', $visitorType) === 'domestik')>Domestik (WNI)</option><option value="mancanegara" @selected(old('visitor_type', $visitorType) === 'mancanegara')>Mancanegara (WNA)</option></select><span class="purchase-field-error" data-error="visitor_type"></span></label>
                    <label>Tanggal kunjungan<input id="visit-date" aria-label="Tanggal kunjungan" type="date" name="visit_date" value="{{ $oldText('visit_date', $today) }}" min="{{ $today }}" required aria-describedby="date-help"><span class="purchase-field-error" data-error="visit_date"></span></label>
                </div>
                <p class="purchase-muted" id="date-help">Tanggal kunjungan menggunakan waktu WIB. Maksimal 4 tiket per transaksi, termasuk dewasa dan anak.</p>
                <div id="ticket-items"></div>
                <button class="purchase-link" type="button" id="add-ticket">+ Tambah jenis tiket</button>
                <p class="purchase-muted" id="catalog-empty" hidden>Belum ada tarif tiket yang tersedia untuk jenis wisatawan ini.</p>
            </div>
        </section>

        <section id="visitor-step" aria-labelledby="visitor-heading" hidden>
            <div class="purchase-heading"><h1 id="visitor-heading" tabindex="-1">Data Pengunjung</h1><p>Lengkapi identitas pemesan dan seluruh pengunjung sesuai jumlah tiket.</p></div>
            <div class="krm-card purchase-fields">
                <h2 id="booker-heading">Identitas Pemesan</h2>
                <input type="hidden" name="booker_is_visitor" value="0">
                <label class="purchase-checkbox"><input type="checkbox" id="booker-is-visitor" name="booker_is_visitor" value="1" @checked(old('booker_is_visitor', '1') === '1')> Pemesan ikut berkunjung</label>
                <p class="purchase-muted" id="booker-help">Pemesan yang ikut berkunjung dihitung sebagai satu pengunjung dewasa.</p>
                <span class="purchase-field-error" data-error="booker_is_visitor"></span>
                <div class="purchase-grid">
                    <label>Nama lengkap<input name="booker[name]" aria-label="Nama lengkap" value="{{ $oldText('booker.name', $booker['name']) }}" autocomplete="name" maxlength="255" required><span class="purchase-field-error" data-error="booker.name"></span></label>
                    <label>Nomor identitas (KTP / Paspor)<input name="booker[identity_number]" aria-label="Nomor identitas (KTP / Paspor)" value="{{ $oldText('booker.identity_number', $booker['identity_number']) }}" maxlength="50" required><span class="purchase-field-error" data-error="booker.identity_number"></span></label>
                    <label>Nomor telepon<input name="booker[phone]" aria-label="Nomor telepon" type="tel" value="{{ $oldText('booker.phone', $booker['phone']) }}" autocomplete="tel" maxlength="25" required><span class="purchase-field-error" data-error="booker.phone"></span></label>
                    <label>Email<input name="booker[email]" aria-label="Email" type="email" value="{{ $oldText('booker.email', $booker['email']) }}" autocomplete="email" maxlength="255" required><span class="purchase-field-error" data-error="booker.email"></span></label>
                </div>
            </div>
            <div id="visitor-fields"></div>
            <p class="purchase-muted">Nomor identitas anak dapat menggunakan nomor pada Kartu Keluarga atau paspor.</p>
        </section>

        <section class="krm-card purchase-summary" aria-labelledby="summary-heading">
            <h2 id="summary-heading">Ringkasan Pesanan</h2>
            <div id="summary-items" aria-live="polite"></div>
            <p class="purchase-muted" id="summary-date"></p>
            <div class="purchase-total"><strong id="total-label">Total estimasi</strong><strong id="summary-total">Rp 0</strong></div>
            <p class="purchase-muted">Harga dan ketersediaan diperiksa kembali saat pesanan disimpan. Kuota belum direservasi atau dikurangi.</p>
        </section>
        <div class="purchase-actions">
            <a class="krm-btn" id="cancel-purchase" href="{{ route('wisatawan.beranda') }}">Kembali</a>
            <button class="krm-btn" type="button" id="previous-step" hidden>Kembali</button>
            <button class="krm-btn krm-btn-primary" type="button" id="next-step">Lanjut →</button>
            <button class="krm-btn krm-btn-primary" type="submit" id="submit-purchase" hidden>Lanjut ke Pembayaran →</button>
        </div>
        <p class="purchase-muted purchase-submit-note" id="submit-note" hidden>Pesanan yang disimpan berstatus menunggu pembayaran.</p>
    </form>
</div>
@endsection

@push('scripts')
    @include('wisatawan.ticket-purchase.script')
@endpush
