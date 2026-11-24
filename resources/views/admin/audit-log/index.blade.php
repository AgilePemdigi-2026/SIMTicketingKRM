@extends('layouts.admin')

@section('title', 'Audit Log - Kebun Raya Mangrove')

@section('content')
<div class="mx-auto max-w-[1500px]">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6 border-b border-[#D4DED7] pb-6">
        <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
            Administrasi Kebun Raya Mangrove
        </p>

        <h1 class="text-2xl font-semibold text-[#173D30]">
            Audit Log
        </h1>

        <p class="mt-2 text-sm text-[#74877B]">
            Monitoring histori aktivitas pengguna dan sistem.
        </p>
    </div>

    {{-- STATISTIK AKTIVITAS --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        @foreach ([
            [
                'label' => 'Total Aktivitas',
                'value' => $stats['total_aktivitas'],
                'description' => 'Seluruh histori aktivitas',
            ],
            [
                'label' => 'Aktivitas Hari Ini',
                'value' => $stats['aktivitas_hari_ini'],
                'description' => 'Berdasarkan tanggal WIB',
            ],
            [
                'label' => 'Aktivitas Pengguna',
                'value' => $stats['aktivitas_pengguna'],
                'description' => 'Admin, Petugas, dan Wisatawan',
            ],
            [
                'label' => 'Aktivitas Sistem',
                'value' => $stats['aktivitas_sistem'],
                'description' => 'Aktivitas otomatis sistem',
            ],
        ] as $stat)

            <div class="rounded-lg border border-[#DCE5DE] bg-white p-5">
                <p class="text-xs font-medium text-[#74877B]">
                    {{ $stat['label'] }}
                </p>

                <p class="mt-3 text-3xl font-bold text-[#173D30]">
                    {{ number_format($stat['value'], 0, ',', '.') }}
                </p>

                <p class="mt-2 text-[11px] leading-5 text-[#819187]">
                    {{ $stat['description'] }}
                </p>
            </div>

        @endforeach

    </div>

    {{-- INFORMASI READ ONLY --}}
    <div class="mb-6 flex items-start gap-3 border border-[#D5E5DA] bg-[#EFF6F0] px-4 py-3 text-xs leading-6 text-[#355D47]">
        <svg class="mt-1 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 11v5M12 7h.01" stroke-linecap="round"/>
        </svg>

        <p>
            Audit Log digunakan untuk monitoring histori aktivitas.
            Catatan tidak dapat diubah atau dihapus melalui halaman ini.
            Seluruh waktu ditampilkan dalam WIB.
        </p>
    </div>

    {{-- FILTER AKTIVITAS --}}
    <section class="mb-6 rounded-lg border border-[#DCE5DE] bg-white">

        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[#E2E9E4] px-5 py-5">

            <div>
                <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                    Filter Aktivitas
                </h2>

                <p class="mt-1 text-xs text-[#819187]">
                    Cari dan saring histori aktivitas pengguna dan sistem.
                </p>
            </div>

            @if ($logs->total() > 0 && ! $errors->any())
                <a
                    href="{{ route('admin.audit-log.export', $filters) }}"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-sm bg-[#174B38] px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-[#073B2D]"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M12 3v12m-5-5 5 5 5-5M4 16v4h16v-4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    Export Log
                </a>
            @endif

        </div>

        <form
            method="GET"
            action="{{ route('admin.audit-log.index') }}"
            class="p-5"
        >

            @if ($errors->any())
                <div class="mb-5 border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700" role="alert">
                    <p class="mb-2 font-semibold">
                        Filter tidak valid.
                    </p>

                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                {{-- CARI AKTIVITAS --}}
                <div>
                    <label for="search" class="mb-2 block text-xs font-semibold text-[#355D47]">
                        Cari Aktivitas
                    </label>

                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-3 h-4 w-4 text-[#819187]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m16 16 4 4" stroke-linecap="round"/>
                        </svg>

                        <input
                            type="search"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            maxlength="150"
                            placeholder="Cari aktivitas..."
                            class="h-11 w-full rounded-sm border border-[#CBD9CF] bg-white pl-9 pr-3 text-sm text-[#173D30] outline-none focus:border-[#527A65] focus:ring-1 focus:ring-[#527A65]"
                        >
                    </div>
                </div>

                {{-- FILTER MODUL --}}
                <div>
                    <label for="module" class="mb-2 block text-xs font-semibold text-[#355D47]">
                        Modul
                    </label>

                    <select
                        id="module"
                        name="module"
                        class="h-11 w-full rounded-sm border border-[#CBD9CF] bg-white px-3 text-sm text-[#173D30] outline-none focus:border-[#527A65] focus:ring-1 focus:ring-[#527A65]"
                    >
                        <option value="">Semua Modul</option>

                        @foreach ($modules as $module)
                            <option
                                value="{{ $module }}"
                                @selected(($filters['module'] ?? '') === $module)
                            >
                                {{ $module }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- TANGGAL AWAL --}}
                <div>
                    <label for="from" class="mb-2 block text-xs font-semibold text-[#355D47]">
                        Dari Tanggal
                    </label>

                    <input
                        type="date"
                        id="from"
                        name="from"
                        value="{{ $filters['from'] ?? '' }}"
                        class="h-11 w-full rounded-sm border border-[#CBD9CF] bg-white px-3 text-sm text-[#173D30] outline-none focus:border-[#527A65] focus:ring-1 focus:ring-[#527A65]"
                    >
                </div>

                {{-- TANGGAL AKHIR --}}
                <div>
                    <label for="until" class="mb-2 block text-xs font-semibold text-[#355D47]">
                        Sampai Tanggal
                    </label>

                    <input
                        type="date"
                        id="until"
                        name="until"
                        value="{{ $filters['until'] ?? '' }}"
                        class="h-11 w-full rounded-sm border border-[#CBD9CF] bg-white px-3 text-sm text-[#173D30] outline-none focus:border-[#527A65] focus:ring-1 focus:ring-[#527A65]"
                    >
                </div>

            </div>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-4">

                <p class="text-xs leading-5 text-[#819187]">
                    Filter tanggal menggunakan zona waktu WIB.
                    Statistik di atas menunjukkan seluruh aktivitas.
                </p>

                <div class="flex flex-wrap items-center gap-2">

                    <a
                        href="{{ route('admin.audit-log.index') }}"
                        class="inline-flex min-h-10 items-center justify-center border border-[#CBD9CF] px-4 py-2.5 text-xs font-semibold text-[#355D47] transition hover:bg-[#EAF2EC]"
                    >
                        Reset Filter
                    </a>

                    <button
                        type="submit"
                        class="inline-flex min-h-10 items-center justify-center bg-[#174B38] px-5 py-2.5 text-xs font-semibold text-white transition hover:bg-[#073B2D]"
                    >
                        Terapkan Filter
                    </button>

                </div>

            </div>

        </form>

    </section>

    {{-- HASIL FILTER --}}
    @if (! empty($filters))
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 border border-[#D5E5DA] bg-[#EFF6F0] px-4 py-3 text-xs text-[#355D47]">

            <p>
                Ditemukan
                <strong>{{ number_format($logs->total(), 0, ',', '.') }}</strong>
                aktivitas sesuai filter.
            </p>

            <a
                href="{{ route('admin.audit-log.index') }}"
                class="font-semibold underline"
            >
                Hapus semua filter
            </a>

        </div>
    @endif

    {{-- DAFTAR AKTIVITAS --}}
    <div class="overflow-hidden border border-[#DCE5DE] bg-white">

        <div class="border-b border-[#E2E9E4] px-5 py-5">
            <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                Daftar Aktivitas
            </h2>

            <p class="mt-1 text-xs text-[#819187]">
                Riwayat aktivitas terbaru ditampilkan terlebih dahulu.
            </p>
        </div>

        {{-- TABEL RESPONSIF --}}
        <div class="overflow-x-auto">

            <table class="w-full min-w-[1050px] border-collapse text-left text-xs">

                <thead class="bg-[#E9F0EB] text-[#4A695A]">
                    <tr class="uppercase tracking-[0.05em]">
                        <th class="px-4 py-4 font-bold">No.</th>
                        <th class="px-4 py-4 font-bold">Waktu (WIB)</th>
                        <th class="px-4 py-4 font-bold">Pengguna</th>
                        <th class="px-4 py-4 font-bold">Role</th>
                        <th class="px-4 py-4 font-bold">Modul</th>
                        <th class="px-4 py-4 font-bold">Aktivitas</th>
                        <th class="px-4 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">

                    @forelse ($logs as $log)

                        @php
                            $roleLabel = match ($log->actor_role) {
                                'admin' => 'Admin',
                                'petugas' => 'Petugas',
                                'wisatawan' => 'Wisatawan',
                                'system' => 'Sistem',
                                default => ucfirst($log->actor_role),
                            };

                            $actionLabel = match ($log->action) {
                                'login' => 'Login',
                                'logout' => 'Logout',
                                'payment_confirmed' => 'Konfirmasi Cash',
                                'qris_payment_confirmed' => 'Konfirmasi QRIS',
                                'ticket_checked_in' => 'Check-in E-Tiket',
                                'reservation_created' => 'Pembuatan Reservasi',
                                'reservation_payment_confirmed' => 'Pembayaran Reservasi',
                                'reschedule_requested' => 'Pengajuan Reschedule',
                                default => ucfirst(str_replace('_', ' ', $log->action)),
                            };
                        @endphp

                        <tr class="transition hover:bg-[#F8FAF8]">

                            <td class="px-4 py-4 text-[#819187]">
                                {{ $logs->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                <p class="font-medium text-[#173D30]">
                                    {{ $log->created_at?->timezone('Asia/Jakarta')->format('d/m/Y') ?? '-' }}
                                </p>

                                <p class="mt-1 text-[11px] text-[#819187]">
                                    {{ $log->created_at?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-' }} WIB
                                </p>
                            </td>

                            <td class="min-w-[150px] px-4 py-4 font-semibold text-[#173D30]">
                                {{ $log->actor_name }}
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-block whitespace-nowrap rounded-sm bg-[#E9F0EB] px-3 py-1.5 font-medium text-[#355D47]">
                                    {{ $roleLabel }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $log->module }}
                            </td>

                            <td class="min-w-[240px] px-4 py-4">
                                <p class="font-semibold text-[#173D30]">
                                    {{ $actionLabel }}
                                </p>

                                <p class="mt-1 text-[11px] leading-5 text-[#819187]">
                                    {{ $log->description }}
                                </p>
                            </td>

                            <td class="px-4 py-4">
                                <a
                                    href="{{ route('admin.audit-log.show', array_merge(['auditLog' => $log->id], $filters, ['page' => $logs->currentPage()])) }}"
                                    class="inline-flex min-h-9 items-center justify-center border border-[#CBD9CF] px-4 py-2 font-medium text-[#355D47] transition hover:bg-[#EAF2EC]"
                                >
                                    Detail
                                </a>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-4 py-14 text-center">
                                <p class="text-sm font-semibold text-[#173D30]">
                                    Tidak ada aktivitas ditemukan.
                                </p>

                                <p class="mt-2 text-xs text-[#819187]">
                                    @if (! empty($filters))
                                        Coba gunakan kata pencarian atau rentang tanggal yang berbeda.
                                    @else
                                        Histori aktivitas akan muncul setelah sistem mencatat aktivitas pengguna.
                                    @endif
                                </p>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>

    </div>

    {{-- PAGINATION --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-4">

        <p class="text-xs text-[#819187]">
            Menampilkan {{ $logs->firstItem() ?? 0 }}
            - {{ $logs->lastItem() ?? 0 }}
            dari {{ $logs->total() }} aktivitas
        </p>

        @if ($logs->hasPages())
            <div>
                {{ $logs->links('pagination::simple-tailwind') }}
            </div>
        @endif

    </div>

</div>
@endsection
