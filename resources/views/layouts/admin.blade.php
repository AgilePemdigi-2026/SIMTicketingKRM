<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin - Kebun Raya Mangrove')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @font-face {
            font-family: 'Plus Jakarta Sans';
            src: url('/fonts/PlusJakartaSans.ttf') format('truetype');
            font-weight: 100 900;
            font-display: swap;
        }

        .admin-shell {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #F3F6F3;
            color: #183D32;
        }
    </style>

    @stack('styles')

</head>

<body class="admin-shell m-0">

@php
    $menuAdmin = [
        [
            'label' => 'Dashboard',
            'route' => 'admin.dashboard',
            'active' => 'admin.dashboard',
            'icon' => 'dashboard',
        ],
        [
            'label' => 'Transaksi',
            'route' => null,
            'active' => null,
            'icon' => 'transaction',
        ],
        [
            'label' => 'Monitoring Pendapatan',
            'route' => null,
            'active' => null,
            'icon' => 'chart',
        ],
        [
            'label' => 'Manajemen Tiket',
            'route' => 'admin.daftar-tiket.index',
            'active' => 'admin.daftar-tiket.*',
            'icon' => 'ticket',
        ],
        [
            'label' => 'Manajemen Wahana',
            'route' => 'admin.wahana.index',
            'active' => 'admin.wahana.*',
            'icon' => 'bike',
        ],
        [
            'label' => 'Manajemen Reservasi',
            'route' => 'admin.reservasi.preview',
            'active' => 'admin.reservasi.preview*',
            'icon' => 'calendar',
        ],
        [
            'label' => 'Manajemen Fasilitas',
            'route' => 'admin.fasilitas.index',
            'active' => 'admin.fasilitas.*',
            'icon' => 'building',
        ],
        [
            'label' => 'Manajemen Konten',
            'route' => 'admin.konten-berita.index',
            'active' => 'admin.konten-berita.*',
            'icon' => 'file',
        ],
        [
            'label' => 'Manajemen Pengguna',
            'route' => null,
            'active' => null,
            'icon' => 'users',
        ],
        [
            'label' => 'Laporan',
            'route' => 'admin.reports.index',
            'active' => 'admin.reports.*',
            'icon' => 'report',
        ],
        [
            'label' => 'Audit Log',
            'route' => 'admin.audit-log.index',
            'active' => 'admin.audit-log.*',
            'icon' => 'history',
        ],
    ];

    $adminName = auth()->user()?->name
        ?? auth()->user()?->email
        ?? 'Admin';

    $adminInitial = mb_strtoupper(
        mb_substr($adminName, 0, 1)
    );
@endphp

<div class="min-h-screen lg:flex">

    {{-- SIDEBAR --}}
    <aside class="flex w-full shrink-0 flex-col bg-[#0B372D] text-white lg:sticky lg:top-0 lg:h-screen lg:w-[clamp(220px,14.4vw,300px)]">

        {{-- BRAND --}}
        <div class="flex items-center justify-between gap-3 border-b border-white/15 px-5 py-5 lg:min-h-[108px] lg:py-6">

            <div class="flex items-center gap-3">

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#668F7E] text-[#C4D9CD]">

                    <x-admin-icon
                        name="leaf"
                        class="h-6 w-6"
                    />

                </div>

                <div>

                    <p class="text-[12px] font-bold tracking-[0.14em]">
                        KEBUN RAYA
                    </p>

                    <p class="mt-1 text-[9px] tracking-[0.12em] text-[#BBD0C3]">
                        MANGROVE SURABAYA
                    </p>

                    <p class="mt-2 text-[9px] tracking-[0.10em] text-[#82A397]">
                        PORTAL ADMINISTRASI
                    </p>

                </div>

            </div>

            {{-- MOBILE MENU --}}
            <details class="relative lg:hidden">

                <summary class="flex cursor-pointer list-none items-center gap-2 border border-white/30 px-3 py-2 text-xs [&::-webkit-details-marker]:hidden">

                    <x-admin-icon
                        name="menu"
                        class="h-4 w-4"
                    />

                    Menu

                </summary>

                <nav class="absolute right-0 top-full z-50 mt-2 w-64 border border-[#D8E3DB] bg-white p-2 text-[#183D32] shadow-lg">

                    @foreach ($menuAdmin as $menu)

                        @if ($menu['route'] && \Illuminate\Support\Facades\Route::has($menu['route']))

                            <a href="{{ route($menu['route']) }}"
                               class="flex items-center gap-3 px-3 py-2.5 text-xs {{ request()->routeIs($menu['active']) ? 'bg-[#E9F0EA] font-bold' : 'hover:bg-[#F3F6F3]' }}">

                                <x-admin-icon :name="$menu['icon']" class="h-4 w-4"/>

                                {{ $menu['label'] }}

                            </a>

                        @else

                            <span aria-disabled="true"
                                  title="Modul belum tersedia"
                                  class="flex items-center gap-3 px-3 py-2.5 text-xs opacity-50">

                                <x-admin-icon :name="$menu['icon']" class="h-4 w-4"/>

                                {{ $menu['label'] }}

                            </span>

                        @endif

                    @endforeach

                    <form method="POST"
                          action="{{ route('logout') }}"
                          class="mt-2 border-t border-[#D8E3DB] pt-2">

                        @csrf

                        <button type="submit"
                                class="flex w-full items-center gap-3 px-3 py-3 text-xs">

                            <x-admin-icon name="logout" class="h-4 w-4"/>

                            Logout

                        </button>

                    </form>

                </nav>

            </details>

        </div>

        {{-- DESKTOP NAVIGATION --}}
        <nav class="hidden flex-1 overflow-y-auto px-3 py-4 lg:block">

            @foreach ($menuAdmin as $menu)

                @if ($menu['route'] && \Illuminate\Support\Facades\Route::has($menu['route']))

                    <a href="{{ route($menu['route']) }}"
                       @if(request()->routeIs($menu['active'])) aria-current="page" @endif
                       class="flex min-h-[38px] items-center gap-3 border-l-[3px] px-3 py-2 text-[12px] transition
                       {{ request()->routeIs($menu['active'])
                           ? 'border-[#A9D17B] bg-[#245744] font-semibold text-white'
                           : 'border-transparent text-[#BBD0C3] hover:bg-white/10 hover:text-white' }}">

                        <x-admin-icon
                            :name="$menu['icon']"
                            class="h-[17px] w-[17px] shrink-0"
                        />

                        <span>{{ $menu['label'] }}</span>

                    </a>

                @else

                    <span aria-disabled="true"
                          title="Modul belum tersedia"
                          class="flex min-h-[38px] items-center gap-3 border-l-[3px] border-transparent px-3 py-2 text-[12px] text-[#8DADA0]">

                        <x-admin-icon
                            :name="$menu['icon']"
                            class="h-[17px] w-[17px] shrink-0"
                        />

                        <span>{{ $menu['label'] }}</span>

                    </span>

                @endif

            @endforeach

        </nav>

        {{-- DESKTOP LOGOUT --}}
        <div class="hidden p-3 lg:block">

            <form method="POST" action="{{ route('logout') }}">

                @csrf

                <button type="submit"
                        class="flex w-full items-center gap-3 border border-[#31594D] px-3 py-3 text-left text-xs text-[#BBD0C3] hover:bg-white/10">

                    <x-admin-icon
                        name="logout"
                        class="h-4 w-4"
                    />

                    Logout

                </button>

            </form>

        </div>

    </aside>

    {{-- MAIN AREA --}}
    <div class="min-w-0 flex-1">

        {{-- TOPBAR --}}
        <header class="flex min-h-[68px] items-center gap-4 border-b border-[#DCE5DE] bg-white px-4 lg:px-7">

            {{-- Global search preview --}}
            <div class="flex min-w-0 flex-1 items-center gap-3 border border-[#D7E2D9] bg-[#F3F6F3] px-4 py-3">

                <x-admin-icon
                    name="search"
                    class="h-4 w-4 shrink-0 text-[#174B38]"
                />

                <input
                    type="search"
                    disabled
                    aria-label="Pencarian global belum tersedia"
                    title="Pencarian global belum tersedia"
                    placeholder="Cari transaksi, tiket, pengunjung..."
                    class="min-w-0 flex-1 border-0 bg-transparent p-0 text-xs outline-none placeholder:text-[#82988B]"
                >

            </div>

            {{-- Notification placeholder --}}
            <button
                type="button"
                disabled
                aria-label="Notifikasi belum tersedia"
                title="Notifikasi belum tersedia"
                class="flex h-10 w-10 shrink-0 items-center justify-center border border-[#DCE5DE] text-[#174B38]"
            >

                <x-admin-icon name="bell" class="h-5 w-5"/>

            </button>

            {{-- PROFILE --}}
            <div class="flex shrink-0 items-center gap-3 border-l border-[#DCE5DE] pl-4">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#E8F1EA] text-base font-semibold text-[#174B38]">

                    {{ $adminInitial }}

                </div>

                <div class="hidden sm:block">

                    <p class="max-w-[150px] truncate text-xs font-semibold text-[#173D30]">

                        {{ $adminName }}

                    </p>

                    <p class="mt-1 text-[10px] text-[#819387]">

                        Administrator

                    </p>

                </div>

                <x-admin-icon
                    name="chevron"
                    class="hidden h-3 w-3 text-[#527A65] sm:block"
                />

            </div>

        </header>

        {{-- PAGE CONTENT --}}
        <main class="px-4 py-6 lg:px-7 lg:py-6">

            @yield('content')

        </main>

    </div>

</div>

@stack('scripts')

</body>

</html>