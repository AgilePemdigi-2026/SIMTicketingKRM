@extends('layouts.app')

@section('title', 'Akses Dibatasi | Kebun Raya Mangrove Surabaya')

@section('content')
    @php
        $role = auth()->user()?->role;
        $portalUrl = match ($role) {
            'admin' => route('admin.dashboard'),
            'petugas' => route('petugas.dashboard'),
            'wisatawan' => route('wisatawan.beranda'),
            default => route('home'),
        };
        $portalLabel = match ($role) {
            'admin' => 'Buka Portal Admin',
            'petugas' => 'Buka Portal Petugas',
            'wisatawan' => 'Kembali ke Beranda Wisatawan',
            default => 'Kembali ke Beranda',
        };
    @endphp

    <div class="mx-auto flex w-full max-w-[720px] flex-col items-center px-4 py-20 text-center sm:px-6">
        <span class="inline-block border border-[#C6D8CD] bg-[#EEF4EE] px-3.5 py-1.5 text-xs font-bold tracking-[0.14em] text-[#064631]">
            AKSES DIBATASI (403)
        </span>

        <h1 class="mt-5 text-2xl font-bold text-[#193B31] sm:text-3xl">
            Halaman ini khusus untuk peran pengguna yang berbeda
        </h1>

        <p class="mt-3 max-w-[520px] text-sm leading-6 text-[#5E7369]">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki akses ke halaman ini.' }}
        </p>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ $portalUrl }}"
               class="inline-flex min-h-11 items-center justify-center bg-[#064631] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#2F795B]">
                {{ $portalLabel }}
            </a>

            <a href="{{ route('home') }}"
               class="inline-flex min-h-11 items-center justify-center border border-[#064631] px-6 py-3 text-sm font-semibold text-[#064631] transition hover:bg-[#EEF4EE]">
                Ke Beranda Utama
            </a>
        </div>
    </div>
@endsection
