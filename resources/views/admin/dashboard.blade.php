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
