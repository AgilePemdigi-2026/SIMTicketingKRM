<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Admin - Kebun Raya Mangrove')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body class="m-0 bg-[#F5F7F5] font-sans text-[#183D32]">

<div class="min-h-screen lg:flex">

    {{-- SIDEBAR ADMIN --}}
    <aside class="flex w-full shrink-0 flex-col bg-[#073B2D] text-white lg:sticky lg:top-0 lg:h-screen lg:w-[250px]">

        {{-- Logo --}}
        <div class="flex items-center gap-3 border-b border-white/15 px-6 py-8">
            <div class="flex h-12 w-12 items-center justify-center rounded-full border border-white/40 text-2xl">
                ♧
            </div>

            <div>
                <p class="text-sm font-bold tracking-[0.14em]">
                    KEBUN RAYA
                </p>
                <p class="mt-1 text-[10px] tracking-[0.14em] text-white/75">
                    MANGROVE SURABAYA
                </p>
                <p class="mt-2 text-[9px] tracking-[0.13em] text-white/45">
                    PORTAL ADMINISTRASI
                </p>
            </div>
        </div>

        {{-- Navigasi --}}
        <nav class="flex-1 space-y-1 px-3 py-6">

            @php
                $menuAdmin = [
                    [
                        'label' => 'Dashboard',
                        'route' => 'admin.dashboard',
                        'active' => 'admin.dashboard',
                    ],
                    [
                        'label' => 'Manajemen Tiket',
                        'route' => 'admin.daftar-tiket.index',
                        'active' => 'admin.daftar-tiket.*',
                    ],
                    [
                        'label' => 'Manajemen Wahana',
                        'route' => 'admin.wahana.index',
                        'active' => 'admin.wahana.*',
                    ],
                    [
                        'label' => 'Manajemen Fasilitas',
                        'route' => 'admin.fasilitas.index',
                        'active' => 'admin.fasilitas.*',
                    ],
                    [
                        'label' => 'Manajemen Konten',
                        'route' => 'admin.konten-berita.index',
                        'active' => 'admin.konten-berita.*',
                    ],
                ];
            @endphp

            @foreach ($menuAdmin as $menu)
                <a
                    href="{{ route($menu['route']) }}"
                    class="block border-l-[3px] px-4 py-3 text-[13px] transition
                        {{ request()->routeIs($menu['active'])
                            ? 'border-[#A9D17B] bg-[#245C49] font-bold text-white'
                            : 'border-transparent text-white/70 hover:bg-white/10 hover:text-white' }}"
                >
                    {{ $menu['label'] }}
                </a>
            @endforeach

            {{-- Menu yang belum tersedia --}}
            <div class="border-t border-white/15 pt-4">
                <p class="px-4 pb-2 text-[10px] uppercase tracking-wider text-white/45">
                    Modul Berikutnya
                </p>

                @foreach ([
                    'Transaksi',
                    'Monitoring Pendapatan',
                    'Manajemen Reservasi',
                    'Manajemen Pengguna',
                    'Laporan',
                    'Audit Log',
                ] as $label)
                    <div class="px-4 py-2 text-[12px] text-white/40">
                        {{ $label }}
                    </div>
                @endforeach
            </div>

        </nav>

        {{-- Logout --}}
        <div class="border-t border-white/15 p-4">
            @if (\Illuminate\Support\Facades\Route::has('logout'))
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="w-full border border-white/20 px-4 py-3 text-left text-[13px] text-white/80 transition hover:bg-white/10"
                    >
                        ← Logout
                    </button>
                </form>
            @endif
        </div>

    </aside>

    {{-- AREA UTAMA --}}
    <div class="min-w-0 flex-1">

        {{-- TOPBAR --}}
        <header class="flex min-h-[76px] items-center justify-between gap-4 border-b border-[#DCE5DE] bg-white px-5 lg:px-8">

            <div class="text-sm text-[#799084]">
                Portal Administrasi Kebun Raya Mangrove
            </div>

            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#E8F3EB] font-bold text-[#174B38]">
                    A
                </div>

                <div class="hidden sm:block">
                    <p class="text-sm font-bold text-[#193B31]">
                        {{ auth()->user()?->name ?? auth()->user()?->email ?? 'Admin' }}
                    </p>
                    <p class="text-[11px] text-[#899B90]">
                        Administrator
                    </p>
                </div>
            </div>

        </header>

        {{-- KONTEN HALAMAN --}}
        <main class="px-5 py-8 lg:px-8 lg:py-10">

            @yield('content')

        </main>

    </div>
</div>

@stack('scripts')

</body>
</html>