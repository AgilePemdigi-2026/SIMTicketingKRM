
@php
    $isPublicHome = request()->routeIs('home');

    $berandaUrl = $isPublicHome
        ? route('home')
        : (
            auth()->user()?->role === 'wisatawan'
                ? route('wisatawan.beranda')
                : route('home')
        );

    $navItems = [
        [
            'label' => 'Beranda',
            'href' => $berandaUrl ?? url('/'),
            'active' => request()->routeIs(
                'home',
                'wisatawan.beranda'
            ),
        ],
        [
            'label' => 'Destinasi',
            'href' => route('wisatawan.destinasi'),
            'active' => request()->routeIs(
                'wisatawan.destinasi',
                'wahana.show'
            ),
        ],
        [
            'label' => 'Tiket',
            'href' => null,
            'active' => false,
        ],
        [
            'label' => 'Reservasi',
            'href' => null,
            'active' => false,
        ],
        [
            'label' => 'Berita',
            'href' => route('wisatawan.berita.index'),
            'active' => request()->routeIs(
                'wisatawan.berita.*'
            ),
        ],
        [
            'label' => 'Tentang',
            'href' => null,
            'active' => false,
        ],
        [
            'label' => 'Kontak',
            'href' => $berandaUrl . '#kontak',
            'active' => false,
        ],
    ];

    if ($isPublicHome) {
        // Di "/" selalu tampil sebagai halaman publik
        $accountRoute = 'login';
        $accountLabel = 'Masuk';
    } else {
        // Di halaman lain ikuti autentikasi pengguna
        $accountRoute = match (auth()->user()?->role) {
            'wisatawan' => 'wisatawan.profil',
            'petugas' => 'petugas.dashboard',
            'admin' => 'admin.dashboard',
            default => 'login',
        };

        $accountLabel = auth()->check()
            ? 'Profil / Akun'
            : 'Masuk';
    }
@endphp

<header class="relative z-30">
    <!-- Bar informasi atas -->
    <div class="bg-[#063A2B] text-[#DCE8E1]">
        <div class="mx-auto flex w-full max-w-[1180px] flex-wrap items-center justify-between gap-x-6 gap-y-1 px-4 py-2 text-[11px] sm:px-6 sm:text-xs lg:px-8">
            <span>Situs Resmi Pemerintah Kota Surabaya</span>
            <span>Jam operasional: 07.00–17.00 WIB</span>
        </div>
    </div>

    <!-- Navigasi utama -->
    <div class="border-b border-[#E6ECE7] bg-white">
        <div class="mx-auto flex min-h-[84px] w-full max-w-[1180px] items-center justify-between gap-5 px-4 py-3 sm:px-6 lg:px-8">
            <!-- Logo / identitas website -->
            <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-3" aria-label="Kebun Raya Mangrove Surabaya - Beranda">
                <svg class="h-11 w-11 text-[#064631]" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                    <circle cx="24" cy="24" r="22" stroke="#86AF99" stroke-width="1.5"/>
                    <path d="M14 32C13 20 24 13 35 13C35 26 28 34 18 34" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15 35C20 26 26 22 33 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
                <span class="leading-tight">
                    <span class="block text-[13px] font-extrabold tracking-[0.17em] text-[#063A2B] sm:text-sm">KEBUN RAYA</span>
                    <span class="mt-1 block text-[9px] tracking-[0.17em] text-[#305A4B] sm:text-[10px]">MANGROVE SURABAYA</span>
                </span>
            </a>

            <!-- Menu desktop -->
            <nav class="hidden items-center gap-5 lg:flex"
                aria-label="Navigasi utama">

                @foreach ($navItems as $item)

                    @if ($item['href'])
                        <a href="{{ $item['href'] }}"
                        @if($item['active'])
                            aria-current="page"
                        @endif
                        class="text-[13px] transition-all duration-200
                                hover:text-[#064631]
                                {{ $item['active']
                                    ? 'font-bold text-[#064631] underline decoration-2 underline-offset-[7px]'
                                    : 'font-normal text-[#466459]' }}">

                            {{ $item['label'] }}

                        </a>
                    @else
                        <span
                            class="cursor-not-allowed text-[13px] font-normal text-[#64766C]"
                            title="Halaman sedang dikembangkan">

                            {{ $item['label'] }}

                        </span>
                    @endif

                @endforeach

            </nav>

            <div class="flex shrink-0 items-center gap-3">
                <!-- Menu akun: login untuk tamu, profil/dashboard untuk pengguna login -->
                <a href="{{ route($accountRoute) }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold text-[#064631] hover:text-[#2F795B] sm:text-[13px]">
                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="7.5" r="3.5"/>
                        <path d="M5 20v-1.5a7 7 0 0 1 14 0V20"/>
                    </svg>
                    <span>{{ $accountLabel }}</span>
                </a>

                <!-- Menu mobile tanpa JavaScript -->
                <details class="relative lg:hidden">
                    <summary class="flex cursor-pointer list-none items-center gap-1 rounded border border-[#D7E3DA] px-3 py-2 text-xs font-semibold text-[#064631] [&::-webkit-details-marker]:hidden">
                        Menu
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <path d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                    </summary>
                    <nav class="absolute right-0 top-full z-50 mt-2 w-52 rounded border border-[#D7E3DA] bg-white py-2 shadow-lg"
                         aria-label="Navigasi seluler">
                        @foreach ($navItems as $item)
                        @if ($item['href'])
                            <a href="{{ $item['href'] }}"
                            @if($item['active'])
                                aria-current="page"
                            @endif
                            class="block px-4 py-2 text-sm
                                    {{ $item['active']
                                        ? 'bg-[#EEF4EE] font-bold text-[#064631] underline underline-offset-4'
                                        : 'font-normal text-[#466459] hover:bg-[#EEF4EE]' }}">

                                {{ $item['label'] }}

                            </a>
                        @else
                            <span
                                class="block cursor-not-allowed px-4 py-2 text-sm font-normal text-[#77857D]">

                                {{ $item['label'] }}

                            </span>
                        @endif

                    @endforeach
                    </nav>
                </details>
            </div>
        </div>
    </div>
</header>