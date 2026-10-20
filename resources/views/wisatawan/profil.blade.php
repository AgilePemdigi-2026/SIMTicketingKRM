@extends('layouts.app')

@section('title', 'Profil Wisatawan | Kebun Raya Mangrove Surabaya')

@section('content')
    @php
        // Avatar sederhana tanpa perlu kolom avatar baru di tabel users.
        $initials = collect(preg_split('/\s+/', trim((string) $user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        $initials = $initials ?: 'W';
        $type = strtolower((string) $user->visitor_type);
        $visitorLabel = match ($type) {
            'domestik' => 'Domestik (WNI)',
            'mancanegara' => 'Mancanegara (WNA)',
            default => $user->visitor_type ?: '-',
        };
    @endphp

    <div class="mx-auto w-full max-w-[1180px] px-4 pb-16 pt-12 sm:px-6 sm:pb-20 sm:pt-16 lg:px-8 lg:pt-20">
        <div class="mb-8 sm:mb-9">
            <p class="text-xs font-bold tracking-[0.18em] text-[#2F795B]">AKUN WISATAWAN</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-[#193B31] sm:text-[36px]">Profil Saya</h1>
            <div class="mt-2 h-[3px] w-36 bg-[#A1C977]" aria-hidden="true"></div>
        </div>

        @if (session('success'))
            <div role="status" class="mb-6 border border-[#AFD4B8] bg-[#E7F3E9] px-5 py-4 text-sm font-medium text-[#175A32]">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid items-stretch gap-6 lg:grid-cols-[310px_minmax(0,1fr)] lg:gap-7">
            {{-- Kartu profil sebelah kiri --}}
            <aside class="border-t-4 border-[#064631] bg-[#EEF4EE] px-6 py-9 text-center sm:px-8">
                <div class="mx-auto flex h-[92px] w-[92px] items-center justify-center rounded-full bg-[#2F795B] text-3xl font-semibold text-white" aria-label="Avatar inisial {{ $initials }}">
                    {{ $initials }}
                </div>

                <h2 class="mt-4 break-words text-xl font-medium text-[#193B31]">{{ $user->name }}</h2>
                <p class="mt-2 text-sm text-[#718079]">Wisatawan {{ $visitorLabel }}</p>

                {{-- Ini menunjukkan bahwa pengguna sedang login, bukan status_akun dari database. --}}
                <span class="mt-3 inline-block bg-[#D9EBD9] px-3 py-1.5 text-xs font-semibold text-[#296542]">
                    Sedang Login
                </span>

                <div class="mt-5 flex flex-col gap-2.5">
                    <a href="{{ route('wisatawan.profil.edit') }}"
                       class="flex min-h-12 items-center justify-center border border-[#064631] px-4 py-3 text-sm font-semibold text-[#064631] transition hover:bg-[#DCEBDD] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#064631]">
                        Edit Profil
                    </a>
                    <a href="{{ route('wisatawan.password.edit') }}"
                       class="flex min-h-12 items-center justify-center border border-[#064631] px-4 py-3 text-sm font-semibold text-[#064631] transition hover:bg-[#DCEBDD] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#064631]">
                        Ubah Password
                    </a>

                    {{-- Modul riwayat pesanan belum dibuat; jangan mengarah ke URL palsu. --}}
                    <button type="button" disabled title="Fitur riwayat pesanan sedang dikembangkan"
                            class="min-h-12 cursor-not-allowed border border-[#AFC1B4] px-4 py-3 text-sm font-semibold text-[#698376] opacity-75">
                        Riwayat Pesanan (Segera)
                    </button>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="min-h-12 w-full bg-[#064631] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#2F795B] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#064631]">
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Kartu informasi sebelah kanan --}}
            <section class="border border-[#DAE3DD] bg-white px-5 py-7 sm:px-8 sm:py-8" aria-labelledby="informasi-akun-title">
                <h2 id="informasi-akun-title" class="text-sm font-bold tracking-[0.1em] text-[#193B31]">
                    INFORMASI AKUN
                </h2>
                <div class="mt-5 h-px bg-[#DAE3DD]" aria-hidden="true"></div>

                <dl class="mt-5 grid grid-cols-1 gap-x-12 gap-y-1 sm:grid-cols-2">
                    <div class="border-b border-[#E6ECE7] py-4">
                        <dt class="text-sm text-[#718079]">Nama Lengkap</dt>
                        <dd class="mt-2 break-words text-[15px] font-semibold text-[#193B31]">{{ $user->name }}</dd>
                    </div>
                    <div class="border-b border-[#E6ECE7] py-4">
                        <dt class="text-sm text-[#718079]">No. Identitas</dt>
                        <dd class="mt-2 break-all text-[15px] font-semibold text-[#193B31]">{{ $user->identity_number ?? '-' }}</dd>
                    </div>
                    <div class="border-b border-[#E6ECE7] py-4">
                        <dt class="text-sm text-[#718079]">No. Telepon</dt>
                        <dd class="mt-2 break-words text-[15px] font-semibold text-[#193B31]">{{ $user->phone ?? '-' }}</dd>
                    </div>
                    <div class="border-b border-[#E6ECE7] py-4">
                        <dt class="text-sm text-[#718079]">Email</dt>
                        <dd class="mt-2 break-all text-[15px] font-semibold text-[#193B31]">{{ $user->email }}</dd>
                    </div>
                    <div class="border-b border-[#E6ECE7] py-4">
                        <dt class="text-sm text-[#718079]">Jenis Wisatawan</dt>
                        <dd class="mt-2 text-[15px] font-semibold text-[#193B31]">{{ $visitorLabel }}</dd>
                    </div>
                    <div class="border-b border-[#E6ECE7] py-4">
                        <dt class="text-sm text-[#718079]">Bergabung Sejak</dt>
                        <dd class="mt-2 text-[15px] font-semibold text-[#193B31]">
                            {{ $user->created_at?->locale('id')->translatedFormat('d F Y') ?? '-' }}
                        </dd>
                    </div>
                </dl>

                <a href="{{ route('wisatawan.profil.edit') }}"
                   class="mt-7 inline-flex min-h-12 items-center justify-center border border-[#064631] px-7 py-3 text-sm font-semibold text-[#064631] transition hover:bg-[#EEF4EE] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#064631]">
                    Edit Profil
                </a>
            </section>
        </div>
    </div>
@endsection