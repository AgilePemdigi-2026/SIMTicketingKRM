@extends('layouts.app')

@section('title', 'Loket Tiket - SIMTicketingKRM')

@push('styles')
    @include('partials.ticket-payment-styles')
@endpush

@section('content')
<div class="purchase-page krm-container">

    <div class="purchase-heading">
        <h1>Loket Pembelian Tiket</h1>
        <p>Layani pembelian tiket langsung oleh pengunjung.</p>
    </div>

    <section class="krm-card payment-card">

        <h2>Form Pembelian Tiket</h2>

        <p>Isi detail kunjungan untuk menghitung harga tiket.</p>

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 p-4 text-red-700">
                <strong>Periksa kembali data berikut:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('petugas.loket.preview') }}">
            @csrf

            <div class="mb-5">
                <label for="visitor_type" class="mb-2 block font-semibold">
                    Jenis Wisatawan
                </label>

                <select id="visitor_type"
                        name="visitor_type"
                        required
                        class="w-full rounded border p-3">

                    <option value="domestik"
                        @selected(old('visitor_type', request('visitor_type', 'domestik')) === 'domestik')>
                        Domestik (WNI)
                    </option>

                    <option value="mancanegara"
                        @selected(old('visitor_type', request('visitor_type')) === 'mancanegara')>
                        Mancanegara (WNA)
                    </option>
                </select>
            </div>

            <div class="mb-5">
                <label for="visit_date" class="mb-2 block font-semibold">
                    Tanggal Kunjungan
                </label>

                <input type="date"
                       id="visit_date"
                       name="visit_date"
                       value="{{ old('visit_date', request('visit_date', $today)) }}"
                       min="{{ $today }}"
                       required
                       class="w-full rounded border p-3">
            </div>

            <div class="mb-5">
                <label for="ticket_reference" class="mb-2 block font-semibold">
                    Pilih Tiket
                </label>

                <select id="ticket_reference"
                        name="items[0][ticket_reference]"
                        required
                        class="w-full rounded border p-3">

                    <option value="">-- Pilih Tiket --</option>

                    @foreach ($tickets as $ticket)
                        @foreach ($ticket['rates'] as $rate)
                            <option value="{{ $ticket['reference'] }}"
                                    data-visitor-type="{{ $rate['visitor_type'] }}"
                                    @selected(old('items.0.ticket_reference', request()->input('items.0.ticket_reference')) === $ticket['reference'])>
                                {{ $ticket['name'] }}
                                — {{ ucfirst($rate['visitor_type']) }}
                                (Kuota: {{ $ticket['available_quota'] }})
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>
                    <label for="adult_quantity" class="mb-2 block font-semibold">
                        Jumlah Dewasa
                    </label>

                    <input type="number"
                           id="adult_quantity"
                           name="items[0][adult_quantity]"
                           min="0"
                           max="4"
                           value="{{ old('items.0.adult_quantity', request()->input('items.0.adult_quantity', 1)) }}"
                           required
                           class="w-full rounded border p-3">
                </div>

                <div>
                    <label for="child_quantity" class="mb-2 block font-semibold">
                        Jumlah Anak
                    </label>

                    <input type="number"
                           id="child_quantity"
                           name="items[0][child_quantity]"
                           min="0"
                           max="4"
                           value="{{ old('items.0.child_quantity', request()->input('items.0.child_quantity', 0)) }}"
                           required
                           class="w-full rounded border p-3">
                </div>

            </div>

            <p class="mb-4 text-sm text-gray-600">
                Maksimal 4 tiket per transaksi.
                Jumlah dewasa dan anak tidak boleh keduanya nol.
            </p>
            
            <div class="mt-6 mb-6 border-t border-gray-200 pt-5">

                <h3 class="mb-2 font-semibold">
                    Identitas Pemesan
                </h3>

                <p class="mb-5 text-sm text-gray-600">
                    Isi identitas pengunjung yang melakukan pembelian
                    tiket secara langsung di loket.
                </p>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div>
                        <label for="booker_name" class="mb-2 block font-semibold">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            id="booker_name"
                            name="booker[name]"
                            maxlength="255"
                            value="{{ old('booker.name', request()->input('booker.name', '')) }}"
                            placeholder="Masukkan nama lengkap"
                            class="w-full rounded border p-3"
                        >
                    </div>

                    <div>
                        <label for="booker_identity" class="mb-2 block font-semibold">
                            Nomor Identitas (KTP/Paspor)
                        </label>

                        <input
                            type="text"
                            id="booker_identity"
                            name="booker[identity_number]"
                            maxlength="50"
                            value="{{ old('booker.identity_number', request()->input('booker.identity_number', '')) }}"
                            placeholder="Masukkan nomor identitas"
                            class="w-full rounded border p-3"
                        >
                    </div>

                    <div>
                        <label for="booker_phone" class="mb-2 block font-semibold">
                            Nomor Telepon
                        </label>

                        <input
                            type="tel"
                            id="booker_phone"
                            name="booker[phone]"
                            maxlength="25"
                            value="{{ old('booker.phone', request()->input('booker.phone', '')) }}"
                            placeholder="Contoh: 081234567890"
                            class="w-full rounded border p-3"
                        >
                    </div>

                    <div>
                        <label for="booker_email" class="mb-2 block font-semibold">
                            Email
                        </label>

                        <input
                            type="email"
                            id="booker_email"
                            name="booker[email]"
                            maxlength="255"
                            value="{{ old('booker.email', request()->input('booker.email', '')) }}"
                            placeholder="Contoh: pengunjung@example.com"
                            class="w-full rounded border p-3"
                        >
                    </div>

                </div>

            </div>
            
            {{-- DATA PENGUNJUNG --}}
            <div class="mt-6 mb-6 border-t border-gray-200 pt-5">

                <h3 class="mb-2 font-semibold">
                    Data Pengunjung
                </h3>

                <p class="mb-4 text-sm text-gray-600">
                    Lengkapi identitas setiap pengunjung sesuai
                    jumlah tiket dewasa dan anak.
                </p>

                <input type="hidden" name="booker_is_visitor" value="0">

                <label class="mb-5 flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="booker_is_visitor"
                        name="booker_is_visitor"
                        value="1"
                        @checked((string) old('booker_is_visitor', request()->input('booker_is_visitor', '1')) === '1')
                    >
                    <span>Pemesan ikut berkunjung</span>
                </label>

                <div id="visitor-fields" class="space-y-4"></div>

                <p id="visitor-info" class="mt-4 text-sm text-gray-600">
                    Form pengunjung akan menyesuaikan jumlah tiket.
                </p>

            </div>
            {{-- AKHIR DATA PENGUNJUNG --}}

            {{-- METODE PEMBAYARAN --}}
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <h3 style="font-weight: 600; margin-bottom: 8px;">
                    Metode Pembayaran
                </h3>

                <p style="margin-bottom: 16px; color: #64748b;">
                    Pilih metode pembayaran untuk pembelian tiket langsung.
                </p>

                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input
                            type="radio"
                            name="payment_method"
                            value="cash"
                            @checked(old('payment_method', request()->input('payment_method', 'cash')) === 'cash')
                        >
                        Cash (Tunai)
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input
                            type="radio"
                            name="payment_method"
                            value="qris"
                            @checked(old('payment_method', request()->input('payment_method', 'cash')) === 'qris')
                        >
                        QRIS
                    </label>
                </div>

                <p style="margin-top: 12px; font-size: 13px; color: #64748b;">
                    Pembayaran belum diproses. Tombol Cek Harga Tiket
                    hanya menghitung estimasi biaya.
                </p>
            </div>
            {{-- AKHIR METODE PEMBAYARAN --}}

            <button type="submit" class="krm-btn krm-btn-primary">
                Cek Harga Tiket
            </button>

        </form>
    </section>

    @if ($quote !== null)
        <section class="krm-card payment-card mt-6">

            <h2>Ringkasan Harga</h2>

            @foreach ($quote['items'] as $item)
                <div class="border-b py-4">

                    <h3 class="font-semibold">
                        {{ $item['ticket_name'] }}
                    </h3>

                    <p>Dewasa: {{ $item['adult_quantity'] }}</p>
                    <p>Anak: {{ $item['child_quantity'] }}</p>

                    <p class="mt-2">
                        Subtotal:
                        Rp {{ number_format((float) $item['subtotal'], 0, ',', '.') }}
                    </p>

                </div>
            @endforeach

            <div class="mt-5 flex items-center justify-between">
                <strong>Total Estimasi</strong>

                <strong class="text-xl">
                    Rp {{ number_format((float) $quote['total'], 0, ',', '.') }}
                </strong>
            </div>

            <p class="mt-3 text-sm text-gray-600">
                Harga dan ketersediaan akan diperiksa kembali
                ketika pesanan disimpan.
            </p>

        </section>
        @if (old('payment_method', request()->input('payment_method', 'cash')) === 'cash')
            <section class="krm-card payment-card mt-6" id="cash-preview">
                <h2>Simulasi Pembayaran Cash</h2>

                <p class="mt-3">Total Tagihan</p>
                <h3 class="text-xl font-semibold">
                    Rp {{ number_format((float) $quote['total'], 0, ',', '.') }}
                </h3>

                <div class="mt-5">
                    <label for="cash-received" class="block font-semibold">
                        Uang yang Diterima (Rp)
                    </label>

                    <input
                        type="number"
                        id="cash-received"
                        min="0"
                        step="1000"
                        placeholder="Masukkan nominal uang"
                        class="mt-2 w-full rounded border p-3"
                    >
                </div>

                <div class="mt-5">
                    <p>Kembalian</p>
                    <h3 id="cash-change" class="text-xl font-semibold">
                        Rp 0
                    </h3>

                    <p id="cash-warning" class="mt-2 text-sm" style="color: #b91c1c;"></p>
                </div>

                <p class="mt-4 text-sm text-gray-600">
                    Ini hanya simulasi. Pembayaran belum dicatat
                    atau dikonfirmasi.
                </p>
            </section>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const total = @json((float) $quote['total']);
                    const receivedInput = document.getElementById('cash-received');
                    const changeOutput = document.getElementById('cash-change');
                    const warningOutput = document.getElementById('cash-warning');

                    receivedInput.addEventListener('input', function () {
                        const received = Number(receivedInput.value);

                        if (!receivedInput.value) {
                            changeOutput.textContent = 'Rp 0';
                            warningOutput.textContent = '';
                            return;
                        }

                        if (received < total) {
                            changeOutput.textContent = 'Rp 0';
                            warningOutput.textContent =
                                'Uang yang diterima belum mencukupi.';
                            return;
                        }

                        const change = received - total;

                        changeOutput.textContent =
                            'Rp ' + change.toLocaleString('id-ID');

                        warningOutput.textContent = '';
                    });
                });
            </script>
        @endif
    @endif

    <div class="purchase-actions">
        <a href="{{ route('petugas.dashboard') }}" class="krm-btn">
            Kembali ke Dashboard
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const visitorType = document.getElementById('visitor_type');
    const ticketSelect = document.getElementById('ticket_reference');

    function updateTicketOptions() {
        let selectedStillAvailable = false;

        Array.from(ticketSelect.options).forEach(function (option) {
            if (!option.value) return;

            const matches = option.dataset.visitorType === visitorType.value;

            option.hidden = !matches;
            option.disabled = !matches;

            if (option.selected && matches) {
                selectedStillAvailable = true;
            }
        });

        if (!selectedStillAvailable) {
            ticketSelect.value = '';
        }
    }

    visitorType.addEventListener('change', updateTicketOptions);
    updateTicketOptions();
});
</script>
@endpush


@php
    $savedVisitors = old(
        'items.0.visitors',
        request()->input('items.0.visitors', [])
    );
@endphp

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const adultInput = document.getElementById('adult_quantity');
    const childInput = document.getElementById('child_quantity');
    const visitorContainer = document.getElementById('visitor-fields');
    const visitorInfo = document.getElementById('visitor-info');
    const bookerCheckbox = document.getElementById('booker_is_visitor');

    const bookerName = document.getElementById('booker_name');
    const bookerIdentity = document.getElementById('booker_identity');

    const savedVisitors = @json($savedVisitors);
    let visitorDraft = Array.isArray(savedVisitors)
        ? savedVisitors
        : [];

    function makeField(labelText, name, value, maxLength) {
        const wrapper = document.createElement('div');

        const label = document.createElement('label');
        label.className = 'mb-2 block font-semibold';
        label.textContent = labelText;

        const input = document.createElement('input');
        input.type = 'text';
        input.name = name;
        input.value = value || '';
        input.maxLength = maxLength;
        input.className = 'w-full rounded border p-3';

        label.appendChild(input);
        wrapper.appendChild(label);

        return input;
    }

    function readCurrentVisitors() {
        const fields = visitorContainer.querySelectorAll(
            '[data-visitor]'
        );

        return Array.from(fields).map(function (element) {
            return {
                name: element.querySelector('[data-name]').value,
                identity_number: element.querySelector(
                    '[data-identity]'
                ).value,
                age_category: element.dataset.age
            };
        });
    }

    function syncBooker() {
        if (!bookerCheckbox.checked) return;

        const firstAdult = visitorContainer.querySelector(
            '[data-age="adult"]'
        );

        if (!firstAdult) return;

        firstAdult.querySelector('[data-name]').value =
            bookerName.value;

        firstAdult.querySelector('[data-identity]').value =
            bookerIdentity.value;
    }

    function renderVisitors() {
        if (visitorContainer.children.length > 0) {
            visitorDraft = readCurrentVisitors();
        }

        const adults = Math.max(
            0, Math.min(4, Number(adultInput.value) || 0)
        );

        const children = Math.max(
            0, Math.min(4, Number(childInput.value) || 0)
        );

        const categories = [
            ...Array(adults).fill('adult'),
            ...Array(children).fill('child')
        ];

        visitorContainer.replaceChildren();

        categories.forEach(function (age, index) {
            const previous = visitorDraft[index];

            const sameCategory = previous &&
                previous.age_category === age;

            const data = sameCategory ? previous : {};

            const card = document.createElement('div');
            card.className = 'rounded border border-gray-200 p-4';
            card.dataset.visitor = '1';
            card.dataset.age = age;

            const heading = document.createElement('h4');
            heading.className = 'mb-4 font-semibold';
            heading.textContent =
                'Pengunjung ' + (index + 1) +
                ' — ' + (age === 'adult' ? 'Dewasa' : 'Anak');

            card.appendChild(heading);

            const grid = document.createElement('div');
            grid.className =
                'grid grid-cols-1 gap-4 md:grid-cols-2';

            const prefix = 'items[0][visitors][' + index + ']';

            const nameInput = makeField(
                'Nama Lengkap',
                prefix + '[name]',
                data.name,
                255
            );
            nameInput.dataset.name = '1';

            const identityInput = makeField(
                'Nomor Identitas (KTP/Paspor/KK)',
                prefix + '[identity_number]',
                data.identity_number,
                50
            );
            identityInput.dataset.identity = '1';

            grid.appendChild(nameInput.parentElement);
            grid.appendChild(identityInput.parentElement);

            card.appendChild(grid);

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = prefix + '[age_category]';
            hidden.value = age;

            card.appendChild(hidden);
            visitorContainer.appendChild(card);
        });

        bookerCheckbox.disabled = adults === 0;

        if (adults === 0) {
            bookerCheckbox.checked = false;
        }

        syncBooker();

        const total = adults + children;

        visitorInfo.textContent = total > 4
            ? 'Maksimal 4 pengunjung per transaksi.'
            : 'Total pengunjung: ' + total +
              '. Jumlah identitas harus sesuai jumlah tiket.';
    }

    adultInput.addEventListener('change', renderVisitors);
    childInput.addEventListener('change', renderVisitors);

    bookerCheckbox.addEventListener('change', function () {
        syncBooker();
    });

    bookerName.addEventListener('input', syncBooker);
    bookerIdentity.addEventListener('input', syncBooker);

    renderVisitors();
});
</script>
@endpush
