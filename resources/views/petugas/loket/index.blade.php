@extends('layouts.petugas')

@section('title', 'Jual Tiket Langsung - Petugas')

@push('styles')
<style>
    .counter-page { min-height: calc(100vh - 72px); background: #f5f8f6; color: #173d32; }
    .counter-shell { max-width: 1470px; margin: 0 auto; padding: 38px 28px 80px; }
    .counter-heading { padding-bottom: 17px; border-bottom: 1px solid #aec4b6; margin-bottom: 22px; }
    .counter-heading h1 { font-size: clamp(25px, 2.4vw, 34px); font-weight: 750; line-height: 1.25; margin: 0; color: #0b382b; }
    .counter-heading p { margin: 9px 0 0; color: #788b80; font-size: 14px; }
    .counter-layout { display: grid; grid-template-columns: minmax(0, 2fr) minmax(330px, 1fr); gap: 20px; align-items: start; }
    .counter-left, .counter-right { display: grid; gap: 18px; min-width: 0; }
    .counter-card { background: #fff; border: 1px solid #d2dfd6; padding: 22px; min-width: 0; }
    .counter-card h2 { margin: 0; padding-bottom: 17px; border-bottom: 1px solid #d2dfd6; color: #143d31; font-size: 15px; font-weight: 750; letter-spacing: .045em; }
    .counter-card-description { margin: 13px 0 0; font-size: 13px; color: #6b8174; }
    .counter-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px 17px; margin-top: 19px; }
    .counter-field { min-width: 0; }
    .counter-field label, .counter-field-title { display: block; font-size: 12px; font-weight: 650; color: #36584a; margin-bottom: 7px; }
    .counter-control { display: block; width: 100%; min-height: 45px; padding: 10px 12px; background: #fff; border: 1px solid #c7d7cc; border-radius: 0; color: #244638; font-size: 14px; font-family: inherit; }
    .counter-control:focus { outline: 2px solid #a3cda7; outline-offset: 1px; border-color: #367457; }
    .counter-help { color: #7c8d82; font-size: 12px; line-height: 1.55; margin: 10px 0 0; }
    .counter-choice { display: flex; gap: 9px; flex-wrap: wrap; margin: 17px 0 12px; }
    .counter-tab { border: 1px solid #cfddd3; background: #fff; color: #29604a; padding: 11px 16px; min-height: 39px; font-size: 13px; font-weight: 650; cursor: pointer; }
    .counter-tab.is-active { background: #07513d; border-color: #07513d; color: white; }
    .counter-tab:focus-visible, .counter-button:focus-visible, .counter-qty-button:focus-visible { outline: 3px solid #a5cda9; outline-offset: 2px; }
    .counter-table-scroller { width: 100%; overflow-x: auto; }
    .counter-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
    .counter-table th { background: #e6ede8; color: #526e5c; text-transform: uppercase; font-size: 11px; letter-spacing: .025em; font-weight: 750; }
    .counter-table th, .counter-table td { border: 1px solid #d7e2da; padding: 12px 13px; }
    .counter-table tbody tr:hover { background: #f3f8f4; }
    .counter-table tbody tr.is-selected { background: #eef7ef; }
    .counter-table input[type='radio'] { width: 17px; height: 17px; accent-color: #07513d; cursor: pointer; }
    .counter-table .counter-pick { width: 70px; text-align: center; }
    .counter-table td[data-price] { white-space: nowrap; }
    .counter-qty-group { display: flex; flex-wrap: wrap; gap: 28px; align-items: end; margin-top: 18px; }
    .counter-qty-group .counter-field { min-width: 150px; }
    .counter-qty-controls { display: flex; align-items: stretch; margin-top: 5px; }
    .counter-qty-button { min-width: 39px; height: 43px; border: 1px solid #c6d6ca; background: #fff; color: #14513b; font-size: 18px; cursor: pointer; }
    .counter-qty-input { width: 60px; height: 43px; text-align: center; border: 1px solid #c6d6ca; border-left: 0; border-right: 0; background: #fff; color: #173d32; font-weight: 700; appearance: textfield; -moz-appearance: textfield; }
    .counter-qty-input::-webkit-outer-spin-button, .counter-qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .counter-qty-note { color: #7a8b80; font-size: 12px; margin-left: auto; padding-bottom: 11px; }
    .counter-visitors { display: grid; gap: 12px; margin-top: 17px; }
    .counter-visitor { background: #f9fbf9; border: 1px solid #dde8df; padding: 15px; }
    .counter-visitor h3 { font-size: 13px; margin: 0; color: #285441; font-weight: 750; }
    .counter-visitor .counter-form-grid { margin-top: 12px; }
    .counter-checkline { display: flex; gap: 8px; align-items: center; margin-top: 14px; font-size: 13px; color: #2e5845; cursor: pointer; }
    .counter-checkline input { accent-color: #07513d; }
    .counter-summary-line { display: flex; align-items: flex-start; justify-content: space-between; gap: 15px; padding: 13px 0; border-bottom: 1px solid #e7eee8; font-size: 14px; }
    .counter-summary-line span { color: #5d7969; }
    .counter-summary-line strong { text-align: right; font-weight: 700; overflow-wrap: anywhere; }
    .counter-summary-total { margin: 11px 0 13px; display: flex; justify-content: space-between; gap: 12px; align-items: baseline; font-size: 17px; font-weight: 750; }
    .counter-summary-total strong { font-size: 26px; color: #07513d; font-weight: 800; white-space: nowrap; }
    .counter-method-label { font-size: 12px; font-weight: 700; color: #546b5d; display: block; padding: 16px 0 8px; border-top: 1px solid #e4eee7; }
    .counter-methods { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
    .counter-method { position: relative; display: flex; cursor: pointer; }
    .counter-method input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; cursor: pointer; }
    .counter-method span { width: 100%; border: 1px solid #cddfd3; color: #07513d; background: #fff; padding: 13px; font-size: 13px; font-weight: 750; text-align: center; }
    .counter-method input:checked + span { color: #fff; background: #07513d; border-color: #07513d; }
    .counter-method input:focus-visible + span { outline: 3px solid #a5cda9; outline-offset: 2px; }
    .counter-actions { margin-top: 15px; display: grid; gap: 8px; }
    .counter-button { min-height: 47px; border: 1px solid #07513d; padding: 12px 16px; background: #fff; color: #07513d; font-size: 13px; font-weight: 750; text-align: center; cursor: pointer; font-family: inherit; }
    .counter-button.primary { color: #fff; background: #07513d; }
    .counter-button.primary:hover { background: #064332; }
    .counter-button.secondary:hover { background: #f0f7f1; }
    .counter-button:disabled { cursor: not-allowed; opacity: .55; }
    .counter-note { border: 1px dashed #bdd2c2; color: #718579; padding: 15px 17px; font-size: 12px; line-height: 1.6; background: transparent; }
    .counter-errors { border: 1px solid #e4a7a7; color: #a82727; background: #fff6f6; padding: 15px 19px; margin-bottom: 18px; font-size: 13px; }
    .counter-errors strong { display: block; margin-bottom: 8px; }
    .counter-errors ul { padding-left: 18px; margin: 0; list-style: disc; }
    .counter-inline-alert { margin-top: 9px; font-size: 12px; color: #a72e2e; }
    .counter-empty { padding: 19px; border: 1px solid #d7e2da; color: #697f70; font-size: 13px; }
    .counter-verified { margin: 11px 0 0; font-size: 12px; color: #32714e; }
    @media (max-width: 1040px) { .counter-layout { grid-template-columns: minmax(0, 1fr); } .counter-right { grid-row: auto; } }
    @media (max-width: 640px) { .counter-shell { padding: 25px 14px 55px; } .counter-card { padding: 16px; } .counter-form-grid { grid-template-columns: 1fr; } .counter-qty-note { width: 100%; padding: 0; margin-left: 0; } .counter-summary-total strong { font-size: 22px; } .counter-table { min-width: 510px; } }
    .counter-confirm-modal {
        border: 1px solid #d6e1d8;
        border-radius: 12px;
        padding: 0;
        width: calc(100% - 32px);
        max-width: 440px;
        margin: auto;
        color: #173c32;
        background: #fff;
        box-shadow: 0 16px 50px rgba(0, 0, 0, .18);
    }

    .counter-confirm-modal::backdrop {
        background: rgba(0, 0, 0, .45);
    }

    .counter-confirm-content {
        padding: 28px;
    }

    .counter-confirm-content h2 {
        margin: 0 0 12px;
        font-size: 22px;
        color: #0b5a43;
    }

    .counter-confirm-content p {
        margin: 0 0 24px;
        font-size: 14px;
        line-height: 1.7;
        color: #52665c;
    }

    .counter-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .counter-confirm-actions button {
        border: 1px solid #0b5a43;
        border-radius: 6px;
        padding: 12px 18px;
        font-weight: 600;
        cursor: pointer;
    }

    #counter-confirm-cancel {
        background: #fff;
        color: #0b5a43;
    }

    #counter-confirm-continue {
        background: #0b5a43;
        color: #fff;
    }

</style>
@endpush

@php
    $selectedMethod = old('payment_method', request()->input('payment_method', 'cash'));
    $selectedType = old('visitor_type', request()->input('visitor_type', 'domestik'));
    $selectedTicket = old('items.0.ticket_reference', request()->input('items.0.ticket_reference', ''));
    $savedVisitors = old('items.0.visitors', request()->input('items.0.visitors', []));
@endphp

@section('content')
<div class="counter-page">
    <div class="counter-shell">
        <header class="counter-heading">
            <h1>Jual Tiket Langsung</h1>
            <p>Walk-in visitor · tanpa booking</p>
        </header>

        @if ($errors->any())
            <div class="counter-errors" role="alert">
                <strong>Periksa kembali data transaksi:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="counter-sale-form" method="POST" action="{{ route('petugas.loket.checkout') }}">
            @csrf
            <input type="hidden" name="checkout_token" value="{{ old('checkout_token', request()->input('checkout_token', $checkoutToken)) }}">
            <input type="hidden" name="booker_is_visitor" value="0">

            <div class="counter-layout">
                <div class="counter-left">
                    <section class="counter-card" aria-labelledby="counter-visitor-title">
                        <h2 id="counter-visitor-title">1. DATA PENGUNJUNG</h2>
                        <div class="counter-form-grid">
                            <div class="counter-field">
                                <label for="booker_name">Nama Pengunjung / Pemesan</label>
                                <input class="counter-control" id="booker_name" type="text" name="booker[name]" maxlength="255" required autocomplete="name" placeholder="Nama lengkap" value="{{ old('booker.name', request()->input('booker.name', '')) }}">
                            </div>
                            <div class="counter-field">
                                <label for="booker_identity">No. Identitas (KTP/Paspor)</label>
                                <input class="counter-control" id="booker_identity" type="text" name="booker[identity_number]" maxlength="50" required placeholder="Nomor identitas" value="{{ old('booker.identity_number', request()->input('booker.identity_number', '')) }}">
                            </div>
                            <div class="counter-field">
                                <label for="booker_phone">Nomor Telepon</label>
                                <input class="counter-control" id="booker_phone" type="tel" name="booker[phone]" maxlength="25" required placeholder="08xxxxxxxxxx" value="{{ old('booker.phone', request()->input('booker.phone', '')) }}">
                            </div>
                            <div class="counter-field">
                                <label for="visitor_type">Jenis Wisatawan</label>
                                <select class="counter-control" id="visitor_type" name="visitor_type" required>
                                    <option value="domestik" @selected($selectedType === 'domestik')>Domestik (WNI)</option>
                                    <option value="mancanegara" @selected($selectedType === 'mancanegara')>Mancanegara (WNA)</option>
                                </select>
                            </div>
                            <div class="counter-field">
                                <label for="booker_email">Email Pemesan</label>
                                <input class="counter-control" id="booker_email" type="email" name="booker[email]" maxlength="255" required placeholder="pengunjung@example.com" value="{{ old('booker.email', request()->input('booker.email', '')) }}">
                            </div>
                            <div class="counter-field">
                                <label for="visit_date">Tanggal Kunjungan</label>
                                <input class="counter-control" id="visit_date" type="date" name="visit_date" min="{{ $today }}" required value="{{ old('visit_date', request()->input('visit_date', $today)) }}">
                            </div>
                        </div>
                        <p class="counter-help">Email dan tanggal kunjungan tetap diperlukan oleh proses checkout. Pengunjung tidak wajib memiliki akun Wisatawan.</p>
                    </section>

                    <section class="counter-card" aria-labelledby="counter-ticket-title">
                        <h2 id="counter-ticket-title">2. PILIH TIKET</h2>
                        <div class="counter-choice" role="group" aria-label="Kategori tiket">
                            <button type="button" class="counter-tab is-active" data-ticket-tab="masuk" aria-pressed="true">Tiket Masuk</button>
                            <button type="button" class="counter-tab" data-ticket-tab="wahana" aria-pressed="false">Tiket Wahana</button>
                        </div>
                        <div class="counter-table-scroller">
                            <table class="counter-table">
                                <thead>
                                    <tr>
                                        <th class="counter-pick">Pilih</th>
                                        <th>Jenis Tiket</th>
                                        <th>Harga Dewasa</th>
                                        <th>Harga Anak</th>
                                    </tr>
                                </thead>
                                <tbody id="counter-ticket-list">
                                    @foreach ($tickets as $ticket)
                                        @php
                                            $category = str_contains(strtolower((string) ($ticket['category'] ?? '')), 'wahana') ? 'wahana' : 'masuk';
                                        @endphp
                                        @foreach ($ticket['rates'] ?? [] as $rate)
                                            @php
                                                $adultPrice = $rate['adult_price'] ?? null;
                                                $childPrice = $rate['child_price'] ?? null;
                                            @endphp
                                            <tr data-ticket-row data-type="{{ $rate['visitor_type'] }}" data-category="{{ $category }}" data-adult-price="{{ is_numeric($adultPrice) ? $adultPrice : '' }}" data-child-price="{{ is_numeric($childPrice) ? $childPrice : '' }}">
                                                <td class="counter-pick">
                                                    <input type="radio" name="items[0][ticket_reference]" value="{{ $ticket['reference'] }}" data-ticket-choice aria-label="Pilih {{ $ticket['name'] }}" @checked($selectedTicket === $ticket['reference'] && $selectedType === $rate['visitor_type']) required>
                                                </td>
                                                <td>
                                                    <strong>{{ $ticket['name'] }}</strong>
                                                    <div class="counter-help">Kuota: {{ $ticket['available_quota'] ?? '-' }}</div>
                                                </td>
                                                <td data-price>{{ is_numeric($adultPrice) ? 'Rp ' . number_format((float) $adultPrice, 0, ',', '.') : '-' }}</td>
                                                <td data-price>{{ is_numeric($childPrice) ? 'Rp ' . number_format((float) $childPrice, 0, ',', '.') : '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div id="counter-no-tickets" class="counter-empty" hidden>Tidak ada tiket aktif untuk kategori dan jenis wisatawan ini.</div>
                        <div class="counter-qty-group">
                            <div class="counter-field">
                                <label for="adult_quantity">Jumlah Dewasa</label>
                                <div class="counter-qty-controls">
                                    <button class="counter-qty-button" type="button" data-change-qty="adult:-1" aria-label="Kurangi jumlah dewasa">−</button>
                                    <input class="counter-qty-input" id="adult_quantity" name="items[0][adult_quantity]" type="number" min="0" max="4" step="1" required value="{{ old('items.0.adult_quantity', request()->input('items.0.adult_quantity', 1)) }}">
                                    <button class="counter-qty-button" type="button" data-change-qty="adult:1" aria-label="Tambah jumlah dewasa">+</button>
                                </div>
                            </div>
                            <div class="counter-field">
                                <label for="child_quantity">Jumlah Anak</label>
                                <div class="counter-qty-controls">
                                    <button class="counter-qty-button" type="button" data-change-qty="child:-1" aria-label="Kurangi jumlah anak">−</button>
                                    <input class="counter-qty-input" id="child_quantity" name="items[0][child_quantity]" type="number" min="0" max="4" step="1" required value="{{ old('items.0.child_quantity', request()->input('items.0.child_quantity', 0)) }}">
                                    <button class="counter-qty-button" type="button" data-change-qty="child:1" aria-label="Tambah jumlah anak">+</button>
                                </div>
                            </div>
                            <p class="counter-qty-note">Maks. 4 tiket per transaksi</p>
                        </div>
                        <p id="counter-quantity-warning" class="counter-inline-alert" role="status" hidden>Jumlah dewasa dan anak harus 1–4 orang secara total.</p>
                    </section>

                    <section class="counter-card" aria-labelledby="counter-identities-title">
                        <h2 id="counter-identities-title">DATA IDENTITAS SETIAP PENGUNJUNG</h2>
                        <p class="counter-card-description">Untuk penerbitan E-Tiket dan QR Check-In, setiap orang harus memiliki data nama dan identitas.</p>
                        <label class="counter-checkline" for="booker_is_visitor">
                            <input id="booker_is_visitor" type="checkbox" name="booker_is_visitor" value="1" @checked((string) old('booker_is_visitor', request()->input('booker_is_visitor', '1')) === '1')>
                            Pemesan juga ikut berkunjung sebagai pengunjung dewasa pertama
                        </label>
                        <div id="visitor-fields" class="counter-visitors"></div>
                        <p id="visitor-info" class="counter-help" role="status">Form identitas menyesuaikan jumlah tiket.</p>
                    </section>
                </div>

                <aside class="counter-right">
                    <section class="counter-card" aria-labelledby="counter-summary-title">
                        <h2 id="counter-summary-title">3. RINGKASAN TRANSAKSI</h2>
                        <div class="counter-summary-line"><span>Tiket Dipilih</span><strong id="summary-ticket">Belum dipilih</strong></div>
                        <div class="counter-summary-line"><span id="summary-adult-label">Dewasa × 1</span><strong id="summary-adult">-</strong></div>
                        <div class="counter-summary-line"><span id="summary-child-label">Anak × 0</span><strong id="summary-child">-</strong></div>
                        <div class="counter-summary-total"><span>Total</span><strong id="summary-total">Rp 0</strong></div>
                        @if ($quote !== null)
                            <p class="counter-verified" id="server-quote-note">Harga sudah diperiksa server saat preview. Total akan divalidasi ulang ketika checkout.</p>
                        @else
                            <p class="counter-help" id="server-quote-note">Ringkasan merupakan estimasi dari katalog. Harga dan kuota akan diperiksa kembali oleh server saat checkout.</p>
                        @endif
                        <span class="counter-method-label">Metode Pembayaran</span>
                        <div class="counter-methods">
                            <label class="counter-method"><input type="radio" name="payment_method" value="cash" @checked($selectedMethod === 'cash') required><span>CASH</span></label>
                            <label class="counter-method"><input type="radio" name="payment_method" value="qris" @checked($selectedMethod === 'qris') required><span>QRIS</span></label>
                        </div>
                        <div class="counter-actions">
                            <button type="submit" class="counter-button primary" id="counter-checkout-button">Proses Transaksi →</button>
                            <button type="submit" class="counter-button secondary" formaction="{{ route('petugas.loket.preview') }}" formmethod="POST">Cek Harga di Server</button>
                        </div>
                    </section>
                    <div class="counter-note">Pastikan identitas dan jumlah pengunjung sudah benar sebelum memproses transaksi. Pembayaran Cash dikonfirmasi setelah uang diterima; QRIS diverifikasi melalui Midtrans Sandbox.</div>
                </aside>
            </div>
        </form>

        <dialog id="counter-confirm-modal" class="counter-confirm-modal"
            aria-labelledby="counter-confirm-title"
            aria-describedby="counter-confirm-description">

            <div class="counter-confirm-content">
                <h2 id="counter-confirm-title">Konfirmasi Transaksi</h2>

                <p id="counter-confirm-description">
                    Pastikan data pengunjung, tiket, dan metode
                    pembayaran sudah benar sebelum melanjutkan transaksi.
                </p>

                <div class="counter-confirm-actions">
                    <button type="button" id="counter-confirm-cancel">
                        Batal
                    </button>

                    <button type="button" id="counter-confirm-continue">
                        Ya, Proses Transaksi
                    </button>
                </div>
            </div>
        </dialog>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('counter-sale-form');
    const typeInput = document.getElementById('visitor_type');
    const ticketRows = Array.from(document.querySelectorAll('[data-ticket-row]'));
    const ticketChoices = Array.from(document.querySelectorAll('[data-ticket-choice]'));
    const tabs = Array.from(document.querySelectorAll('[data-ticket-tab]'));
    const adultInput = document.getElementById('adult_quantity');
    const childInput = document.getElementById('child_quantity');
    const visitorContainer = document.getElementById('visitor-fields');
    const visitorInfo = document.getElementById('visitor-info');
    const bookerCheckbox = document.getElementById('booker_is_visitor');
    const bookerName = document.getElementById('booker_name');
    const bookerIdentity = document.getElementById('booker_identity');
    const checkoutButton = document.getElementById('counter-checkout-button');
    const quantityWarning = document.getElementById('counter-quantity-warning');
    const emptyTickets = document.getElementById('counter-no-tickets');
    const storedVisitors = @json($savedVisitors);
    let draftVisitors = Array.isArray(storedVisitors) ? storedVisitors : Object.values(storedVisitors || {});
    let activeCategory = 'masuk';
    const currentSelectedRow = ticketRows.find(row => row.querySelector('input:checked'));
    if (currentSelectedRow) activeCategory = currentSelectedRow.dataset.category;

    const rupiah = amount => 'Rp ' + Number(amount).toLocaleString('id-ID');
    const integerQuantity = input => Math.max(0, Math.min(4, Math.trunc(Number(input.value) || 0)));

    function updateTicketVisibility() {
        const selected = ticketRows.find(row => row.querySelector('input:checked'));
        // Jenis wisatawan berubah: jangan kirim tarif negara yang salah.
        if (selected && (selected.dataset.type !== typeInput.value || selected.dataset.category !== activeCategory)) {
            selected.querySelector('input').checked = false;
        }
        let count = 0;
        ticketRows.forEach(row => {
            const typeMatches = row.dataset.type === typeInput.value;
            const categoryMatches = row.dataset.category === activeCategory;
            row.hidden = !typeMatches || !categoryMatches;
            // Hanya pilihan yang sedang terlihat boleh dikirim dalam checkout.
            row.querySelector('input').disabled = !typeMatches || !categoryMatches;
            if (typeMatches && categoryMatches) count++;
            row.classList.toggle('is-selected', row.querySelector('input').checked);
        });
        emptyTickets.hidden = count !== 0;
        tabs.forEach(tab => {
            const isActive = tab.dataset.ticketTab === activeCategory;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        updateSummary();
    }

    function updateSummary() {
        const selected = ticketRows.find(row => row.querySelector('input:checked') && !row.querySelector('input').disabled);
        const adults = integerQuantity(adultInput);
        const children = integerQuantity(childInput);
        const validCount = adults + children >= 1 && adults + children <= 4;
        document.getElementById('summary-adult-label').textContent = 'Dewasa × ' + adults;
        document.getElementById('summary-child-label').textContent = 'Anak × ' + children;
        quantityWarning.hidden = validCount;
        checkoutButton.disabled = !selected || !validCount;
        if (!selected) {
            document.getElementById('summary-ticket').textContent = 'Belum dipilih';
            document.getElementById('summary-adult').textContent = '-';
            document.getElementById('summary-child').textContent = '-';
            document.getElementById('summary-total').textContent = 'Rp 0';
            return;
        }
        const title = selected.querySelector('td:nth-child(2) strong');
        document.getElementById('summary-ticket').textContent = title ? title.textContent : '-';
        const adultPrice = selected.dataset.adultPrice;
        const childPrice = selected.dataset.childPrice;
        if (adultPrice === '' || childPrice === '') {
            document.getElementById('summary-adult').textContent = '-';
            document.getElementById('summary-child').textContent = '-';
            document.getElementById('summary-total').textContent = 'Cek tarif';
            return;
        }
        const adultSubtotal = Number(adultPrice) * adults;
        const childSubtotal = Number(childPrice) * children;
        document.getElementById('summary-adult').textContent = rupiah(adultSubtotal);
        document.getElementById('summary-child').textContent = rupiah(childSubtotal);
        document.getElementById('summary-total').textContent = rupiah(adultSubtotal + childSubtotal);
    }

    function captureVisitors() {
        if (!visitorContainer.children.length) return;
        draftVisitors = Array.from(visitorContainer.querySelectorAll('[data-visitor]')).map(card => ({
            age_category: card.dataset.age,
            name: card.querySelector('[data-name]').value,
            identity_number: card.querySelector('[data-identity]').value
        }));
    }

    function renderVisitors() {
        captureVisitors();
        const adults = integerQuantity(adultInput);
        const children = integerQuantity(childInput);
        const ages = [...Array(adults).fill('adult'), ...Array(children).fill('child')];
        const oldByAge = {
            adult: draftVisitors.filter(item => item.age_category === 'adult'),
            child: draftVisitors.filter(item => item.age_category === 'child')
        };
        const counters = { adult: 0, child: 0 };
        visitorContainer.replaceChildren();
        ages.forEach((age, index) => {
            const previous = oldByAge[age][counters[age]++] || {};
            const card = document.createElement('div');
            card.className = 'counter-visitor';
            card.dataset.visitor = '1';
            card.dataset.age = age;
            const heading = document.createElement('h3');
            heading.textContent = 'Pengunjung ' + (index + 1) + ' — ' + (age === 'adult' ? 'Dewasa' : 'Anak');
            card.appendChild(heading);
            const grid = document.createElement('div');
            grid.className = 'counter-form-grid';
            const prefix = 'items[0][visitors][' + index + ']';
            [['Nama Lengkap', 'name', 'name', 255], ['Nomor Identitas (KTP/Paspor/KK)', 'identity_number', 'identity', 50]].forEach(([labelText, fieldName, datasetKey, limit]) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'counter-field';
                const label = document.createElement('label');
                const id = 'counter-visitor-' + index + '-' + fieldName;
                label.htmlFor = id;
                label.textContent = labelText;
                const input = document.createElement('input');
                input.className = 'counter-control';
                input.id = id;
                input.type = 'text';
                input.name = prefix + '[' + fieldName + ']';
                input.maxLength = limit;
                input.required = true;
                input.value = previous[fieldName] || '';
                input.dataset[datasetKey] = '1';
                wrapper.append(label, input);
                grid.appendChild(wrapper);
            });
            card.appendChild(grid);
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = prefix + '[age_category]';
            hidden.value = age;
            card.appendChild(hidden);
            visitorContainer.appendChild(card);
        });
        bookerCheckbox.disabled = adults === 0;
        if (adults === 0) bookerCheckbox.checked = false;
        syncBooker();
        visitorInfo.textContent = ages.length > 0
            ? 'Total ' + ages.length + ' pengunjung. Lengkapi identitas tiap orang sesuai kategori usia.'
            : 'Pilih minimal satu tiket untuk mengisi identitas pengunjung.';
        updateSummary();
    }

    function syncBooker() {
        if (!bookerCheckbox.checked) return;
        const firstAdult = visitorContainer.querySelector('[data-age="adult"]');
        if (!firstAdult) return;
        firstAdult.querySelector('[data-name]').value = bookerName.value;
        firstAdult.querySelector('[data-identity]').value = bookerIdentity.value;
    }

    tabs.forEach(tab => tab.addEventListener('click', function () {
        activeCategory = tab.dataset.ticketTab;
        updateTicketVisibility();
    }));
    ticketChoices.forEach(choice => choice.addEventListener('change', updateTicketVisibility));
    typeInput.addEventListener('change', updateTicketVisibility);
    document.querySelectorAll('[data-change-qty]').forEach(button => button.addEventListener('click', function () {
        const [kind, delta] = button.dataset.changeQty.split(':');
        const input = kind === 'adult' ? adultInput : childInput;
        const otherInput = kind === 'adult' ? childInput : adultInput;
        const maxAllowed = Math.max(0, 4 - integerQuantity(otherInput));
        input.value = Math.max(0, Math.min(maxAllowed, integerQuantity(input) + Number(delta)));
        renderVisitors();
    }));
    [adultInput, childInput].forEach(input => input.addEventListener('change', function () {
        input.value = Math.min(integerQuantity(input), Math.max(0, 4 - integerQuantity(input === adultInput ? childInput : adultInput)));
        renderVisitors();
    }));
    bookerCheckbox.addEventListener('change', syncBooker);
    bookerName.addEventListener('input', syncBooker);
    bookerIdentity.addEventListener('input', syncBooker);

    const confirmModal = document.getElementById('counter-confirm-modal');
    const confirmCancel = document.getElementById('counter-confirm-cancel');
    const confirmContinue = document.getElementById('counter-confirm-continue');

    let checkoutConfirmed = false;

    form.addEventListener('submit', function (event) {
        if (event.submitter !== checkoutButton || checkoutConfirmed) {
            return;
        }

        event.preventDefault();
        confirmModal.showModal();
    });

    confirmCancel.addEventListener('click', function () {
        confirmModal.close();
    });

    confirmContinue.addEventListener('click', function () {
        confirmModal.close();
        checkoutConfirmed = true;
        form.requestSubmit(checkoutButton);
    });

    updateTicketVisibility();
    renderVisitors();
});
</script>
@endpush
