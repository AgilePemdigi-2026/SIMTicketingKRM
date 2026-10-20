@extends('layouts.app')

@section('title', 'Beranda - Kebun Raya Mangrove Surabaya')

@section('content')
@if(request()->routeIs('wisatawan.beranda'))
    <div class="bg-[#EAF3EC] px-6 py-3 text-center text-sm text-[#064631]">
        Selamat datang kembali,
        <strong>{{ auth()->user()->name }}</strong>!
    </div>
@endif
    {{-- Hero: bisa diakses semua pengunjung, termasuk yang belum login. --}}
    <section class="relative isolate flex min-h-[430px] items-center justify-center overflow-hidden bg-[#063A2B] px-4 py-16 text-center text-white sm:min-h-[490px]"
             aria-labelledby="hero-title">
        <img src="{{ asset('images/home/boardwalk.jpg') }}"
             alt="Jalur wisata di antara pepohonan mangrove"
             class="absolute inset-0 -z-20 h-full w-full object-cover"
             fetchpriority="high">
        <div class="absolute inset-0 -z-10 bg-[#052e23]/70"></div>

        <div class="mx-auto max-w-[800px]">
            <p class="mb-5 text-[11px] font-bold uppercase tracking-[0.27em] text-[#e5efe7] sm:text-xs">
                WISATA ALAM KOTA SURABAYA
            </p>
            <h1 id="hero-title" class="text-4xl font-bold leading-[1.16] tracking-tight sm:text-5xl lg:text-[58px]">
                Jelajahi Keindahan<br class="hidden sm:block"> Mangrove Surabaya
            </h1>
            <p class="mx-auto mt-5 max-w-[580px] text-sm leading-7 text-[#f0f5ef] sm:text-base">
                Nikmati wisata alam mangrove yang unik, hijau, dan edukatif di Kebun Raya Mangrove Surabaya.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                {{-- Belum membuka pembelian tiket selama route Naufal belum tersedia. --}}
                @if (\Illuminate\Support\Facades\Route::has('tiket.index'))
                    <a href="{{ route('tiket.index') }}"
                       class="inline-flex min-h-11 items-center justify-center bg-[#064631] px-7 py-3 text-sm font-semibold text-white transition hover:bg-[#0a674a]">
                        Beli Tiket
                    </a>
                @else
                    <span aria-disabled="true" title="Fitur pembelian tiket sedang dikembangkan"
                          class="inline-flex min-h-11 cursor-not-allowed items-center justify-center bg-[#064631] px-7 py-3 text-sm font-semibold text-white opacity-80">
                        Beli Tiket
                    </span>
                @endif
                <a href="#jelajahi"
                   class="inline-flex min-h-11 items-center justify-center border border-white bg-white/10 px-7 py-3 text-sm font-semibold text-white transition hover:bg-white/20">
                    Jelajahi Kebun Raya
                </a>
            </div>
        </div>
    </section>

    <div class="mx-auto w-full max-w-[1180px] space-y-16 px-4 py-16 sm:px-6 sm:py-20 lg:space-y-20 lg:px-8">
        {{-- WAHANA --}}
        <section id="jelajahi" aria-labelledby="wahana-title" class="scroll-mt-12">
            <div class="mb-6 flex items-center gap-5 border-b-2 border-[#064631] pb-3">
                <h2 id="wahana-title" class="text-sm font-bold uppercase tracking-[0.13em] text-[#064631]">
                    JELAJAHI KEBUN RAYA MANGROVE
                </h2>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($wahana as $item)
                    <article class="flex min-w-0 flex-col border border-[#DFE7E1] bg-white">
                        <img src="{{ asset($item['gambar']) }}"
                             alt="Ilustrasi {{ $item['nama'] }}"
                             class="h-44 w-full object-cover sm:h-48" loading="lazy">
                        <div class="flex flex-1 flex-col p-4">
                            <h3 class="text-[15px] font-semibold text-[#193B31]">{{ $item['nama'] }}</h3>
                            <p class="mt-2 flex-1 text-[13px] leading-5 text-[#718079]">
                                {{ $item['deskripsi'] }}
                            </p>
                            @if (\Illuminate\Support\Facades\Route::has('wahana.show'))
                                <a href="{{ route('wahana.show', $item['slug']) }}"
                                   class="mt-3 inline-block text-[13px] font-bold text-[#064631] hover:underline">
                                    Lihat Detail →
                                </a>
                            @else
                                <span title="Halaman detail wahana masih dikembangkan"
                                      class="mt-3 inline-block text-[13px] font-bold text-[#668776]">
                                    Detail segera hadir
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="col-span-full border border-[#DFE7E1] bg-white px-5 py-8 text-sm text-[#718079]">
                        Informasi wahana belum tersedia.
                    </p>
                @endforelse
            </div>
        </section>

        {{-- FASILITAS --}}
        <section id="fasilitas" aria-labelledby="fasilitas-title">
            <div class="mb-6 border-b-2 border-[#064631] pb-3">
                <h2 id="fasilitas-title" class="text-sm font-bold uppercase tracking-[0.13em] text-[#064631]">
                    VENUE &amp; FASILITAS
                </h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                @forelse ($fasilitas as $item)
                    <article class="flex min-w-0 flex-col border border-[#DFE7E1] bg-white">
                        <img src="{{ asset($item['gambar']) }}" alt="Ilustrasi {{ $item['nama'] }}"
                             class="h-32 w-full object-cover sm:h-36" loading="lazy">
                        <div class="flex flex-1 flex-col p-3">
                            <h3 class="text-[13px] font-semibold text-[#193B31]">{{ $item['nama'] }}</h3>
                            <p class="mt-1 flex-1 text-xs leading-5 text-[#718079]">{{ $item['kapasitas'] }}</p>
                            @if (\Illuminate\Support\Facades\Route::has('reservasi.index'))
                                <a href="{{ route('reservasi.index') }}"
                                   class="mt-3 flex min-h-9 items-center justify-center border border-[#064631] text-xs font-bold text-[#064631] hover:bg-[#EEF4EE]">
                                    Reservasi
                                </a>
                            @else
                                <span title="Reservasi masih dikembangkan"
                                      class="mt-3 flex min-h-9 cursor-not-allowed items-center justify-center border border-[#A8C1B2] text-xs font-bold text-[#648875]">
                                    Reservasi (segera)
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="col-span-full text-sm text-[#718079]">Informasi fasilitas belum tersedia.</p>
                @endforelse
            </div>
        </section>

        {{-- GALERI --}}
        <section id="galeri" aria-labelledby="galeri-title">
            <div class="mb-6 border-b-2 border-[#064631] pb-3">
                <h2 id="galeri-title" class="text-sm font-bold uppercase tracking-[0.13em] text-[#064631]">
                    GALERI
                </h2>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach (array_slice($galeri, 0, 4) as $foto)
                    <img src="{{ asset($foto) }}" alt="Galeri wisata mangrove {{ $loop->iteration }}"
                         class="h-32 w-full object-cover sm:h-44" loading="lazy">
                @endforeach
            </div>
            <details class="group mt-3">
                <summary class="mx-auto mt-5 flex w-fit min-h-10 cursor-pointer list-none items-center justify-center border border-[#064631] px-6 py-2 text-xs font-semibold text-[#064631] hover:bg-[#EEF4EE] [&::-webkit-details-marker]:hidden">
                    <span class="group-open:hidden">Lihat Semua Foto +</span>
                    <span class="hidden group-open:inline">Sembunyikan Foto −</span>
                </summary>
                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach (array_slice($galeri, 4) as $foto)
                        <img src="{{ asset($foto) }}" alt="Galeri wisata mangrove tambahan {{ $loop->iteration }}"
                             class="h-32 w-full object-cover sm:h-44" loading="lazy">
                    @endforeach
                </div>
            </details>
        </section>

        {{-- BERITA: sementara menggunakan data contoh, belum artikel resmi --}}
        <section id="berita" aria-labelledby="berita-title">
            <div class="mb-6 border-b-2 border-[#064631] pb-3">
                <h2 id="berita-title" class="text-sm font-bold uppercase tracking-[0.13em] text-[#064631]">
                    BERITA &amp; ARTIKEL
                </h2>
            </div>
            <p class="mb-4 text-xs text-[#718079]">Konten berita berikut merupakan contoh tampilan sampai data dari admin tersedia.</p>
            <div class="grid gap-5 md:grid-cols-3">
                @forelse ($berita as $item)
                    <article class="flex min-w-0 flex-col border border-[#DFE7E1] bg-white">
                        <img src="{{ asset($item['gambar']) }}" alt="Ilustrasi artikel {{ $item['judul'] }}"
                             class="h-44 w-full object-cover" loading="lazy">
                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex items-center gap-3 text-xs text-[#7A8B80]">
                                <span class="bg-[#E4EFE3] px-2 py-1 font-semibold text-[#367750]">{{ $item['kategori'] }}</span>
                                <span>{{ $item['tanggal'] }}</span>
                            </div>
                            <h3 class="mt-3 text-[15px] font-semibold leading-6 text-[#193B31]">{{ $item['judul'] }}</h3>
                            <p class="mt-2 flex-1 text-[13px] leading-5 text-[#718079]">{{ $item['ringkasan'] }}</p>
                            <span class="mt-4 text-xs font-bold text-[#648875]" title="Halaman detail berita sedang dibuat">
                                Artikel lengkap segera hadir
                            </span>
                        </div>
                    </article>
                @empty
                    <p class="col-span-full text-sm text-[#718079]">Belum ada berita yang ditampilkan.</p>
                @endforelse
            </div>
        </section>

        {{-- KONTAK: isinya masih contoh dari mockup, belum data resmi. --}}
        <section id="kontak" aria-labelledby="kontak-title">
            <div class="mb-6 border-b-2 border-[#064631] pb-3">
                <h2 id="kontak-title" class="text-sm font-bold uppercase tracking-[0.13em] text-[#064631]">KONTAK</h2>
            </div>
            <div class="grid gap-5 md:grid-cols-2">
                <div class="border border-[#DFE7E1] bg-white p-6 sm:p-7">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-[#193B31]">INFORMASI KONTAK</h3>
                    <dl class="mt-6 grid grid-cols-[90px_1fr] gap-x-3 gap-y-3 text-xs leading-5 sm:grid-cols-[110px_1fr] sm:text-[13px]">
                        <dt class="text-[#718079]">Alamat</dt><dd>Jl. Mangrove Raya No. 1, Surabaya 60111</dd>
                        <dt class="text-[#718079]">Telepon</dt><dd>(031) 123-4567</dd>
                        <dt class="text-[#718079]">Email</dt><dd>info@krm.surabaya.go.id</dd>
                        <dt class="text-[#718079]">Instagram</dt><dd>@krm_surabaya</dd>
                        <dt class="text-[#718079]">Jam Buka</dt><dd>07.00–17.00 WIB (Setiap Hari)</dd>
                    </dl>
                    <p class="mt-5 text-[11px] italic text-[#7B897E]">*Informasi kontak sementara mengikuti mockup; perlu verifikasi pengelola.</p>
                </div>
                <div class="flex min-h-60 flex-col items-center justify-center border border-[#DFE7E1] bg-[#E7EFEB] p-6 text-center">
                    <svg class="h-12 w-12 text-[#064631]" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                        <circle cx="24" cy="24" r="22" fill="white" />
                        <path d="M13 15L21 11L28 15L36 11V33L28 37L21 33L13 37V15Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" />
                        <path d="M21 11V33M28 15V37" stroke="currentColor" stroke-width="1.8" />
                    </svg>
                    <p class="mt-4 text-sm font-semibold text-[#064631]">Kebun Raya Mangrove Surabaya</p>
                    <p class="mt-1 text-xs text-[#718079]">Gunung Anyar, Surabaya</p>
                    <a href="https://www.google.com/maps/search/?api=1&amp;query=Kebun%20Raya%20Mangrove%20Surabaya"
                       target="_blank" rel="noopener noreferrer"
                       class="mt-5 inline-flex min-h-10 items-center justify-center border border-[#064631] px-5 py-2 text-xs font-semibold text-[#064631] hover:bg-white">
                        Buka Petunjuk Lokasi ↗
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
