@extends('layouts.app')

@section('title', 'Destinasi - Kebun Raya Mangrove Surabaya')

@section('content')
<div class="mx-auto w-full max-w-[1180px] px-4 pb-20 pt-10 sm:px-6 sm:pb-24 sm:pt-14 lg:px-8 lg:pt-16">
    {{-- Breadcrumb: menuju beranda milik wisatawan, bukan halaman publik. --}}
    <nav class="mb-5 text-[12px] font-semibold tracking-[0.08em] text-[#2F795B] sm:text-[13px]"
         aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li><a href="{{ route('wisatawan.beranda') }}" class="hover:underline">Beranda</a></li>
            <li aria-hidden="true">›</li>
            <li>Destinasi</li>
            <li aria-hidden="true">›</li>
            <li aria-current="page">{{ $destinasi['nama'] }}</li>
        </ol>
    </nav>

    {{-- Foto utama sesuai komposisi mockup. --}}
    <section aria-labelledby="destinasi-title">
        <img src="{{ asset($destinasi['gambar']) }}"
             alt="Jalur jembatan kayu di kawasan hutan mangrove"
             class="h-[245px] w-full object-cover object-center sm:h-[350px] lg:h-[460px]"
             fetchpriority="high">
    </section>

    <div class="mt-7 grid items-start gap-7 lg:grid-cols-[minmax(0,1fr)_290px] lg:gap-7">
        {{-- Deskripsi utama di sebelah kiri. --}}
        <div class="min-w-0">
            <h1 id="destinasi-title"
                class="inline-block max-w-full border-b-[3px] border-[#A1C977] pb-1 text-[23px] font-bold uppercase leading-tight tracking-tight text-[#193B31] sm:text-[28px] lg:text-[31px]">
                {{ $destinasi['nama'] }}
            </h1>

            <p class="mt-7 text-[13px] leading-7 text-[#586C60] sm:text-[15px] sm:leading-8">
                {{ $destinasi['deskripsi'] }}
            </p>

            {{-- Informasi terstruktur: nanti isi dapat berasal dari tabel master data. --}}
            <div class="mt-5 space-y-3">
                @foreach ($destinasi['informasi'] as $info)
                    <section class="border border-[#D6E1DA] bg-white px-5 py-5 sm:px-6" aria-label="{{ $info['judul'] }}">
                        <h2 class="text-xs font-bold uppercase tracking-[0.08em] text-[#17392E] sm:text-[13px]">
                            {{ $info['judul'] }}
                        </h2>
                        <p class="mt-2 text-[13px] leading-6 text-[#64786D] sm:text-[14px] sm:leading-7">
                            {{ $info['isi'] }}
                        </p>
                    </section>
                @endforeach
            </div>

            {{-- Galeri enam foto seperti desain. --}}
            <section class="mt-8" aria-labelledby="galeri-destinasi-title">
                <h2 id="galeri-destinasi-title"
                    class="mb-3 text-sm font-extrabold uppercase tracking-[0.09em] text-[#193B31]">
                    Galeri
                </h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-2.5">
                    @foreach ($galeri as $foto)
                        <div class="overflow-hidden bg-[#E4ECE6]">
                            <img src="{{ asset($foto['gambar']) }}"
                                 alt="{{ $foto['alt'] }}"
                                 class="h-28 w-full object-cover transition-transform duration-300 hover:scale-[1.04] sm:h-36 lg:h-[156px]"
                                 loading="lazy">
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Panel harga sebelah kanan --}}
        <aside class="border border-[#D6E1DA] border-t-[5px] border-t-[#A1C977] bg-white p-5 sm:p-6 lg:sticky lg:top-6"
               aria-labelledby="harga-tiket-title">
            <h2 id="harga-tiket-title" class="mb-4 text-[12px] font-bold uppercase tracking-[0.12em] text-[#17392E]">
                Harga Tiket
            </h2>

            <dl>
                @forelse ($hargaTiket as $harga)
                    <div class="flex items-start justify-between gap-2 border-b border-[#DEE6E0] py-3 text-[13px]">
                        <dt class="text-[#445F51]">{{ $harga['label'] }}</dt>
                        <dd class="shrink-0 font-bold text-[#193B31]">{{ $harga['harga'] }}</dd>
                    </div>
                @empty
                    <div class="py-4">
                        <dt class="text-sm text-[#718079]">
                            Harga tiket belum tersedia.
                        </dt>
                    </div>
                @endforelse
            </dl>

            @if (\Illuminate\Support\Facades\Route::has('tiket.index'))
                <a href="{{ route('tiket.index') }}"
                   class="mt-4 flex min-h-11 w-full items-center justify-center bg-[#064631] px-3 py-3 text-center text-[13px] font-bold text-white transition hover:bg-[#2F795B] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2F795B]">
                    Beli Tiket
                </a>
            @else
                <span aria-disabled="true" title="Fitur pembelian tiket sedang dikembangkan"
                      class="mt-4 flex min-h-11 w-full cursor-not-allowed items-center justify-center bg-[#064631] px-3 py-3 text-center text-[13px] font-bold text-white opacity-65">
                    Beli Tiket (segera)
                </span>
            @endif

            @if (\Illuminate\Support\Facades\Route::has('reservasi.index'))
                <a href="{{ route('reservasi.index') }}"
                   class="mt-3 flex min-h-11 w-full items-center justify-center border border-[#064631] px-3 py-3 text-center text-[13px] font-bold text-[#064631] transition hover:bg-[#EEF4EE]">
                    Reservasi Venue
                </a>
            @else
                <span aria-disabled="true" title="Fitur reservasi venue sedang dikembangkan"
                      class="mt-3 flex min-h-11 w-full cursor-not-allowed items-center justify-center border border-[#819B8B] px-3 py-3 text-center text-[13px] font-bold text-[#537463] opacity-70">
                    Reservasi Venue (segera)
                </span>
            @endif

            <p class="mt-4 text-[11px] leading-5 text-[#708176]">
                *Harga diambil dari master data tiket aplikasi untuk pengujian. Tarif resmi mengikuti konfirmasi pengelola.
            </p>
        </aside>
    </div>
</div>
@endsection