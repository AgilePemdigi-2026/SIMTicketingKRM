@extends('layouts.admin')

@section('title', 'Dashboard Admin - Kebun Raya Mangrove')

@section('content')
<div class="mx-auto max-w-[1440px]">

    {{-- Header --}}
    <div class="mb-8 border-b border-[#D4DED7] pb-5">
        <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[#628371]">
            Administrasi Kebun Raya Mangrove
        </p>

        <h1 class="text-2xl font-semibold text-[#143E30]">
            Dashboard Admin
        </h1>

        <p class="mt-2 text-sm text-[#799085]">
            Selamat datang di Portal Administrasi Kebun Raya Mangrove Surabaya.
        </p>
    </div>

    {{-- Informasi --}}
    <div class="mb-8 rounded-sm border border-[#D8E6DC] bg-white p-6">
        <h2 class="text-lg font-semibold text-[#173E30]">
            Selamat Datang!
        </h2>

        <p class="mt-2 text-sm leading-7 text-[#667E70]">
            Gunakan menu navigasi di samping untuk mengelola
            tiket, wahana, dan fasilitas Kebun Raya Mangrove Surabaya.
        </p>
    </div>

    {{-- Monitoring Operasional Harian --}}
    <div class="mb-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-[#173E30]">
                    Monitoring Operasional Harian
                </h2>
                <p class="mt-1 text-sm text-[#74877B]">
                    Ringkasan aktivitas operasional hari ini.
                </p>
            </div>

            <span class="rounded-md bg-[#E9F4EC] px-3 py-2 text-xs font-medium text-[#135A3F]">
                {{ now('Asia/Jakarta')->translatedFormat('d F Y') }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            {{-- Transaksi Tiket --}}
            <div class="rounded-lg border border-[#D9E4DC] bg-white p-5">
                <p class="text-sm text-[#74877B]">Transaksi Tiket</p>
                <p class="mt-3 text-3xl font-bold text-[#173E30]">
                    {{ number_format($stats['transaksi_tiket']) }}
                </p>
                <p class="mt-2 text-xs text-[#74877B]">
                    Pesanan tiket dibuat hari ini
                </p>
            </div>

            {{-- Pembayaran Lunas --}}
            <div class="rounded-lg border border-[#D9E4DC] bg-white p-5">
                <p class="text-sm text-[#74877B]">Pembayaran Lunas</p>
                <p class="mt-3 text-3xl font-bold text-[#173E30]">
                    {{ number_format($stats['pembayaran_lunas']) }}
                </p>
                <p class="mt-2 text-xs text-[#74877B]">
                    Pembayaran dikonfirmasi hari ini
                </p>
            </div>

            {{-- Pendapatan Tiket --}}
            <div class="rounded-lg border border-[#D9E4DC] bg-white p-5">
                <p class="text-sm text-[#74877B]">Pendapatan Tiket</p>
                <p class="mt-3 text-3xl font-bold text-[#173E30]">
                    Rp {{ number_format($stats['pendapatan_tiket'], 0, ',', '.') }}
                </p>
                <p class="mt-2 text-xs text-[#74877B]">
                    Total pembayaran lunas hari ini
                </p>
            </div>

            {{-- Pengunjung Check-in --}}
            <div class="rounded-lg border border-[#D9E4DC] bg-white p-5">
                <p class="text-sm text-[#74877B]">Pengunjung Check-in</p>
                <p class="mt-3 text-3xl font-bold text-[#173E30]">
                    {{ number_format($stats['pengunjung_checkin']) }}
                </p>
                <p class="mt-2 text-xs text-[#74877B]">
                    E-Tiket digunakan hari ini
                </p>
            </div>

            {{-- Reservasi Fasilitas --}}
            <div class="rounded-lg border border-[#D9E4DC] bg-white p-5">
                <p class="text-sm text-[#74877B]">Reservasi Fasilitas</p>
                <p class="mt-3 text-3xl font-bold text-[#173E30]">
                    {{ number_format($stats['reservasi_hari_ini']) }}
                </p>
                <p class="mt-2 text-xs text-[#74877B]">
                    Reservasi terjadwal hari ini, semua status
                </p>
            </div>

        </div>
    </div>


    {{-- Akses cepat --}}
    <h2 class="mb-4 text-sm font-bold uppercase tracking-[0.10em] text-[#173E30]">
        Akses Cepat Manajemen
    </h2>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

        {{-- Tiket --}}
        <div class="flex flex-col border border-[#D9E4DC] bg-white p-6">
            <div class="mb-4 flex h-11 w-11 items-center justify-center bg-[#E9F4EC] text-2xl text-[#135A3F]">
                🎫
            </div>

            <h3 class="text-lg font-semibold text-[#173E30]">
                Manajemen Tiket
            </h3>

            <p class="mt-2 flex-1 text-sm leading-6 text-[#74877B]">
                Kelola daftar tiket, kategori wisatawan,
                harga, dan status tiket.
            </p>

            <a href="{{ route('admin.daftar-tiket.index') }}"
               class="mt-6 inline-flex justify-center bg-[#075039] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#237052]">
                Kelola Tiket
            </a>
        </div>

        {{-- Wahana --}}
        <div class="flex flex-col border border-[#D9E4DC] bg-white p-6">
            <div class="mb-4 flex h-11 w-11 items-center justify-center bg-[#E9F4EC] text-2xl text-[#135A3F]">
                🚲
            </div>

            <h3 class="text-lg font-semibold text-[#173E30]">
                Manajemen Wahana
            </h3>

            <p class="mt-2 flex-1 text-sm leading-6 text-[#74877B]">
                Kelola informasi wahana, foto, kapasitas,
                jam operasional, dan status wahana.
            </p>

            <a href="{{ route('admin.wahana.index') }}"
               class="mt-6 inline-flex justify-center bg-[#075039] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#237052]">
                Kelola Wahana
            </a>
        </div>

        {{-- Fasilitas --}}
        <div class="flex flex-col border border-[#D9E4DC] bg-white p-6">
            <div class="mb-4 flex h-11 w-11 items-center justify-center bg-[#E9F4EC] text-2xl text-[#135A3F]">
                🏢
            </div>

            <h3 class="text-lg font-semibold text-[#173E30]">
                Manajemen Fasilitas
            </h3>

            <p class="mt-2 flex-1 text-sm leading-6 text-[#74877B]">
                Kelola fasilitas, foto, kapasitas,
                jadwal sesi, dan harga sewa.
            </p>

            <a href="{{ route('admin.fasilitas.index') }}"
               class="mt-6 inline-flex justify-center bg-[#075039] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#237052]">
                Kelola Fasilitas
            </a>
        </div>

    </div>

</div>
@endsection
