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
                <h2 id="galeri-title"
                    class="text-sm font-bold uppercase tracking-[0.13em] text-[#064631]">
                    GALERI
                </h2>
            </div>

            @if (count($galeri) > 0)

                {{-- EMPAT FOTO PERTAMA --}}
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach (array_slice($galeri, 0, 4) as $foto)
                        <img
                            src="{{ asset($foto) }}"
                            alt="Galeri wisata mangrove {{ $loop->iteration }}"
                            class="h-32 w-full object-cover sm:h-44"
                            loading="lazy"
                        >
                    @endforeach
                </div>

                {{-- FOTO TAMBAHAN --}}
                @if (count($galeri) > 4)
                    <details class="group mt-3">

                        <summary
                            class="mx-auto mt-5 flex w-fit min-h-10 cursor-pointer list-none items-center justify-center border border-[#064631] px-6 py-2 text-xs font-semibold text-[#064631] hover:bg-[#EEF4EE] [&::-webkit-details-marker]:hidden"
                        >
                            <span class="group-open:hidden">
                                Lihat Semua Foto +
                            </span>

                            <span class="hidden group-open:inline">
                                Sembunyikan Foto −
                            </span>
                        </summary>

                        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach (array_slice($galeri, 4) as $foto)
                                <img
                                    src="{{ asset($foto) }}"
                                    alt="Galeri wisata mangrove tambahan {{ $loop->iteration }}"
                                    class="h-32 w-full object-cover sm:h-44"
                                    loading="lazy"
                                >
                            @endforeach
                        </div>

                    </details>
                @endif

            @else

                {{-- BELUM ADA FOTO PUBLISHED --}}
                <div class="border border-[#D6E1DA] bg-white px-6 py-10 text-center">
                    <p class="text-sm text-[#718079]">
                        Belum ada foto galeri yang dipublikasikan.
                    </p>
                </div>

            @endif

        </section>

        {{-- BERITA & ARTIKEL DARI POSTGRESQL --}}
        <section id="berita" aria-labelledby="berita-title">

            <div class="mb-6 border-b-2 border-[#064631] pb-3">
                <h2 id="berita-title"
                    class="text-sm font-bold uppercase tracking-[0.13em] text-[#064631]">
                    BERITA &amp; ARTIKEL
                </h2>
            </div>

            <div class="grid gap-5 md:grid-cols-3">

                @forelse ($berita as $item)

                    <article class="flex min-w-0 flex-col border border-[#DFE7E1] bg-white">

                        {{-- THUMBNAIL --}}
                        @if (!empty($item['gambar']))
                            <img
                                src="{{ asset($item['gambar']) }}"
                                alt="Gambar artikel {{ $item['judul'] }}"
                                class="h-44 w-full object-cover"
                                loading="lazy"
                            >
                        @else
                            <div class="flex h-44 items-center justify-center bg-[#E9F0EB] text-sm text-[#819187]">
                                Gambar belum tersedia
                            </div>
                        @endif

                        <div class="flex flex-1 flex-col p-4">

                            {{-- KATEGORI DAN TANGGAL --}}
                            <div class="flex flex-wrap items-center gap-3 text-xs text-[#7A8B80]">

                                <span class="bg-[#E4EFE3] px-2 py-1 font-semibold text-[#367750]">
                                    {{ $item['kategori'] }}
                                </span>

                                <span>{{ $item['tanggal'] }}</span>

                            </div>

                            {{-- JUDUL --}}
                            <h3 class="mt-3 text-[15px] font-semibold leading-6 text-[#193B31]">
                                {{ $item['judul'] }}
                            </h3>

                            {{-- RINGKASAN --}}
                            <p class="mt-2 flex-1 text-[13px] leading-5 text-[#718079]">
                                {{ $item['ringkasan'] }}
                            </p>

                            {{-- LINK DETAIL --}}
                            @if (auth()->check() && auth()->user()->role === 'wisatawan')

                                <a
                                    href="{{ route('wisatawan.berita.show', $item['slug']) }}"
                                    class="mt-4 w-fit text-xs font-bold text-[#064631] hover:underline"
                                >
                                    Baca selengkapnya →
                                </a>

                            @elseif (!auth()->check())

                                <a
                                    href="{{ route('login') }}"
                                    class="mt-4 w-fit text-xs font-bold text-[#064631] hover:underline"
                                >
                                    Masuk untuk membaca →
                                </a>

                            @else

                                <span class="mt-4 text-xs text-[#718079]">
                                    Detail tersedia untuk akun wisatawan.
                                </span>

                            @endif

                        </div>

                    </article>

                @empty

                    <div class="col-span-full border border-[#DFE7E1] bg-white px-6 py-10 text-center">
                        <p class="text-sm text-[#718079]">
                            Belum ada berita atau artikel yang dipublikasikan.
                        </p>
                    </div>

                @endforelse

            </div>

        </section>
        
        {{-- KONTAK DARI POSTGRESQL --}}
        <section id="kontak" aria-labelledby="kontak-title">

        <div class="mb-6 border-b-2 border-[#064631] pb-3">
            <h2
                id="kontak-title"
                class="text-sm font-bold uppercase tracking-[0.13em] text-[#064631]"
            >
                KONTAK
            </h2>
        </div>

        <div class="grid gap-5 md:grid-cols-2">

            {{-- INFORMASI KONTAK --}}
            <div class="border border-[#DFE7E1] bg-white p-6 sm:p-7">

                <h3 class="text-xs font-bold uppercase tracking-wide text-[#193B31]">
                    INFORMASI KONTAK
                </h3>

                @if ($kontak)

                    <dl class="mt-6 grid grid-cols-[90px_1fr] gap-x-3 gap-y-3 text-xs leading-5 sm:grid-cols-[110px_1fr] sm:text-[13px]">

                        @if ($kontak->alamat)
                            <dt class="text-[#718079]">Alamat</dt>
                            <dd class="break-words">{{ $kontak->alamat }}</dd>
                        @endif

                        @if ($kontak->nomor_telepon)
                            <dt class="text-[#718079]">Telepon</dt>
                            <dd>{{ $kontak->nomor_telepon }}</dd>
                        @endif

                        @if ($kontak->email)
                            <dt class="text-[#718079]">Email</dt>
                            <dd class="break-all">{{ $kontak->email }}</dd>
                        @endif

                        @if ($kontak->jam_operasional)
                            <dt class="text-[#718079]">Jam Buka</dt>
                            <dd>{{ $kontak->jam_operasional }}</dd>
                        @endif

                        @if ($kontak->instagram)
                            <dt class="text-[#718079]">Instagram</dt>
                            <dd class="break-all">{{ $kontak->instagram }}</dd>
                        @endif

                        @if ($kontak->facebook)
                            <dt class="text-[#718079]">Facebook</dt>
                            <dd class="break-all">{{ $kontak->facebook }}</dd>
                        @endif

                        @if ($kontak->youtube)
                            <dt class="text-[#718079]">YouTube</dt>
                            <dd class="break-all">{{ $kontak->youtube }}</dd>
                        @endif

                        @if ($kontak->twitter)
                            <dt class="text-[#718079]">Twitter / X</dt>
                            <dd class="break-all">{{ $kontak->twitter }}</dd>
                        @endif

                    </dl>

                    @if (
                        !$kontak->alamat &&
                        !$kontak->nomor_telepon &&
                        !$kontak->email &&
                        !$kontak->jam_operasional &&
                        !$kontak->instagram &&
                        !$kontak->facebook &&
                        !$kontak->youtube &&
                        !$kontak->twitter
                    )
                        <p class="mt-6 text-sm text-[#718079]">
                            Informasi kontak belum tersedia.
                        </p>
                    @endif

                @else

                    <p class="mt-6 text-sm text-[#718079]">
                        Informasi kontak belum tersedia.
                    </p>

                @endif

            </div>

            {{-- INFORMASI LOKASI --}}
            <div class="flex min-h-60 flex-col items-center justify-center border border-[#DFE7E1] bg-[#E7EFEB] p-6 text-center">

                <svg
                    class="h-12 w-12 text-[#064631]"
                    viewBox="0 0 48 48"
                    fill="none"
                    aria-hidden="true"
                >
                    <circle cx="24" cy="24" r="22" fill="white" />

                    <path
                        d="M13 15L21 11L28 15L36 11V33L28 37L21 33L13 37V15Z"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M21 11V33M28 15V37"
                        stroke="currentColor"
                        stroke-width="1.8"
                    />
                </svg>

                <p class="mt-4 text-sm font-semibold text-[#064631]">
                    Kebun Raya Mangrove Surabaya
                </p>

                @if ($kontak?->alamat)
                    <p class="mt-2 text-xs leading-5 text-[#718079]">
                        {{ $kontak->alamat }}
                    </p>
                @endif

                @if ($kontak?->google_maps_url)

                    <a
                        href="{{ $kontak->google_maps_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-5 inline-flex min-h-10 items-center justify-center border border-[#064631] px-5 py-2 text-xs font-semibold text-[#064631] hover:bg-white"
                    >
                        Buka Petunjuk Lokasi ↗
                    </a>

                @else

                    <p class="mt-5 text-xs text-[#718079]">
                        Tautan lokasi belum tersedia.
                    </p>

                @endif

            </div>

        </div>

        </section>

    </div>
@endsection
