@extends('layouts.app')

@section('title', 'Validasi Tiket - Petugas')

@push('styles')
    @include('partials.ticket-payment-styles')
@endpush

@section('content')
<div class="purchase-page krm-container">

    <div class="purchase-heading">
        <h1>Validasi E-Tiket</h1>
        <p>Periksa keabsahan tiket pengunjung sebelum masuk.</p>
    </div>

    <section class="krm-card payment-card">
        <h2>Periksa Kode Tiket</h2>

        <p class="mb-4">
            Masukkan kode pesanan pada E-Tiket pengunjung.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 p-4 text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('petugas.validasi-tiket.verify') }}"
        >
            @csrf

            <div class="mb-4">
                <label for="code" class="mb-2 block font-semibold">
                    Kode Pesanan
                </label>

                <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code', request('code')) }}"
                    placeholder="Masukkan kode UUID pesanan"
                    required
                    class="w-full rounded border p-3"
                >
            </div>

            <button type="submit" class="krm-btn krm-btn-primary">
                Periksa Tiket
            </button>
        </form>
    </section>

    @if ($result !== null)
        <section class="krm-card payment-card mt-6">
            <h2>Hasil Pemeriksaan</h2>

            <div
                class="mt-4 rounded border p-4"
                style="{{ $result['valid']
                    ? 'background:#ecfdf5; color:#065f46;'
                    : 'background:#fef2f2; color:#991b1b;' }}"
            >
                <strong>
                    {{ $result['valid'] ? 'LOLOS PEMERIKSAAN' : 'TIDAK VALID' }}
                </strong>

                <p class="mt-2">{{ $result['message'] }}</p>
            </div>

            @if ($order !== null)
                <div class="mt-5">
                    <p><strong>Kode:</strong> {{ $order->code }}</p>
                    <p><strong>Pemesan:</strong> {{ $order->booker_name }}</p>
                    <p>
                        <strong>Tanggal Kunjungan:</strong>
                        {{ $order->visit_date->format('d/m/Y') }}
                    </p>
                    <p>
                        <strong>Total:</strong>
                        Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                    </p>
                </div>
            @endif
        </section>
    @endif

    <div class="purchase-actions">
        <a href="{{ route('petugas.dashboard') }}" class="krm-btn">
            Kembali ke Dashboard
        </a>
    </div>

</div>
@endsection
