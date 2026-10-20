@extends('layouts.app')

@section('title', $artikel['judul'].' - Kebun Raya Mangrove Surabaya')

@section('content')
<div class="mx-auto w-full max-w-[940px] px-4 pb-20 pt-14 sm:px-6 sm:pt-20 lg:px-8">
    <nav aria-label="Breadcrumb" class="mb-6 text-[12px] font-bold tracking-[0.06em] text-[#2F795B]">
        <a href="{{ route('wisatawan.beranda') }}" class="hover:underline">Beranda</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('wisatawan.berita.index') }}" class="hover:underline">Berita &amp; Artikel</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">Detail Artikel</span>
    </nav>

    <article>
        <div class="mb-4 flex flex-wrap items-center gap-3 text-xs text-[#718079]">
            <span class="bg-[#E2EEE2] px-2 py-1 font-semibold text-[#367750]">{{ $artikel['kategori'] }}</span>
            <span>{{ $artikel['tanggal'] }}</span>
        </div>
        <h1 class="max-w-3xl border-b-[3px] border-[#A1C977] pb-3 text-3xl font-bold leading-tight text-[#193B31] sm:text-4xl">
            {{ $artikel['judul'] }}
        </h1>
        <img src="{{ asset($artikel['gambar']) }}" alt="Ilustrasi: {{ $artikel['judul'] }}"
             class="mt-7 h-[260px] w-full object-cover sm:h-[390px]">
        <p class="mt-7 text-base leading-8 text-[#52695D]">{{ $artikel['ringkasan'] }}</p>
        <div class="mt-6 border-l-[3px] border-[#A1C977] bg-[#F0F5EF] px-5 py-4 text-sm leading-7 text-[#466459]">
            Artikel ini masih berupa <strong>data contoh berdasarkan mockup</strong>. Isi lengkap belum dipublikasikan;
            nanti akan diambil dari tabel konten sesuai struktur yang disepakati tim.
        </div>
    </article>

    <a href="{{ route('wisatawan.berita.index') }}"
       class="mt-10 inline-flex min-h-11 items-center justify-center border border-[#064631] px-6 py-3 text-sm font-semibold text-[#064631] hover:bg-[#EEF4EE]">
        ← Kembali ke daftar berita
    </a>
</div>
@endsection