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
