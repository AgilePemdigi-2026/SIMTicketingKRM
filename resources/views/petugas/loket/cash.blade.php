@extends('layouts.petugas')

@section('title', 'Terima Pembayaran Tunai - Petugas')

@push('styles')
<style>
    .loket-cash-page { min-height: calc(100vh - 72px); background: #f5f8f6; color: #153e31; }
    .loket-cash-shell { max-width: 1090px; margin: 0 auto; padding: 39px 24px 88px; }
    .loket-cash-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; padding-bottom: 25px; border-bottom: 1px solid #a8c2b2; margin-bottom: 24px; }
    .loket-cash-header h1 { color: #0a3829; font-size: clamp(24px, 2.35vw, 32px); font-weight: 750; margin: 0; line-height: 1.25; }
    .loket-cash-header p { font-size: 13px; color: #768a7d; margin: 9px 0 0; }
    .loket-cash-back { flex: none; display: inline-flex; align-items: center; justify-content: center; padding: 12px 24px; min-height: 46px; color: #07513d; border: 1px solid #07513d; background: #fff; font-size: 13px; font-weight: 650; text-decoration: none; }
    .loket-cash-back:hover { background: #f0f7f1; }
    .loket-cash-card { padding: 22px 24px 24px; background: #fff; border: 1px solid #d1ded4; margin-bottom: 18px; }
    .loket-cash-card h2 { font-size: 15px; font-weight: 750; color: #153c30; letter-spacing: .04em; margin: 0 0 17px; padding-bottom: 15px; border-bottom: 1px solid #d8e4db; }
    .loket-cash-details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 45px; }
    .loket-cash-detail { display: flex; align-items: flex-start; justify-content: space-between; gap: 15px; border-bottom: 1px solid #e9efea; padding: 14px 0; font-size: 14px; }
    .loket-cash-detail span { color: #798980; }
    .loket-cash-detail strong { color: #193e31; font-weight: 700; text-align: right; overflow-wrap: anywhere; }
    .loket-cash-form-top { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; align-items: end; }
    .loket-cash-field label { display: block; font-size: 13px; font-weight: 700; color: #405d50; margin-bottom: 8px; }
    .loket-cash-input { min-height: 52px; width: 100%; border: 1px solid #c4d5c9; padding: 12px 13px; background: white; color: #193e32; font: inherit; font-size: 15px; border-radius: 0; }
    .loket-cash-input:focus { outline: 2px solid #9dcaab; outline-offset: 1px; }
    .loket-cash-bill { min-height: 90px; background: #f5f8f6; padding: 14px 17px; border: 1px solid #dce6de; }
    .loket-cash-bill span { font-size: 13px; color: #7b8b80; }
    .loket-cash-bill strong { display: block; margin-top: 7px; font-size: 25px; color: #0b3c2c; font-weight: 750; }
    .loket-cash-change { margin-top: 18px; background: #e8f3e8; border-left: 5px solid #2d8060; padding: 17px 19px 21px; }
    .loket-cash-change span { display: block; color: #72887a; font-size: 13px; letter-spacing: .04em; }
    .loket-cash-change strong { display: block; font-size: clamp(29px, 3vw, 39px); font-weight: 750; color: #0b3e2d; margin-top: 8px; }
    .loket-cash-warning { min-height: 20px; color: #ae3333; font-size: 13px; margin: 8px 0 0; }
    .loket-cash-checkline { display: flex; align-items: flex-start; gap: 9px; margin: 15px 0 17px; color: #395949; font-size: 13px; cursor: pointer; }
    .loket-cash-checkline input { margin-top: 2px; accent-color: #07513d; width: 16px; height: 16px; flex: none; }
    .loket-cash-submit { display: block; width: 100%; min-height: 54px; padding: 14px 20px; background: #07513d; border: 1px solid #07513d; color: #fff; font-weight: 700; font-size: 14px; cursor: pointer; font-family: inherit; }
    .loket-cash-submit:hover { background: #063e30; }
    .loket-cash-submit:disabled { opacity: .55; cursor: not-allowed; }
    .loket-cash-note { color: #71857a; font-size: 12px; line-height: 1.6; margin-top: 12px; }
    .loket-cash-alert { margin-bottom: 19px; padding: 14px 16px; color: #983131; border: 1px solid #e8b8b8; background: #fff7f7; font-size: 13px; }
    .loket-cash-alert-success { color: #215c43; border-color: #cbe0d0; background: #edf6ef; }
    .loket-cash-error { color: #b02929; font-size: 12px; margin-top: 7px; }
    @media (max-width: 700px) { .loket-cash-shell { padding: 26px 14px 60px; } .loket-cash-card { padding: 18px 16px; } .loket-cash-details, .loket-cash-form-top { grid-template-columns: 1fr; gap: 0; } .loket-cash-form-top { gap: 17px; } .loket-cash-header { align-items: center; } .loket-cash-back { padding: 10px 14px; } }
</style>
@endpush

@section('content')
@php
    $order = $payment->order;
    $adultCount = $order->items->sum('adult_quantity');
    $childCount = $order->items->sum('child_quantity');
    $ticketNames = $order->items->pluck('ticket_name')->unique()->implode(', ');
    $quantities = collect([
        $adultCount > 0 ? 'Dewasa x' . $adultCount : null,
        $childCount > 0 ? 'Anak x' . $childCount : null,
    ])->filter()->implode(', ');
@endphp

<div class="loket-cash-page">
    <div class="loket-cash-shell">
        <header class="loket-cash-header">
            <div>
                <h1>Terima Pembayaran Tunai</h1>
                <p>Pembelian tiket langsung · menunggu penerimaan uang</p>
            </div>
            <a class="loket-cash-back" href="{{ route('petugas.loket.index') }}">← Kembali</a>
        </header>

        @if(session('payment_updated'))
            <div class="loket-cash-alert loket-cash-alert-success" role="status">{{ session('payment_updated') }}</div>
        @endif
        @if($errors->any())
            <div class="loket-cash-alert" role="alert">
                Pembayaran belum dapat diproses. Periksa jumlah uang dan konfirmasi penerimaannya.
            </div>
        @endif

        <section class="loket-cash-card" aria-labelledby="loket-detail-heading">
            <h2 id="loket-detail-heading">DETAIL TRANSAKSI</h2>
            <div class="loket-cash-details">
                <div>
                    <div class="loket-cash-detail"><span>Nama Pengunjung</span><strong>{{ $order->booker_name }}</strong></div>
                    <div class="loket-cash-detail"><span>Jumlah Tiket</span><strong>{{ $quantities }}</strong></div>
                </div>
                <div>
                    <div class="loket-cash-detail"><span>Jenis Tiket</span><strong>{{ $ticketNames }}</strong></div>
                    <div class="loket-cash-detail"><span>Total Pembayaran</span><strong>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</strong></div>
                </div>
            </div>
        </section>

        <form id="loket-cash-form" method="POST" action="{{ route('ticket-payment.staff.confirm', $payment->id) }}">
            @csrf
            <section class="loket-cash-card" aria-labelledby="loket-cash-heading">
                <h2 id="loket-cash-heading">PROSES PEMBAYARAN</h2>
                <div class="loket-cash-form-top">
                    <div class="loket-cash-field">
                        <label for="cash_received_amount">Uang yang Diterima (Rp)</label>
                        <input
                            class="loket-cash-input"
                            id="cash_received_amount"
                            name="cash_received_amount"
                            type="number"
                            step="1"
                            min="0"
                            max="999999999999"
                            placeholder="Contoh: 100000"
                            value="{{ old('cash_received_amount') }}"
                            inputmode="numeric"
                            autocomplete="off"
                            required
                            aria-describedby="loket-cash-warning"
                        >
                        @error('cash_received_amount')
                            <p class="loket-cash-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="loket-cash-bill">
                        <span>Total Tagihan</span>
                        <strong>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="loket-cash-change" aria-live="polite">
                    <span>KEMBALIAN</span>
                    <strong id="loket-cash-change">Rp 0</strong>
                </div>
                <p id="loket-cash-warning" class="loket-cash-warning" role="status"></p>

                <label class="loket-cash-checkline" for="receipt_checked">
                    <input type="checkbox" name="receipt_checked" id="receipt_checked" value="1" required>
                    <span>Saya sudah menerima dan memeriksa uang tunai dari pengunjung.</span>
                </label>
                @error('receipt_checked')
                    <p class="loket-cash-error">{{ $message }}</p>
                @enderror
            </section>

            <button type="submit" class="loket-cash-submit" id="loket-cash-submit">
                Terima Pembayaran Tunai &amp; Cetak Tiket
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('cash_received_amount');
    const output = document.getElementById('loket-cash-change');
    const warning = document.getElementById('loket-cash-warning');
    const submit = document.getElementById('loket-cash-submit');
    const form = document.getElementById('loket-cash-form');
    if (!input || !output || !warning || !submit || !form) return;

    const total = @json((int) $payment->amount);
    const currency = amount => 'Rp ' + amount.toLocaleString('id-ID');

    function updateChange() {
        const raw = input.value.trim();
        const received = Number(raw);
        const valid = raw !== '' && Number.isSafeInteger(received) && received >= total && received <= 999999999999;

        if (raw === '') {
            output.textContent = 'Rp 0';
            warning.textContent = '';
        } else if (!Number.isSafeInteger(received) || received < 0) {
            output.textContent = 'Rp 0';
            warning.textContent = 'Masukkan nominal rupiah bulat yang valid.';
        } else if (received < total) {
            output.textContent = 'Rp 0';
            warning.textContent = 'Uang yang diterima belum mencukupi total tagihan.';
        } else {
            output.textContent = currency(received - total);
            warning.textContent = '';
        }
        submit.disabled = !valid;
    }

    input.addEventListener('input', updateChange);
    form.addEventListener('submit', function (event) {
        updateChange();
        if (submit.disabled) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
    });
    updateChange();
});
</script>
@endpush
