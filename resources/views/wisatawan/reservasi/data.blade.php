@extends('layouts.app')

@section('title', 'Reservasi - Data Reservasi')

@push('styles')
<style>
    .reservasi-data-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .reservasi-data-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .reservasi-input {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #D6E0D9;
        background: white;
        color: #193B31;
        font-size: 13px;
        border-radius: 0;
    }

    .reservasi-input:focus {
        outline: 2px solid #07513D;
        outline-offset: 1px;
    }

    .reservasi-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 500;
        color: #193B31;
    }

    .reservasi-invoice-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 7px 0;
        font-size: 13px;
    }

    .reservasi-invoice-row span:last-child {
        text-align: right;
        font-weight: 600;
    }

    @media (max-width: 640px) {
        .reservasi-data-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="reservasi-data-container px-4 py-10 sm:px-6">

    @php
        $langkah = [
            'Pilih Fasilitas',
            'Pilih Tanggal',
            'Data Reservasi',
            'Pembayaran',
            'Invoice',
        ];

        $tanggalFormatted = \Carbon\Carbon::parse($tanggal)
            ->locale('id')
            ->translatedFormat('d F Y');

        $waktuMulai = substr($sesi->jam_mulai, 0, 5);
        $waktuSelesai = substr($sesi->jam_selesai, 0, 5);

        $totalBiaya = (float) $sesi->harga_sesi;
    @endphp

    {{-- Progress Reservasi --}}
    <div class="mx-auto mb-14 max-w-3xl">
        <div class="relative flex items-start justify-between">

            <div class="absolute left-[10%] right-[10%] top-4 h-px bg-gray-200"></div>

            @foreach ($langkah as $index => $nama)
                <div class="relative z-10 flex flex-1 flex-col items-center gap-2 text-center">

                    <div class="flex h-8 w-8 items-center justify-center border text-sm font-semibold
                        {{ $index <= 2
                            ? 'border-[#07513D] bg-[#07513D] text-white'
                            : 'border-gray-200 bg-[#FBFCF8] text-gray-400' }}"
                        @if ($index === 2) aria-current="step" @endif
                    >
                        {{ $index < 2 ? '✓' : $index + 1 }}
                    </div>

                    <span class="text-[10px] sm:text-xs
                        {{ $index <= 2
                            ? 'font-semibold text-[#07513D]'
                            : 'text-gray-400' }}">
                        {{ $nama }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Judul --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#193B31]">
            Data Reservasi
        </h1>

        <div class="mt-2 h-1 w-36 bg-[#D9E8BE]"></div>
    </div>

    {{-- Informasi Pemesan --}}
    <section class="mb-5 border border-gray-200 bg-white p-5 sm:p-6">

        <h2 class="mb-6 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
            Informasi Pemesan
        </h2>

        {{-- Form frontend, belum dikirim ke backend --}}
        <form id="formReservasi" method="POST" action="">
            @csrf

            <div class="reservasi-data-grid">

                <div class="reservasi-field">
                    <label for="nama_pemesan">
                        Nama Pemesan / Penanggung Jawab *
                    </label>
                    <input
                        id="nama_pemesan"
                        name="nama_pemesan"
                        type="text"
                        class="reservasi-input"
                        placeholder="Masukkan nama lengkap"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="reservasi-field">
                    <label for="nomor_identitas">
                        No. Identitas KTP / NPWP *
                    </label>
                    <input
                        id="nomor_identitas"
                        name="nomor_identitas"
                        type="text"
                        class="reservasi-input"
                        placeholder="Masukkan nomor identitas"
                        maxlength="32"
                        required
                    >
                </div>

                <div class="reservasi-field">
                    <label for="nomor_telepon">
                        No. Telepon *
                    </label>
                    <input
                        id="nomor_telepon"
                        name="nomor_telepon"
                        type="tel"
                        class="reservasi-input"
                        placeholder="08xxxxxxxxxx"
                        maxlength="20"
                        required
                    >
                </div>

                <div class="reservasi-field">
                    <label for="email">
                        Email *
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="reservasi-input"
                        placeholder="nama@email.com"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="reservasi-field" style="grid-column: 1 / -1">
                    <label for="keperluan">
                        Keperluan Kegiatan *
                    </label>
                    <input
                        id="keperluan"
                        name="keperluan"
                        type="text"
                        class="reservasi-input"
                        placeholder="Contoh: Seminar, pernikahan, kegiatan komunitas"
                        maxlength="200"
                        required
                    >
                </div>

                <div class="reservasi-field">
                    <label for="jumlah_peserta">
                        Jumlah Peserta / Pengunjung *
                    </label>
                    <input
                        id="jumlah_peserta"
                        name="jumlah_peserta"
                        type="number"
                        min="1"
                        max="{{ $fasilitas->kapasitas_fasilitas }}"
                        class="reservasi-input"
                        placeholder="Jumlah peserta"
                        required
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Maksimal {{ $fasilitas->kapasitas_fasilitas }} peserta
                    </p>
                </div>

                <div class="reservasi-field">
                    <label for="durasi">
                        Durasi Penggunaan
                    </label>
                    <input
                        id="durasi"
                        type="text"
                        class="reservasi-input bg-gray-50"
                        value="{{ $sesi->durasi_menit }} menit ({{ $sesi->nama_sesi }})"
                        readonly
                    >
                </div>

                <div class="reservasi-field" style="grid-column: 1 / -1">
                    <label for="informasi_tambahan">
                        Informasi Tambahan
                    </label>
                    <textarea
                        id="informasi_tambahan"
                        name="informasi_tambahan"
                        class="reservasi-input"
                        rows="3"
                        maxlength="1000"
                        placeholder="Kebutuhan teknis, dekorasi, dan lain-lain"
                    ></textarea>
                </div>

            </div>
        </form>
    </section>

    {{-- Pratinjau Invoice --}}
    <section class="border border-gray-200 bg-white p-5 sm:p-6">

        <h2 class="mb-5 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
            Pratinjau Invoice
        </h2>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">No. Invoice</span>
            <span>Belum diterbitkan</span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Fasilitas</span>
            <span>{{ $fasilitas->nama_fasilitas }}</span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Tanggal</span>
            <span>{{ $tanggalFormatted }}</span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Waktu</span>
            <span>
                {{ $waktuMulai }}–{{ $waktuSelesai }} WIB
            </span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Jumlah Peserta</span>
            <span id="invoicePeserta">Belum diisi</span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Keperluan</span>
            <span id="invoiceKeperluan">Belum diisi</span>
        </div>

        <div class="reservasi-invoice-row border-t border-gray-200 pt-4">
            <span class="font-bold text-[#193B31]">
                Total Pembayaran
            </span>
            <span class="font-bold text-[#07513D]">
                Rp {{ number_format($totalBiaya, 0, ',', '.') }}
            </span>
        </div>

        <p class="mt-4 text-xs text-gray-500">
            Pratinjau ini bukan invoice final.
            Nomor invoice dan status pembayaran akan diterbitkan
            setelah proses reservasi terintegrasi.
        </p>
    </section>

    {{-- Navigasi --}}
    <div class="mt-6 flex justify-end gap-3">

        <a
            href="{{ route('wisatawan.reservasi.tanggal', [
                'fasilitas' => $fasilitas->id_fasilitas,
                'tanggal' => $tanggal,
                'sesi' => $sesi->id_sesi,
            ]) }}"
            class="border border-[#07513D] px-6 py-3 text-sm font-semibold text-[#07513D]"
        >
            ← Kembali
        </a>

        <button
            type="button"
            id="lanjutPembayaran"
            class="bg-[#07513D] px-6 py-3 text-sm font-semibold text-white hover:bg-[#063A2B]"
        >
            Lanjut ke Pembayaran →
        </button>
    </div>

    <p
        id="pesanForm"
        role="status"
        aria-live="polite"
        class="mt-3 text-right text-xs text-[#557066]"
    ></p>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formReservasi');
    const peserta = document.getElementById('jumlah_peserta');
    const keperluan = document.getElementById('keperluan');
    const pesanForm = document.getElementById('pesanForm');

    // Hindari submit sebelum backend reservasi tersedia.
    form.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    peserta.addEventListener('input', function () {
        document.getElementById('invoicePeserta').textContent =
            peserta.value || 'Belum diisi';
    });

    keperluan.addEventListener('input', function () {
        document.getElementById('invoiceKeperluan').textContent =
            keperluan.value || 'Belum diisi';
    });

    document.getElementById('lanjutPembayaran')
        .addEventListener('click', function () {

            // Periksa kelengkapan form
            if (!form.reportValidity()) {
                return;
            }

            // Arahkan ke halaman pratinjau pembayaran
            const tujuan = new URL(
                @json(route('wisatawan.reservasi.pembayaran.preview', $fasilitas->id_fasilitas)),
                window.location.origin
            );

            // Bawa tanggal dan sesi yang telah dipilih
            tujuan.searchParams.set('tanggal', @json($tanggal));
            tujuan.searchParams.set('sesi', @json($sesi->id_sesi));

            window.location.assign(tujuan.toString());
        });
});
</script>
@endpush
