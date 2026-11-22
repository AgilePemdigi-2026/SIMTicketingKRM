@extends('layouts.app')

@section('title', 'Tentang Kami - Kebun Raya Mangrove Surabaya')

@section('content')
<div class="mx-auto w-full max-w-[1180px] px-4 pb-20 pt-10 sm:px-6 lg:px-8 lg:pt-14">

    {{-- Breadcrumb menggunakan komponen bersama --}}
    <x-breadcrumb :items="[
        ['label' => 'Tentang Kami']
    ]" />

    {{-- Judul --}}
    <h1 class="krm-page-title">
        Tentang Kami
    </h1>

    {{-- Layout utama sesuai mockup --}}
    <div class="mt-7 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">

        {{-- KOLOM KIRI --}}
        <main class="min-w-0 space-y-4">

            {{-- Foto utama --}}
            <img
                src="{{ $tentang['gambar'] }}"
                alt="Kawasan {{ $tentang['nama'] }}"
                class="h-[250px] w-full object-cover sm:h-[350px]"
                fetchpriority="high"
            >

            {{-- Profil BRIDA --}}
            <section class="border border-[#D6E1DA] bg-white p-5 sm:p-6">
                <h2 class="text-xs font-bold uppercase tracking-[0.06em] text-[#193B31]">
                    Profil BRIDA Kota Surabaya
                </h2>

                <p class="mt-4 text-[13px] leading-7 text-[#64786D] sm:text-[14px]">
                    Badan Riset dan Inovasi Daerah (BRIDA) Kota Surabaya
                    merupakan perangkat daerah yang menjalankan fungsi
                    penunjang penelitian, pengembangan, pengkajian,
                    penerapan, serta invensi dan inovasi daerah.
                </p>
            </section>

            {{-- Tujuan dan peran --}}
            <section class="border border-[#D6E1DA] bg-white p-5 sm:p-6">
                <h2 class="text-xs font-bold uppercase tracking-[0.06em] text-[#193B31]">
                    Tujuan &amp; Peran BRIDA
                </h2>

                <p class="mt-4 text-[13px] leading-7 text-[#64786D] sm:text-[14px]">
                    BRIDA mendukung pembangunan Kota Surabaya melalui
                    riset, inovasi, dan pengembangan ilmu pengetahuan.
                    Salah satu perannya adalah mendukung pengelolaan
                    Kebun Raya Mangrove Surabaya sebagai kawasan
                    konservasi, edukasi, dan penelitian lingkungan.
                </p>
            </section>

            {{-- Struktur organisasi --}}
            <section class="border border-[#D6E1DA] bg-white p-5 sm:p-6">
                <h2 class="text-xs font-bold uppercase tracking-[0.06em] text-[#193B31]">
                    Struktur Organisasi
                </h2>

                <div class="mt-5 border border-[#D6E1DA] bg-[#F8FBF9] p-3 sm:p-4">
                    <img
                        src="{{ asset('images/tentang/struktur-organisasi-brida.png') }}"
                        alt="Bagan struktur organisasi BRIDA Kota Surabaya"
                        class="h-auto w-full object-contain"
                        loading="lazy"
                    >
                </div>

                <p class="mt-4 text-[12px] leading-6 text-[#718079]">
                    Bagan struktur organisasi BRIDA Kota Surabaya.
                </p>

                <a
                    href="https://jdih.surabaya.go.id/peraturan/4711"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-2 inline-block text-xs font-bold text-[#064631] hover:underline"
                >
                    Lihat dokumen struktur organisasi resmi &rarr;
                </a>
            </section>

            {{-- Dasar hukum --}}
            <section class="border border-[#D6E1DA] bg-white p-5 sm:p-6">
                <h2 class="text-xs font-bold uppercase tracking-[0.06em] text-[#193B31]">
                    Dasar Hukum
                </h2>

                <p class="mt-4 text-[13px] leading-7 text-[#64786D] sm:text-[14px]">
                    Peraturan Wali Kota Surabaya Nomor 41 Tahun 2025
                    tentang kedudukan, susunan organisasi, uraian tugas,
                    fungsi, serta tata kerja BRIDA Kota Surabaya.
                    Pengelolaan UPT Kebun Raya Mangrove juga diatur
                    melalui Peraturan Wali Kota Surabaya Nomor 55 Tahun 2025.
                </p>
            </section>

            {{-- Pengelolaan KRM --}}
            <section class="border border-[#D6E1DA] bg-white p-5 sm:p-6">
                <h2 class="text-xs font-bold uppercase tracking-[0.06em] text-[#193B31]">
                    Pengelolaan Kebun Raya Mangrove
                </h2>

                <p class="mt-4 text-[13px] leading-7 text-[#64786D] sm:text-[14px]">
                    {{ $tentang['nama'] }} dikelola melalui Unit
                    Pelaksana Teknis Kebun Raya Mangrove di lingkungan
                    BRIDA Kota Surabaya untuk mendukung konservasi,
                    penelitian, edukasi, dan pemanfaatan kawasan mangrove.
                </p>
            </section>

        </main>

        {{-- KOLOM KANAN --}}
        <aside class="space-y-4">

            {{-- Informasi singkat --}}
            <section class="border border-[#D6E1DA] bg-white p-6">
                <h2 class="text-[13px] font-bold uppercase tracking-[0.06em] text-[#193B31]">
                    Informasi Singkat
                </h2>

                <div class="mt-4 border-t border-[#D6E1DA] pt-5">

                    <div class="mb-5">
                        <p class="text-[11px] text-[#718079]">
                            Instansi
                        </p>
                        <p class="mt-1 text-[13px] font-semibold text-[#193B31]">
                            BRIDA Kota Surabaya
                        </p>
                    </div>

                    <div class="mb-5">
                        <p class="text-[11px] text-[#718079]">
                            Dasar Organisasi
                        </p>
                        <p class="mt-1 text-[13px] font-semibold text-[#193B31]">
                            Perwali 41 Tahun 2025
                        </p>
                    </div>

                    <div class="mb-5">
                        <p class="text-[11px] text-[#718079]">
                            Lokasi
                        </p>
                        <p class="mt-1 text-[13px] font-semibold text-[#193B31]">
                            Kota Surabaya
                        </p>
                    </div>

                    <div>
                        <p class="text-[11px] text-[#718079]">
                            Website
                        </p>
                        <a
                            href="https://brida.surabaya.go.id"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-1 inline-block text-[13px] font-semibold text-[#064631] hover:underline"
                        >
                            brida.surabaya.go.id
                        </a>
                    </div>
                </div>
            </section>

            {{-- Tautan terkait --}}
            <section class="border border-[#D6E1DA] bg-white p-6">
                <h2 class="text-[13px] font-bold uppercase tracking-[0.06em] text-[#193B31]">
                    Tautan Terkait
                </h2>

                <div class="mt-4 divide-y divide-[#E1E9E3] border-t border-[#D6E1DA] text-[13px]">

                    <a href="{{ route('wisatawan.destinasi') }}"
                       class="block py-4 text-[#2F795B] hover:text-[#064631]">
                        &rarr; Kebun Raya Mangrove
                    </a>

                    <a href="{{ route('ticket-purchase.index') }}"
                       class="block py-4 text-[#2F795B] hover:text-[#064631]">
                        &rarr; Beli Tiket
                    </a>

                    <a href="{{ route('wisatawan.reservasi.index') }}"
                       class="block py-4 text-[#2F795B] hover:text-[#064631]">
                        &rarr; Reservasi Venue
                    </a>

                    <a href="{{ route('wisatawan.kontak') }}"
                    class="block py-4 text-[#2F795B] hover:text-[#064631]">
                        &rarr; Hubungi Kami
                    </a>
                </div>
            </section>

            {{-- Identitas BRIDA --}}
            <section class="flex min-h-[170px] items-center justify-center border border-[#D6E1DA] bg-white p-6">
                <img
                    src="{{ asset('images/tentang/logo-brida.jpg') }}"
                    alt="Logo BRIDA Kota Surabaya"
                    class="max-h-[140px] w-auto max-w-full object-contain"
                    loading="lazy"
                >
            </section>

        </aside>
    </div>
</div>
@endsection
