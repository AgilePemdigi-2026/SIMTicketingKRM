@extends('layouts.app')

@section('title', 'Reservasi - Pratinjau Invoice')

@push('styles')
<style>
    .rv-invoice-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .rv-invoice-card {
        background: white;
        border: 1px solid #D6E0D9;
        padding: 30px;
    }

    .rv-invoice-heading {
        border-bottom: 1px solid #D6E0D9;
        padding-bottom: 20px;
        margin-bottom: 25px;
    }

    .rv-invoice-details {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .rv-invoice-label {
        font-size: 12px;
        color: #718078;
        margin-bottom: 6px;
    }

    .rv-invoice-value {
        font-size: 14px;
        font-weight: 600;
        color: #193B31;
        overflow-wrap: anywhere;
    }

    .rv-invoice-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        border-top: 1px solid #D6E0D9;
        margin-top: 28px;
        padding-top: 22px;
    }

    @media (max-width: 640px) {
        .rv-invoice-card {
            padding: 20px;
        }

        .rv-invoice-details {
            grid-template-columns: 1fr;
            gap: 18px;
        }
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

    $isFinal = isset($reservasi);

    $tanggalFormatted = \Carbon\Carbon::parse(
        $isFinal ? $reservasi->visit_date : $tanggal
    )->locale('id')->translatedFormat('d F Y');

    $waktuMulai = substr(
        (string) ($isFinal ? $reservasi->start_time : $sesi->jam_mulai),
        0,
        5
    );

    $waktuSelesai = substr(
        (string) ($isFinal ? $reservasi->end_time : $sesi->jam_selesai),
        0,
        5
    );

    $totalBiaya = (float) (
        $isFinal ? $reservasi->total_amount : $sesi->harga_sesi
    );

@endphp

<div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

    {{-- Progress Reservasi --}}
    <div class="mx-auto mb-14 max-w-3xl">

        <div class="relative flex items-start justify-between">

            <div class="absolute left-[10%] right-[10%] top-4 h-px bg-gray-200"></div>

            @foreach ($langkah as $index => $nama)

                <div class="relative z-10 flex flex-1 flex-col items-center gap-2 text-center">

                    <div
                        class="flex h-8 w-8 items-center justify-center border text-sm font-semibold
                        {{ $index <= 4
                            ? 'border-[#07513D] bg-[#07513D] text-white'
                            : 'border-gray-200 bg-[#FBFCF8] text-gray-400' }}"
                        @if ($index === 4) aria-current="step" @endif
                    >
                        {{ $index < 4 ? '✓' : $index + 1 }}
                    </div>

                    <span class="text-[10px] sm:text-xs
                        {{ $index <= 4
                            ? 'font-semibold text-[#07513D]'
                            : 'text-gray-400' }}">
                        {{ $nama }}
                    </span>
                </div>

            @endforeach

        </div>
    </div>

    <div class="rv-invoice-container">

        {{-- Judul --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-[#193B31]">
                {{ $isFinal ? 'Invoice Reservasi' : 'Pratinjau Invoice' }}
            </h1>

            <div class="mt-2 h-1 w-44 bg-[#D9E8BE]"></div>
        </div>

        {{-- Pemberitahuan --}}
        <div class="mb-6 border border-[#D6E0D9] bg-[#F0F7F0] p-4 text-sm text-[#193B31]">
            @if ($isFinal)
                <strong>Pembayaran Lunas.</strong>
                Invoice ini diterbitkan berdasarkan data reservasi
                dan pembayaran yang telah tercatat dalam sistem.
            @else
                <strong>Mode Pratinjau.</strong>
                Dokumen ini hanya contoh tampilan.
                Reservasi dan pembayaran belum diproses.
            @endif
        </div>

        {{-- Kartu Invoice --}}
        <section class="rv-invoice-card">

            {{-- Header --}}
            <div class="rv-invoice-heading">

                <div class="flex flex-wrap items-start justify-between gap-4">

                    <div>
                        <h2 class="text-lg font-bold text-[#193B31]">
                            Invoice Reservasi Fasilitas
                        </h2>

                        <p class="mt-2 text-xs text-gray-500">
                            Kebun Raya Mangrove Surabaya
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-xs text-gray-500">
                            Status Pembayaran
                        </p>

                        <p class="mt-1 text-sm font-bold text-[#193B31]">
                            {{ $isFinal && $reservasi->payment?->status === 'paid'
                                ? 'Lunas'
                                : 'Belum Ada Transaksi' }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- Identitas Invoice --}}
            <div class="mb-7 border-b border-gray-200 pb-6">

                <div class="rv-invoice-details">

                    <div>
                        <p class="rv-invoice-label">
                            Nomor Invoice
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->invoice_number : 'Belum diterbitkan' }}
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Tanggal Penerbitan
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB' : 'Belum diterbitkan' }}
                        </p>
                    </div>

                </div>

            </div>

            {{-- Data Pemesan --}}
            <div class="mb-7">

                <h3 class="mb-5 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                    Informasi Pemesan
                </h3>

                <div class="rv-invoice-details">

                    <div>
                        <p class="rv-invoice-label">
                            Nama Pemesan
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->customer_name : 'Belum tersimpan' }}
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Nomor Identitas
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->identity_number : 'Belum tersimpan' }}
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Nomor Telepon
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->phone : 'Belum tersimpan' }}
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Email
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->email : 'Belum tersimpan' }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- Detail Reservasi --}}
            <div class="border-t border-gray-200 pt-6">

                <h3 class="mb-5 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                    Detail Reservasi
                </h3>

                <div class="rv-invoice-details">

                    <div>
                        <p class="rv-invoice-label">
                            Fasilitas
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->facility_name : $fasilitas->nama_fasilitas }}
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Tanggal Penggunaan
                        </p>
                        <p class="rv-invoice-value">
                            {{ $tanggalFormatted }}
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Sesi Penggunaan
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->session_name : $sesi->nama_sesi }}
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Waktu Penggunaan
                        </p>
                        <p class="rv-invoice-value">
                            {{ $waktuMulai }}–{{ $waktuSelesai }} WIB
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Durasi
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->duration_minutes : $sesi->durasi_menit }} menit
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Jumlah Peserta
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->participant_count . ' peserta' : 'Belum tersimpan' }}
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Keperluan Kegiatan
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? $reservasi->purpose : 'Belum tersimpan' }}
                        </p>
                    </div>

                    <div>
                        <p class="rv-invoice-label">
                            Metode Pembayaran
                        </p>
                        <p class="rv-invoice-value">
                            {{ $isFinal ? 'QRIS (Midtrans Sandbox)' : 'QRIS (Direncanakan)' }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- Total --}}
            <div class="rv-invoice-total">

                <div>
                    <p class="text-sm font-semibold text-[#193B31]">
                        Total Biaya Reservasi
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $reservasi->participant_count }} peserta ×
                        Rp {{ number_format((float) $reservasi->unit_price, 0, ',', '.') }}
                        per peserta
                    </p>
                </div>

                <p class="text-xl font-bold text-[#07513D]">
                    Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                </p>

            </div>

            {{-- Catatan --}}
            <div class="mt-7 bg-[#F4F7F2] p-4">

                <p class="text-xs leading-relaxed text-[#557066]">
                    @if ($isFinal)
                        Pembayaran reservasi telah terverifikasi Lunas.
                        Persetujuan administratif reservasi tetap mengikuti
                        proses pengelolaan oleh Admin.
                    @else
                        Dokumen ini bukan bukti pembayaran atau konfirmasi
                        reservasi. Invoice final hanya tersedia setelah
                        pembayaran diverifikasi oleh sistem.
                    @endif
                </p>

            </div>

        </section>

        {{-- Tombol --}}
        <div class="mt-6 flex flex-wrap justify-end gap-3">

            <a
                href="{{ route('wisatawan.reservasi.pembayaran.preview', [
                    'fasilitas' => $fasilitas->id_fasilitas,
                    'tanggal' => $tanggal,
                    'sesi' => $sesi->id_sesi,
                ]) }}"
                class="border border-[#07513D] px-6 py-3 text-sm font-semibold text-[#07513D] hover:bg-[#F0F7F0]"
            >
                ← Kembali
            </a>

            @if ($isFinal)
                <a
                    href="{{ route('facility-reservation.payment.invoice.pdf', [
                        'code' => $reservasi->code
                    ]) }}"
                    class="inline-flex items-center justify-center bg-[#07513D] px-6 py-3 text-sm font-semibold text-white hover:bg-[#064332]"
                >
                    Unduh Invoice PDF
                </a>
            @else
                <button
                    type="button"
                    disabled
                    class="cursor-not-allowed bg-[#07513D] px-6 py-3 text-sm font-semibold text-white opacity-50"
                >
                    Unduh Invoice PDF
                </button>
            @endif

            <a
                href="{{ route('wisatawan.beranda') }}"
                class="bg-[#07513D] px-6 py-3 text-sm font-semibold text-white hover:bg-[#063A2B]"
            >
                Kembali ke Beranda
            </a>

        </div>

    </div>

</div>
@endsection
