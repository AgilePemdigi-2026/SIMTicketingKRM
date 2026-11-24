@extends('layouts.admin')

@section('title', 'Audit Log - Kebun Raya Mangrove')

@section('content')

<div class="mx-auto max-w-[1800px]">

    {{-- PAGE HEADER --}}
    <header class="mb-4 border-b border-[#B8C9BD] pb-2">

        <div class="flex flex-wrap items-start justify-between gap-3">

            <div>

                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#527A65]">

                    Administrasi Kebun Raya Mangrove

                </p>

                <h1 class="mt-2 text-[24px] font-medium leading-tight text-[#0B372D]">

                    Audit Log

                </h1>

            </div>

            @if ($logs->total() > 0 && ! $errors->any())

                <a
                    href="{{ route('admin.audit-log.export', $filters) }}"
                    class="inline-flex min-h-10 items-center justify-center border border-[#174B38] bg-white px-4 py-2.5 text-[11px] font-semibold text-[#174B38] transition hover:bg-[#E9F0EB]"
                >

                    Export Log

                </a>

            @endif

        </div>

        <p class="mt-4 text-[11px] text-[#74877B]">

            Jejak aktivitas administrator dan petugas

        </p>

    </header>

    {{-- FILTER PANEL --}}
    <section class="mb-3 border border-[#D8E2DA] bg-white p-4">

        <form
            id="audit-log-filter"
            method="GET"
            action="{{ route('admin.audit-log.index') }}"
        >

            @if ($errors->any())

                <div
                    class="mb-4 border border-red-200 bg-red-50 p-3 text-xs text-red-700"
                    role="alert"
                >

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

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

                {{-- KEYWORD --}}
                <div class="min-w-0">

                    <label
                        for="search"
                        class="mb-1.5 block text-[10px] font-semibold text-[#405A4D]"
                    >

                        Cari Aktivitas

                    </label>

                    <div class="relative">

                        <input
                            id="search"
                            name="search"
                            type="search"
                            value="{{ $filters['search'] ?? '' }}"
                            maxlength="150"
                            placeholder="Keyword aktivitas..."
                            class="h-10 w-full border border-[#CBD9CF] bg-white px-3 pr-8 text-[11px] text-[#173D30] outline-none placeholder:text-[#74877B] focus:border-[#527A65]"
                        >

                        <button
                            type="submit"
                            aria-label="Cari aktivitas"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-[#527A65]"
                        >

                            <x-admin-icon
                                name="chevron"
                                class="h-3 w-3"
                            />

                        </button>

                    </div>

                </div>

                {{-- MODULE --}}
                <div class="min-w-0">

                    <label
                        for="module"
                        class="mb-1.5 block text-[10px] font-semibold text-[#405A4D]"
                    >

                        Modul

                    </label>

                    <select
                        id="module"
                        name="module"
                        class="h-10 w-full border border-[#CBD9CF] bg-white px-3 text-[11px] text-[#173D30] outline-none focus:border-[#527A65]"
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

                {{-- START DATE --}}
                <div class="min-w-0">

                    <label
                        for="from"
                        class="mb-1.5 block text-[10px] font-semibold text-[#405A4D]"
                    >

                        Dari Tanggal

                    </label>

                    <input
                        type="date"
                        id="from"
                        name="from"
                        value="{{ $filters['from'] ?? '' }}"
                        class="h-10 w-full border border-[#CBD9CF] bg-white px-3 text-[11px] text-[#173D30] outline-none focus:border-[#527A65]"
                    >

                </div>

                {{-- END DATE --}}
                <div class="min-w-0">

                    <label
                        for="until"
                        class="mb-1.5 block text-[10px] font-semibold text-[#405A4D]"
                    >

                        Sampai Tanggal

                    </label>

                    <input
                        type="date"
                        id="until"
                        name="until"
                        value="{{ $filters['until'] ?? '' }}"
                        class="h-10 w-full border border-[#CBD9CF] bg-white px-3 text-[11px] text-[#173D30] outline-none focus:border-[#527A65]"
                    >

                </div>

            </div>

            <noscript>

                <button
                    type="submit"
                    class="mt-3 border border-[#174B38] px-4 py-2 text-xs font-semibold text-[#174B38]"
                >

                    Terapkan Filter

                </button>

            </noscript>

        </form>

    </section>

    {{-- FILTER RESET --}}
    @if (! empty($filters))

        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 text-[10px] text-[#74877B]">

            <p>

                Ditemukan {{ number_format($logs->total(), 0, ',', '.') }} aktivitas sesuai filter.

            </p>

            <a
                href="{{ route('admin.audit-log.index') }}"
                class="font-semibold text-[#174B38] underline"
            >

                Reset Filter

            </a>

        </div>

    @endif

    {{-- ACTIVITY TABLE --}}
    <section class="overflow-hidden border border-[#D8E2DA] bg-white">

        <div tabindex="0" role="region" aria-label="Tabel data yang dapat digeser" class="min-w-0 max-w-full overflow-x-auto focus-visible:outline-2 focus-visible:outline-[#527A65]">

            <table class="w-full min-w-[950px] table-fixed border-collapse text-left">

                <colgroup>
                    <col style="width:15%">
                    <col style="width:21%">
                    <col style="width:15%">
                    <col style="width:11%">
                    <col style="width:9%">
                    <col style="width:29%">
                </colgroup>

                <thead class="bg-[#E9F0EB] text-[#355D47]">

                    <tr class="text-[10px] uppercase">

                        <th scope="col" class="border-b border-r border-[#D8E2DA] px-3 py-3 font-semibold">
                            Pengguna
                        </th>

                        <th scope="col" class="border-b border-r border-[#D8E2DA] px-3 py-3 font-semibold">
                            Aktivitas
                        </th>

                        <th scope="col" class="border-b border-r border-[#D8E2DA] px-3 py-3 font-semibold">
                            Modul
                        </th>

                        <th scope="col" class="border-b border-r border-[#D8E2DA] px-3 py-3 font-semibold">
                            Tanggal
                        </th>

                        <th scope="col" class="border-b border-r border-[#D8E2DA] px-3 py-3 font-semibold">
                            Waktu
                        </th>

                        <th scope="col" class="border-b border-[#D8E2DA] px-3 py-3 font-semibold">
                            Keterangan
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-[#E0E8E1] text-[11px] leading-4 text-[#405A4D]">

                    @forelse ($logs as $log)

                        @php

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

                            $roleLabel = match ($log->actor_role) {
                                'admin' => 'Admin',
                                'petugas' => 'Petugas',
                                'wisatawan' => 'Wisatawan',
                                'system' => 'Sistem',
                                default => ucfirst($log->actor_role),
                            };

                            $timestamp = $log->created_at
                                ?->timezone('Asia/Jakarta');

                            $detailUrl = route(
                                'admin.audit-log.show',
                                array_merge(
                                    ['auditLog' => $log->id],
                                    $filters,
                                    ['page' => $logs->currentPage()]
                                )
                            );

                        @endphp

                        <tr class="transition hover:bg-[#F3F6F3]">

                            {{-- USER --}}
                            <td class="break-words border-r border-[#E0E8E1] px-3 py-2.5">

                                <a
                                    href="{{ $detailUrl }}"
                                    title="Lihat detail aktivitas - {{ $roleLabel }}"
                                    class="text-[#405A4D] hover:text-[#174B38] hover:underline focus:underline"
                                >

                                    {{ $log->actor_name }}

                                </a>

                            </td>

                            {{-- ACTIVITY --}}
                            <td class="break-words border-r border-[#E0E8E1] px-3 py-2.5">

                                <a
                                    href="{{ $detailUrl }}"
                                    title="Lihat detail aktivitas"
                                    class="hover:text-[#174B38] hover:underline focus:underline"
                                >

                                    {{ $actionLabel }}

                                </a>

                            </td>

                            {{-- MODULE --}}
                            <td class="break-words border-r border-[#E0E8E1] px-3 py-2.5">

                                {{ $log->module }}

                            </td>

                            {{-- DATE --}}
                            <td class="whitespace-nowrap border-r border-[#E0E8E1] px-3 py-2.5">

                                {{ $timestamp?->locale('id')->translatedFormat('d M Y') ?? '-' }}

                            </td>

                            {{-- TIME --}}
                            <td class="whitespace-nowrap border-r border-[#E0E8E1] px-3 py-2.5">

                                {{ $timestamp?->format('H:i') ?? '-' }} WIB

                            </td>

                            {{-- DESCRIPTION --}}
                            <td class="break-words px-3 py-2.5">

                                {{ $log->description }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="px-4 py-12 text-center">

                                <p class="text-xs font-semibold text-[#173D30]">

                                    Tidak ada aktivitas ditemukan.

                                </p>

                                <p class="mt-2 text-[11px] text-[#819187]">

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

    </section>

    {{-- PAGINATION --}}
    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">

        <p class="text-[10px] text-[#74877B]">

            Menampilkan {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }}
            dari {{ number_format($logs->total(), 0, ',', '.') }} log aktivitas

        </p>

        @if ($logs->hasPages())

            @php

                $current = $logs->currentPage();
                $last = $logs->lastPage();

                $firstPage = max(1, min($current - 1, $last - 2));
                $lastVisiblePage = min($last, $firstPage + 2);

                $pageClass = 'flex h-8 w-8 items-center justify-center border border-[#CBD9CF] bg-white text-[11px] text-[#527A65] transition hover:bg-[#E9F0EB]';

                $disabledClass = 'flex h-8 w-8 items-center justify-center border border-[#CBD9CF] bg-white text-[11px] text-[#B8C9BD]';

            @endphp

            <nav class="flex items-center gap-px" aria-label="Halaman Audit Log">

                {{-- PREVIOUS --}}
                @if ($logs->onFirstPage())

                    <span class="{{ $disabledClass }}" aria-disabled="true">

                        &#8592;

                    </span>

                @else

                    <a
                        href="{{ $logs->previousPageUrl() }}"
                        class="{{ $pageClass }}"
                        aria-label="Halaman sebelumnya"
                        rel="prev"
                    >

                        &#8592;

                    </a>

                @endif

                {{-- PAGE NUMBERS --}}
                @for ($page = $firstPage; $page <= $lastVisiblePage; $page++)

                    @if ($page === $current)

                        <span
                            aria-current="page"
                            class="flex h-8 w-8 items-center justify-center border border-[#174B38] bg-[#174B38] text-[11px] text-white"
                        >

                            {{ $page }}

                        </span>

                    @else

                        <a
                            href="{{ $logs->url($page) }}"
                            aria-label="Halaman {{ $page }}"
                            class="{{ $pageClass }}"
                        >

                            {{ $page }}

                        </a>

                    @endif

                @endfor

                {{-- NEXT --}}
                @if ($logs->hasMorePages())

                    <a
                        href="{{ $logs->nextPageUrl() }}"
                        class="{{ $pageClass }}"
                        aria-label="Halaman berikutnya"
                        rel="next"
                    >

                        &#8594;

                    </a>

                @else

                    <span class="{{ $disabledClass }}" aria-disabled="true">

                        &#8594;

                    </span>

                @endif

            </nav>

        @endif

    </div>

</div>

@endsection

@push('scripts')

<script>
    (() => {
        const form = document.getElementById('audit-log-filter');

        if (!form) {
            return;
        }

        const search = form.querySelector('[name="search"]');
        const module = form.querySelector('[name="module"]');
        const from = form.querySelector('[name="from"]');
        const until = form.querySelector('[name="until"]');

        let timer;

        const submitFilters = () => {
            clearTimeout(timer);
            form.requestSubmit();
        };

        const validateDates = () => {
            until.setCustomValidity('');

            if (from.value && until.value && until.value < from.value) {
                until.setCustomValidity(
                    'Tanggal akhir tidak boleh lebih awal dari tanggal awal.'
                );

                until.reportValidity();
                return false;
            }

            return true;
        };

        search.addEventListener('input', () => {
            clearTimeout(timer);

            timer = setTimeout(() => {
                if (validateDates()) {
                    submitFilters();
                }
            }, 1000);
        });

        module.addEventListener('change', () => {
            if (validateDates()) {
                submitFilters();
            }
        });

        [from, until].forEach(input => {
            input.addEventListener('change', () => {
                if (validateDates()) {
                    submitFilters();
                }
            });
        });

        form.addEventListener('submit', event => {
            clearTimeout(timer);

            if (!validateDates()) {
                event.preventDefault();
            }
        });
    })();
</script>

@endpush
