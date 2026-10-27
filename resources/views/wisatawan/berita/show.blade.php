@extends('layouts.app')

@section('title', $artikel['judul'].' - Kebun Raya Mangrove Surabaya')

@section('content')

<div class="mx-auto w-full max-w-[940px] px-4 pb-20 pt-14 sm:px-6 sm:pt-20 lg:px-8">

    {{-- BREADCRUMB --}}
    <nav aria-label="Breadcrumb"
         class="mb-6 text-[12px] font-bold tracking-[0.06em] text-[#2F795B]">

        <a href="{{ route('wisatawan.beranda') }}"
           class="hover:underline">
            Beranda
        </a>

        <span aria-hidden="true">›</span>

        <a href="{{ route('wisatawan.berita.index') }}"
           class="hover:underline">
            Berita &amp; Artikel
        </a>

        <span aria-hidden="true">›</span>

        <span aria-current="page">Detail Artikel</span>

    </nav>

    {{-- DETAIL ARTIKEL --}}
    <article>

        {{-- KATEGORI DAN TANGGAL --}}
        <div class="mb-4 flex flex-wrap items-center gap-3 text-xs text-[#718079]">

            <span class="bg-[#E2EEE2] px-2 py-1 font-semibold text-[#367750]">
                {{ $artikel['kategori'] }}
            </span>

            <span>
                {{ $artikel['tanggal'] }}
            </span>

        </div>

        {{-- JUDUL ARTIKEL --}}
        <h1 class="max-w-3xl border-b-[3px] border-[#A1C977] pb-3 text-3xl font-bold leading-tight text-[#193B31] sm:text-4xl">
            {{ $artikel['judul'] }}
        </h1>

        {{-- NAMA PENULIS --}}
        @if (!empty($artikel['penulis']))
            <p class="mt-4 text-sm text-[#718079]">
                Ditulis oleh
                <span class="font-semibold text-[#345D47]">
                    {{ $artikel['penulis'] }}
                </span>
            </p>
        @endif


        {{-- GAMBAR ARTIKEL --}}
        @if (!empty($artikel['gambar']))
            <img
                src="{{ asset($artikel['gambar']) }}"
                alt="Gambar artikel: {{ $artikel['judul'] }}"
                class="mt-7 h-[260px] w-full object-cover sm:h-[390px]"
            >
        @endif

        {{-- ISI ARTIKEL DARI POSTGRESQL --}}
        <div class="mt-8">

            <div class="whitespace-pre-line break-words text-base leading-8 text-[#52695D]">{{ $artikel['isi'] }}</div>

        </div>

    </article>

    {{-- TOMBOL KEMBALI --}}
    <a
        href="{{ route('wisatawan.berita.index') }}"
        class="mt-10 inline-flex min-h-11 items-center justify-center border border-[#064631] px-6 py-3 text-sm font-semibold text-[#064631] hover:bg-[#EEF4EE]"
    >
        ← Kembali ke daftar berita
    </a>

</div>

@endsection