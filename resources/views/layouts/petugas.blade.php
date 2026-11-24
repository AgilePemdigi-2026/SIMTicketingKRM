<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Portal Petugas - Kebun Raya Mangrove')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="min-h-screen bg-[#F5F8F6] text-[#183D30]">

    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:p-3">
        Lewati ke konten
    </a>

    <header class="relative z-40 bg-[#063A2B] text-white shadow-sm">
        <div class="mx-auto flex min-h-[72px] w-full max-w-[1600px] items-center justify-between gap-4 px-4 lg:px-8">

            {{-- Identitas Portal --}}
            <a href="{{ route('petugas.dashboard') }}"
               class="flex shrink-0 items-center gap-3">

                <span class="flex h-10 w-10 items-center justify-center rounded-full border border-[#8FB9A1]">
                    <svg width="28" height="28"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.7"
                         stroke-linecap="round"
                         stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="M20 4c-9 0-15 4-15 12a4 4 0 0 0 4 4c8 0 12-6 11-16Z"/>
                        <path d="M5 20c3-6 7-9 12-12"/>
                    </svg>
                </span>

                <span class="leading-tight">
                    <span class="block text-sm font-extrabold tracking-[0.12em]">
                        KRM
                    </span>
                    <span class="block text-[10px] tracking-[0.18em] text-[#B5CFC2]">
                        PETUGAS
                    </span>
                </span>
            </a>

            {{-- Navigasi Desktop --}}
            <nav class="hidden h-full items-center gap-1 xl:flex"
                 aria-label="Navigasi Petugas">

                <a href="{{ route('petugas.dashboard') }}"
                   @if(request()->routeIs('petugas.dashboard')) aria-current="page" @endif
                   class="border-b-[3px] px-4 py-6 text-sm transition
                   {{ request()->routeIs('petugas.dashboard')
                       ? 'border-[#A3CA84] bg-white/10 font-semibold text-white'
                       : 'border-transparent text-[#C5D7CC] hover:bg-white/10 hover:text-white' }}">
                    Dashboard
                </a>

                <a href="{{ route('petugas.loket.index') }}"
                   @if(request()->routeIs('petugas.loket.*')) aria-current="page" @endif
                   class="border-b-[3px] px-4 py-6 text-sm transition
                   {{ request()->routeIs('petugas.loket.*')
                       ? 'border-[#A3CA84] bg-white/10 font-semibold text-white'
                       : 'border-transparent text-[#C5D7CC] hover:bg-white/10 hover:text-white' }}">
                    Jual Tiket Langsung
                </a>

                <a href="{{ route('ticket-payment.staff.index') }}"
                   @if(request()->routeIs('ticket-payment.staff.*')) aria-current="page" @endif
                   class="border-b-[3px] px-4 py-6 text-sm transition
                   {{ request()->routeIs('ticket-payment.staff.*')
                       ? 'border-[#A3CA84] bg-white/10 font-semibold text-white'
                       : 'border-transparent text-[#C5D7CC] hover:bg-white/10 hover:text-white' }}">
                    Bayar Cash Booking
                </a>

                <a href="{{ route('petugas.validasi-tiket.index') }}"
                   @if(request()->routeIs('petugas.validasi-tiket.*')) aria-current="page" @endif
                   class="border-b-[3px] px-4 py-6 text-sm transition
                   {{ request()->routeIs('petugas.validasi-tiket.*')
                       ? 'border-[#A3CA84] bg-white/10 font-semibold text-white'
                       : 'border-transparent text-[#C5D7CC] hover:bg-white/10 hover:text-white' }}">
                    Scan Tiket
                </a>

                {{-- Riwayat Transaksi --}}
                <a href="{{ route('petugas.riwayat-transaksi.index') }}"
                @if(request()->routeIs('petugas.riwayat-transaksi.*')) aria-current="page" @endif
                class="border-b-[3px] px-4 py-6 text-sm transition
                {{ request()->routeIs('petugas.riwayat-transaksi.*')
                    ? 'border-[#A3CA84] bg-white/10 font-semibold text-white'
                    : 'border-transparent text-[#C5D7CC] hover:bg-white/10 hover:text-white' }}">
                    Riwayat Transaksi
                </a>
            </nav>

            {{-- Informasi Akun --}}
            <div class="flex shrink-0 items-center gap-3">

                <div class="hidden text-right md:block">
                    <p class="text-xs font-semibold text-white">
                        {{ now()->timezone('Asia/Jakarta')->format('H:i') }} WIB
                    </p>
                    <p class="mt-1 text-[11px] text-[#B5CFC2]">
                        {{ now()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y') }}
                    </p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#2B7456] text-sm font-semibold text-white"
                     title="{{ auth()->user()?->name }}">
                    {{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'P', 0, 1)) }}
                </div>

                {{-- Navigasi Mobile --}}
                <details class="relative xl:hidden">
                    <summary class="flex cursor-pointer list-none items-center rounded border border-white/30 px-3 py-2 text-xs font-semibold [&::-webkit-details-marker]:hidden">
                        Menu
                        <svg class="ml-2 h-4 w-4"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             aria-hidden="true">
                            <path d="M4 7h16M4 12h16M4 17h16"/>
                        </svg>
                    </summary>

                    <nav class="absolute right-0 top-full z-50 mt-3 w-60 rounded border border-[#D7E3DA] bg-white p-2 text-[#183D30] shadow-xl"
                         aria-label="Navigasi Petugas Mobile">

                        <a href="{{ route('petugas.dashboard') }}"
                           class="block rounded px-3 py-3 text-sm hover:bg-[#EEF4EE]">
                            Dashboard
                        </a>

                        <a href="{{ route('petugas.loket.index') }}"
                           class="block rounded px-3 py-3 text-sm hover:bg-[#EEF4EE]">
                            Jual Tiket Langsung
                        </a>

                        <a href="{{ route('ticket-payment.staff.index') }}"
                           class="block rounded px-3 py-3 text-sm hover:bg-[#EEF4EE]">
                            Bayar Cash Booking
                        </a>

                        <a href="{{ route('petugas.validasi-tiket.index') }}"
                           class="block rounded px-3 py-3 text-sm hover:bg-[#EEF4EE]">
                            Scan Tiket
                        </a>

                        <a href="{{ route('petugas.riwayat-transaksi.index') }}"
                        @if(request()->routeIs('petugas.riwayat-transaksi.*')) aria-current="page" @endif
                        class="block rounded px-3 py-3 text-sm hover:bg-[#EEF4EE]
                        {{ request()->routeIs('petugas.riwayat-transaksi.*')
                            ? 'bg-[#EEF4EE] font-semibold text-[#07513D]'
                            : '' }}">
                            Riwayat Transaksi
                        </a>
                    </nav>
                </details>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="rounded border border-white/30 px-3 py-2 text-xs font-medium text-white transition hover:bg-white/10">
                        Keluar
                    </button>
                </form>

            </div>
        </div>
    </header>

    <main id="main-content" class="min-h-[calc(100vh-72px)]">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
