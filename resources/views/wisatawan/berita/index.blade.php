@extends('layouts.app')

@section('title', 'Berita & Artikel - Kebun Raya Mangrove Surabaya')

@section('content')
<div class="mx-auto w-full max-w-[1180px] px-4 pb-20 pt-14 sm:px-6 sm:pt-20 lg:px-8">
    <nav aria-label="Breadcrumb" class="mb-3 text-[12px] font-bold tracking-[0.09em] text-[#2F795B]">
        <a href="{{ route('wisatawan.beranda') }}" class="hover:underline">Beranda</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">Berita &amp; Artikel</span>
    </nav>

    <h1 class="inline-block border-b-[3px] border-[#A1C977] pb-1 text-[27px] font-bold leading-tight text-[#193B31] sm:text-[32px]">
        Berita &amp; Artikel
    </h1>

    <p class="mt-5 text-sm leading-6 text-[#718079]">
        Temukan berita, kegiatan, dan artikel edukasi
        seputar Kebun Raya Mangrove Surabaya.
    </p>

    {{-- Cari dan filter, keduanya memakai query string agar hasilnya dapat dibagikan/direfresh. --}}
    <div class="mt-8 flex flex-col gap-4 border border-[#D6E1DA] bg-white p-3 sm:p-4 lg:flex-row lg:items-center lg:justify-between">
        <form action="{{ route('wisatawan.berita.index') }}" method="GET" role="search"
              class="flex min-w-0 flex-1 items-center gap-2">
            @if ($kategori !== 'semua')
                <input type="hidden" name="kategori" value="{{ $kategori }}">
            @endif
            <label for="search-berita" class="sr-only">Cari artikel</label>
            <input id="search-berita" name="q" type="search" value="{{ $cari }}" maxlength="100"
                   placeholder="Cari artikel..."
                   class="w-full min-w-0 flex-1 rounded-none border-0 bg-transparent px-2 py-2 text-sm text-[#193B31] outline-none placeholder:text-[#93A29A] focus:ring-0"
                   autocomplete="off">
            <button type="submit" class="shrink-0 bg-[#064631] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#2F795B]">
                Cari
            </button>
        </form>

        <nav aria-label="Filter kategori berita" class="flex flex-wrap items-center gap-2">
            @foreach (['semua' => 'Semua', 'event' => 'Event', 'edukasi' => 'Edukasi', 'info' => 'Info'] as $key => $nama)
                <a href="{{ route('wisatawan.berita.index', array_filter(['kategori' => $key === 'semua' ? null : $key, 'q' => $cari], fn ($value) => $value !== null && $value !== '')) }}"
                   @if ($kategori === $key) aria-current="true" @endif
                   class="inline-flex min-h-9 items-center justify-center border px-4 py-2 text-xs font-semibold transition
                          {{ $kategori === $key
                              ? 'border-[#064631] bg-[#064631] text-white'
                              : 'border-[#D6E1DA] bg-white text-[#466459] hover:border-[#064631] hover:text-[#064631]' }}">
                    {{ $nama }}
                </a>
            @endforeach
        </nav>
    </div>

    <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3" aria-live="polite">
        @forelse ($berita as $artikel)
            <article class="flex min-w-0 flex-col border border-[#D6E1DA] bg-white">
                @if (!empty($artikel['gambar']))
                    <a href="{{ route('wisatawan.berita.show', $artikel['slug']) }}"
                    tabindex="-1" aria-hidden="true">
                        <img
                            src="{{ asset($artikel['gambar']) }}"
                            alt=""
                            class="h-[195px] w-full object-cover transition-opacity hover:opacity-90 sm:h-[200px]"
                            loading="lazy"
                        >
                    </a>
                @else
                    <div class="flex h-[195px] w-full items-center justify-center bg-[#E9F0EB] text-sm text-[#819187] sm:h-[200px]">
                        Gambar belum tersedia
                    </div>
                @endif
                <div class="flex flex-1 flex-col px-5 pb-5 pt-5">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-[#718079]">
                        <span class="bg-[#E2EEE2] px-2 py-1 font-semibold text-[#367750]">{{ $artikel['kategori'] }}</span>
                        <span>{{ $artikel['tanggal'] }}</span>
                    </div>
                    <h2 class="mt-4 text-[18px] font-semibold leading-7 text-[#193B31]">
                        <a href="{{ route('wisatawan.berita.show', $artikel['slug']) }}" class="hover:underline">
                            {{ $artikel['judul'] }}
                        </a>
                    </h2>
                    <p class="mt-3 flex-1 text-[15px] leading-7 text-[#718079]">{{ $artikel['ringkasan'] }}</p>
                    <a href="{{ route('wisatawan.berita.show', $artikel['slug']) }}"
                       class="mt-2 w-fit text-[13px] font-bold text-[#064631] hover:underline">
                        Baca selengkapnya →
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full border border-[#D6E1DA] bg-white p-10 text-center">

                @if ($cari !== '' || $kategori !== 'semua')
                    <h2 class="text-base font-semibold text-[#193B31]">
                        Artikel tidak ditemukan
                    </h2>

                    <p class="mt-2 text-sm text-[#718079]">
                        Coba kata kunci lain atau pilih kategori Semua.
                    </p>

                    <a href="{{ route('wisatawan.berita.index') }}"
                    class="mt-4 inline-block text-sm font-bold text-[#064631] underline">
                        Reset pencarian
                    </a>
                @else
                    <h2 class="text-base font-semibold text-[#193B31]">
                        Belum Ada Berita
                    </h2>

                    <p class="mt-2 text-sm text-[#718079]">
                        Belum ada berita atau artikel yang dipublikasikan.
                        Silakan kunjungi kembali nanti.
                    </p>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-9 flex flex-col gap-4 border-t border-[#D6E1DA] pt-5 text-[13px] text-[#718079] sm:flex-row sm:items-center sm:justify-between">
        <p>
            @if ($berita->total() > 0)
                Menampilkan {{ $berita->firstItem() }}–{{ $berita->lastItem() }} dari {{ $berita->total() }} artikel
            @else
                Menampilkan 0 artikel
            @endif
        </p>

        @php
            $start = max(1, $berita->currentPage() - 1);
            $end = min($berita->lastPage(), $start + 3);
            $start = max(1, $end - 3);
        @endphp
        <nav aria-label="Navigasi halaman berita" class="flex flex-wrap gap-2">
            @if ($berita->onFirstPage())
                <span aria-disabled="true" class="inline-flex h-10 items-center justify-center border border-[#D6E1DA] px-3 text-xs text-[#ABB6B0]">← Prev</span>
            @else
                <a href="{{ $berita->previousPageUrl() }}" rel="prev" class="inline-flex h-10 items-center justify-center border border-[#D6E1DA] px-3 text-xs text-[#466459] hover:border-[#064631]">← Prev</a>
            @endif

            @for ($page = $start; $page <= $end; $page++)
                <a href="{{ $berita->url($page) }}"
                   @if ($page === $berita->currentPage()) aria-current="page" @endif
                   class="inline-flex h-10 w-10 items-center justify-center border text-xs
                          {{ $page === $berita->currentPage()
                              ? 'border-[#064631] bg-[#064631] font-bold text-white'
                              : 'border-[#D6E1DA] text-[#466459] hover:border-[#064631]' }}">
                    {{ $page }}
                </a>
            @endfor

            @if ($berita->hasMorePages())
                <a href="{{ $berita->nextPageUrl() }}" rel="next" class="inline-flex h-10 items-center justify-center border border-[#D6E1DA] px-3 text-xs text-[#466459] hover:border-[#064631]">Next →</a>
            @else
                <span aria-disabled="true" class="inline-flex h-10 items-center justify-center border border-[#D6E1DA] px-3 text-xs text-[#ABB6B0]">Next →</span>
            @endif
        </nav>
    </div>
</div>
@endsection