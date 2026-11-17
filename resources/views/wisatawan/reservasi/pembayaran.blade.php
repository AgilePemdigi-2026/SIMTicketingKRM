@extends('layouts.app')

@section('title', 'Reservasi - Pembayaran QRIS')

@push('styles')
<style>
    .rv-payment-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
        align-items: stretch;
    }

    @media (min-width: 1024px) {
        .rv-payment-layout {
            grid-template-columns:
                minmax(0, 2.2fr) minmax(0, 1fr);
        }
    }

    .rv-payment-layout > * {
        min-width: 0;
    }

    .rv-payment-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 8px 0;
        font-size: 13px;
    }

    .rv-payment-row span:last-child {
        font-weight: 600;
        color: #193B31;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .rv-qr-placeholder {
        width: 194px;
        height: 194px;
        max-width: 100%;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        border: 1px dashed #A8BDB1;
        background: #F7FAF7;
        color: #557066;
        text-align: center;
        padding: 16px;
    }

    .rv-qr-icon {
        width: 48px;
        height: 48px;
        border: 3px solid #07513D;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px;
        padding: 5px;
    }

    .rv-qr-icon span {
        background: #07513D;
    }
</style>
@endpush

@section('content')
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
        ->translatedFormat('d M Y');

    $waktuMulai = substr($sesi->jam_mulai, 0, 5);
    $waktuSelesai = substr($sesi->jam_selesai, 0, 5);

    $totalBiaya = (float) $sesi->harga_sesi;
@endphp

<div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

    {{-- =====================================
         PROGRESS RESERVASI
    ====================================== --}}
    <div class="mx-auto mb-14 max-w-3xl">

        <div class="relative flex items-start justify-between">

            <div class="absolute left-[10%] right-[10%] top-4 h-px bg-gray-200"></div>

            @foreach ($langkah as $index => $nama)

                <div class="relative z-10 flex flex-1 flex-col items-center gap-2 text-center">

                    <div
                        class="flex h-8 w-8 items-center justify-center border text-sm font-semibold
                        {{ $index <= 3
                            ? 'border-[#07513D] bg-[#07513D] text-white'
                            : 'border-gray-200 bg-[#FBFCF8] text-gray-400' }}"
                        @if ($index === 3)
                            aria-current="step"
                        @endif
                    >
                        {{ $index < 3 ? '✓' : $index + 1 }}
                    </div>

                    <span
                        class="text-[10px] sm:text-xs
                        {{ $index <= 3
                            ? 'font-semibold text-[#07513D]'
                            : 'text-gray-400' }}"
                    >
                        {{ $nama }}
                    </span>

                </div>

            @endforeach

        </div>
    </div>

    {{-- =====================================
         JUDUL
    ====================================== --}}
    <div class="mb-8">

        <h1 class="text-2xl font-bold text-[#193B31]">
            Pembayaran QRIS
        </h1>

        <div class="mt-2 h-1 w-56 bg-[#D9E8BE]"></div>

    </div>

    {{-- Pemberitahuan mode pratinjau --}}
    <div class="mb-6 border border-[#D6E0D9] bg-[#F0F7F0] px-4 py-3 text-sm text-[#193B31]" role="status">
        <strong>Pratinjau frontend.</strong>
        Belum ada reservasi atau transaksi pembayaran yang dibuat.
        QRIS akan tersedia setelah integrasi backend.
    </div>

    {{-- =====================================
         PRATINJAU INVOICE DAN QRIS
    ====================================== --}}
    <div class="rv-payment-layout">

        {{-- KOLOM KIRI --}}
        <section class="border border-gray-200 bg-white p-5 sm:p-6">

            <h2 class="mb-5 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                Pratinjau Invoice
            </h2>

            <div class="rv-payment-row">
                <span class="text-gray-500">No. Invoice</span>
                <span>Belum diterbitkan</span>
            </div>

            <div class="rv-payment-row">
                <span class="text-gray-500">Fasilitas</span>
                <span>{{ $fasilitas->nama_fasilitas }}</span>
            </div>

            <div class="rv-payment-row">
                <span class="text-gray-500">Tanggal</span>
                <span>{{ $tanggalFormatted }}</span>
            </div>

            <div class="rv-payment-row">
                <span class="text-gray-500">Waktu</span>
                <span>
                    {{ $waktuMulai }}–{{ $waktuSelesai }} WIB
                </span>
            </div>

            <div class="rv-payment-row">
                <span class="text-gray-500">Pemesan</span>
                <span>Belum disimpan</span>
            </div>

            <div class="rv-payment-row mt-2 border-t border-[#8FA99B] pt-4">

                <span class="font-bold text-[#193B31]">
                    Total
                </span>

                <span class="text-base font-bold text-[#07513D]">
                    Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                </span>

            </div>

            <p class="mt-5 text-xs leading-relaxed text-gray-500">
                Nominal ini merupakan harga sesi fasilitas dari
                master data. Total final akan dikonfirmasi oleh
                backend reservasi sebelum pembayaran.
            </p>

        </section>

        {{-- =====================================
             KOLOM KANAN - QRIS
        ====================================== --}}
        <aside class="border border-gray-200 bg-white p-5 text-center sm:p-6">

            <h2 class="mb-6 text-sm font-bold uppercase text-[#193B31]">
                Scan untuk Membayar
            </h2>

            {{-- Placeholder, bukan QRIS pembayaran --}}
            <div class="rv-qr-placeholder">

                <div class="rv-qr-icon" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <p class="text-xs font-semibold">
                    QRIS Belum Tersedia
                </p>

            </div>

            <div class="mt-6">
                <p class="mb-2 text-xs text-gray-500">
                    Batas waktu pembayaran
                </p>

                <p class="text-2xl font-bold text-[#193B31]">
                    --:--
                </p>
            </div>

            <p class="mt-4 text-xs leading-relaxed text-gray-500">
                Pembayaran reservasi hanya melalui QRIS.
                Batas waktu akan ditampilkan setelah
                transaksi pembayaran berhasil dibuat.
            </p>

            <div class="mt-5 border-t border-gray-200 pt-4">
                <p class="text-xs text-gray-500">
                    Status Pembayaran
                </p>

                <p class="mt-1 text-sm font-semibold text-[#193B31]">
                    Belum Dibuat
                </p>
            </div>

        </aside>

    </div>

    {{-- =====================================
         NAVIGASI
    ====================================== --}}
    <div class="mt-6 flex flex-wrap items-center justify-end gap-3">

        <a
            href="{{ route('wisatawan.reservasi.data', [
                'fasilitas' => $fasilitas->id_fasilitas,
                'tanggal' => $tanggal,
                'sesi' => $sesi->id_sesi,
            ]) }}"
            class="border border-[#07513D] px-6 py-3 text-sm font-semibold text-[#07513D] hover:bg-[#F0F7F0]"
        >
            ← Kembali
        </a>

        <button
            type="button"
            disabled
            title="Menunggu integrasi backend pembayaran reservasi"
            class="cursor-not-allowed bg-[#07513D] px-6 py-3 text-sm font-semibold text-white opacity-50"
        >
            Pembayaran Selesai — Lihat Invoice
        </button>

    </div>

    <p class="mt-3 text-right text-xs text-gray-500">
        Tombol invoice akan aktif hanya setelah pembayaran
        benar-benar dinyatakan lunas oleh sistem.
    </p>

</div>

{{-- Akses pengujian tampilan invoice --}}
<div class="mt-4 text-right">

    <a
        href="{{ route('wisatawan.reservasi.invoice.preview', [
            'fasilitas' => $fasilitas->id_fasilitas,
            'tanggal' => $tanggal,
            'sesi' => $sesi->id_sesi,
        ]) }}"
        class="text-sm font-semibold text-[#07513D] underline underline-offset-4"
    >
        Lihat Pratinjau Desain Invoice
    </a>

    <p class="mt-1 text-xs text-gray-500">
        Hanya untuk pengujian frontend, bukan bukti pembayaran.
    </p>

</div>

@endsection
