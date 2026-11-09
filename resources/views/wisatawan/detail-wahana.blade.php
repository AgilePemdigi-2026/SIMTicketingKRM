@extends('layouts.app')

@section('title', $wahana['nama'] . ' - Detail Wahana')

@section('content')
<div class="mx-auto w-full max-w-[1180px] px-4 pb-20 pt-10 sm:px-6 sm:pb-24 sm:pt-14 lg:px-8 lg:pt-16">
    {{-- Semua detail wahana wajib login; breadcrumb kembali ke beranda wisatawan. --}}
    <nav class="mb-5 text-[12px] font-semibold tracking-[0.08em] text-[#2F795B] sm:text-[13px]" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li><a href="{{ route('wisatawan.beranda') }}" class="hover:underline">Beranda</a></li>
            <li aria-hidden="true">›</li>
            <li>Wahana</li>
            <li aria-hidden="true">›</li>
            <li aria-current="page">{{ $wahana['nama'] }}</li>
        </ol>
    </nav>

    {{-- Mengikuti mockup: foto di kiri dan panel harga di kanan. --}}
    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_290px] lg:gap-7">
        <div class="min-w-0">
            <img src="{{ asset($wahana['gambar']) }}"
                 alt="Ilustrasi pemandangan kawasan mangrove untuk wahana {{ $wahana['nama'] }}"
                 class="h-[245px] w-full bg-[#E4ECE6] object-cover object-center sm:h-[345px] lg:h-[390px]"
                 fetchpriority="high">

            <article class="mt-8" aria-labelledby="wahana-title">
                <h1 id="wahana-title"
                    class="inline-block border-b-[3px] border-[#A1C977] pb-1 text-[24px] font-bold uppercase leading-tight tracking-tight text-[#193B31] sm:text-[29px] lg:text-[31px]">
                    {{ $wahana['nama'] }}
                </h1>

                <p class="mt-7 text-[13px] leading-7 text-[#586C60] sm:text-[15px] sm:leading-8">
                    {{ $wahana['deskripsi'] }}
                </p>

                <div class="mt-5 space-y-3">
                    <section class="border border-[#D6E1DA] bg-white px-5 py-5 sm:px-6" aria-labelledby="wahana-harga-title">
                        <h2 id="wahana-harga-title" class="text-xs font-bold uppercase tracking-[0.08em] text-[#17392E] sm:text-[13px]">
                            Harga
                        </h2>
                        <p class="mt-2 text-[13px] leading-6 text-[#64786D] sm:text-[14px]">
                            {{ $wahana['harga_detail'] }}
                        </p>
                    </section>

                    <section class="border border-[#D6E1DA] bg-white px-5 py-5 sm:px-6" aria-labelledby="wahana-ketentuan-title">
                        <h2 id="wahana-ketentuan-title" class="text-xs font-bold uppercase tracking-[0.08em] text-[#17392E] sm:text-[13px]">
                            Ketentuan Penggunaan
                        </h2>
                        <p class="mt-2 text-[13px] leading-6 text-[#64786D] sm:text-[14px]">
                            {{ $wahana['ketentuan'] }}
                        </p>
                    </section>

                    <section class="border border-[#D6E1DA] bg-white px-5 py-5 sm:px-6" aria-labelledby="wahana-jam-title">
                        <h2 id="wahana-jam-title" class="text-xs font-bold uppercase tracking-[0.08em] text-[#17392E] sm:text-[13px]">
                            Jam Operasional
                        </h2>
                        <p class="mt-2 text-[13px] leading-6 text-[#64786D] sm:text-[14px]">
                            {{ $wahana['jam_operasional'] }}
                        </p>
                    </section>
                </div>
            </article>

            <section class="mt-8" aria-labelledby="galeri-wahana-title">
                <h2 id="galeri-wahana-title" class="mb-3 text-sm font-extrabold uppercase tracking-[0.09em] text-[#193B31]">
                    Galeri
                </h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-2.5">
                    @foreach ($wahana['galeri'] as $foto)
                        <div class="overflow-hidden bg-[#E4ECE6]">
                            <img src="{{ asset($foto['gambar']) }}"
                                 alt="{{ $foto['alt'] }}"
                                 class="h-28 w-full object-cover transition-transform duration-300 hover:scale-[1.04] sm:h-32 lg:h-[142px]"
                                 loading="lazy">
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-[11px] leading-5 text-[#718079]">
                    Foto merupakan ilustrasi dari mockup. Akan diganti dengan dokumentasi asli wahana ketika tersedia.
                </p>
            </section>
        </div>

        {{-- Pembelian tiket belum aktif: belum ada route Naufal / tabel final Dana. --}}
        <aside class="border-t-[5px] border-[#A1C977] bg-white p-5 sm:p-6 lg:sticky lg:top-6"
               aria-labelledby="detail-harga-title">
            <h2 id="detail-harga-title" class="mb-5 text-[12px] font-bold uppercase tracking-[0.12em] text-[#17392E]">
                Detail Harga
            </h2>

            @if ($wahana['harga'])
                <p class="text-[27px] font-bold leading-tight text-[#064631] sm:text-[31px]">
                    {{ $wahana['harga'] }}
                </p>
                <p class="mt-1 text-[13px] text-[#718079]">
                    {{ $wahana['harga_satuan'] }}
                </p>
            @else
                <p class="text-[17px] font-semibold leading-6 text-[#064631]">Harga belum tersedia</p>
                <p class="mt-2 text-[13px] leading-5 text-[#718079]">Menunggu data harga dari Admin.</p>
            @endif

            <button type="button" disabled aria-disabled="true"
                    title="Pembelian tiket wahana belum tersedia"
                    class="mt-5 flex min-h-11 w-full cursor-not-allowed items-center justify-center bg-[#064631] px-3 py-3 text-center text-[13px] font-bold text-white opacity-65">
                Beli Tiket Wahana
            </button>
            <p class="mt-2 text-[11px] leading-5 text-[#718079]">
                Pembelian wahana akan aktif setelah modul tiket terintegrasi.
            </p>

            <a href="{{ route('wisatawan.beranda') }}"
               class="mt-4 flex min-h-11 w-full items-center justify-center border border-[#064631] px-3 py-3 text-center text-[13px] font-bold text-[#064631] transition hover:bg-[#EEF4EE] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2F795B]">
                ← Kembali
            </a>
        </aside>
    </div>
</div>
@endsection
