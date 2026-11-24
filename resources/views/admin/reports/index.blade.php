@extends('layouts.admin')

@section('title', 'Laporan Operasional - Kebun Raya Mangrove')

@section('content')

@php

    $exportFilters = [
        'period' => 'custom',
        'from' => $period['from'],
        'until' => $period['until'],
    ];

    $categories = [
        [
            'key' => 'ticket-sales',
            'title' => 'Laporan Penjualan Tiket',
            'description' => 'Rekap penjualan tiket per periode',
            'icon' => 'ticket',
        ],
        [
            'key' => 'visitors',
            'title' => 'Laporan Pengunjung',
            'description' => 'Data kunjungan dan tren pengunjung',
            'icon' => 'users',
        ],
        [
            'key' => 'revenue',
            'title' => 'Laporan Pendapatan',
            'description' => 'Rekap total pendapatan per periode',
            'icon' => 'chart',
        ],
        [
            'key' => 'cash',
            'title' => 'Laporan Transaksi Cash',
            'description' => 'Rekap pembayaran tunai lunas',
            'icon' => 'transaction',
        ],
        [
            'key' => 'qris',
            'title' => 'Laporan Transaksi QRIS',
            'description' => 'Transaksi pembayaran digital QRIS',
            'icon' => 'transaction',
        ],
        [
            'key' => 'reservations',
            'title' => 'Laporan Reservasi',
            'description' => 'Data reservasi fasilitas & venue',
            'icon' => 'calendar',
        ],
        [
            'key' => 'attractions',
            'title' => 'Laporan Pemakaian Wahana',
            'description' => 'Statistik penggunaan wahana',
            'icon' => 'bike',
        ],
        [
            'key' => 'checkins',
            'title' => 'Laporan Check-in',
            'description' => 'Rekap validasi kedatangan pengunjung',
            'icon' => 'users',
        ],
        [
            'key' => 'cancellations',
            'title' => 'Laporan Pembatalan',
            'description' => 'Data transaksi dan reservasi batal',
            'icon' => 'file',
        ],
    ];

    $presets = [
        'daily' => 'Hari Ini',
        'weekly' => '7 Hari Terakhir',
        'monthly' => 'Bulan Ini',
    ];

@endphp

<div class="mx-auto max-w-[1800px]">

    {{-- PAGE HEADER --}}
    <header class="mb-4 border-b border-[#B8C9BD] pb-2">

        <div class="flex flex-wrap items-start justify-between gap-3">

            <div>

                <p class="text-[10px] font-semibold uppercase tracking-[0.12em] text-[#527A65]">

                    Administrasi Kebun Raya Mangrove

                </p>

                <h1 class="mt-2 text-[24px] font-medium leading-tight text-[#0B372D]">

                    Laporan

                </h1>

            </div>

            <div class="flex flex-wrap gap-2">

                <a
                    href="{{ route('admin.reports.export.excel', $exportFilters) }}"
                    class="inline-flex min-h-10 items-center justify-center border border-[#174B38] bg-white px-4 py-2.5 text-[11px] font-semibold text-[#174B38] hover:bg-[#E9F0EB]"
                >

                    Export Excel

                </a>

                <a
                    href="{{ route('admin.reports.export.pdf', $exportFilters) }}"
                    class="inline-flex min-h-10 items-center justify-center border border-[#174B38] bg-white px-4 py-2.5 text-[11px] font-semibold text-[#174B38] hover:bg-[#E9F0EB]"
                >

                    Export PDF

                </a>

            </div>

        </div>

        <p class="mt-4 text-[11px] text-[#74877B]">

            Unduh dan tinjau laporan operasional

        </p>

    </header>

    {{-- PERIOD FILTER --}}
    <section class="mb-3 border border-[#D8E2DA] bg-white p-4">

        @if ($errors->any())

            <div class="mb-3 border border-red-200 bg-red-50 p-3 text-xs text-red-700" role="alert">

                <p class="font-semibold">

                    Periode laporan tidak valid.

                </p>

                <ul class="mt-2 list-disc space-y-1 pl-4">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif

        <div class="flex flex-wrap items-end gap-3">

            <form
                id="report-period-form"
                method="GET"
                action="{{ route('admin.reports.index') }}"
                class="flex flex-wrap items-end gap-3"
            >

                <input type="hidden" name="period" value="custom">

                <div class="w-full sm:w-[225px]">

                    <label
                        for="from"
                        class="mb-1.5 block text-[11px] font-semibold text-[#405A4D]"
                    >

                        Dari Tanggal

                    </label>

                    <input
                        type="date"
                        id="from"
                        name="from"
                        value="{{ $period['from'] }}"
                        required
                        class="h-10 w-full border border-[#CBD9CF] bg-white px-3 text-[11px] text-[#173D30] outline-none focus:border-[#527A65]"
                    >

                </div>

                <div class="w-full sm:w-[225px]">

                    <label
                        for="until"
                        class="mb-1.5 block text-[11px] font-semibold text-[#405A4D]"
                    >

                        Sampai Tanggal

                    </label>

                    <input
                        type="date"
                        id="until"
                        name="until"
                        value="{{ $period['until'] }}"
                        required
                        class="h-10 w-full border border-[#CBD9CF] bg-white px-3 text-[11px] text-[#173D30] outline-none focus:border-[#527A65]"
                    >

                </div>



                    <button
                        type="submit"
                        class="border border-[#174B38] bg-[#174B38] px-3 py-2 text-xs text-white"
                    >

                        Terapkan

                    </button>



            </form>

            <div class="flex flex-wrap items-center gap-1.5">

                @foreach ($presets as $key => $label)

                    <a
                        href="{{ route('admin.reports.index', ['period' => $key]) }}"
                        @if ($filters['period'] === $key) aria-current="true" @endif
                        class="inline-flex min-h-7 items-center justify-center border border-[#D8E2DA] px-3 py-1.5 text-[10px]
                        {{ $filters['period'] === $key
                            ? 'bg-[#174B38] text-white'
                            : 'bg-white text-[#405A4D] hover:bg-[#E9F0EB]' }}"
                    >

                        {{ $label }}

                    </a>

                @endforeach

            </div>

        </div>

    </section>

    {{-- NINE REPORT CATEGORIES --}}
    <section
        class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3"
        aria-label="Kategori Laporan"
    >

        @foreach ($categories as $category)

            <article
                class="flex min-h-[120px] min-w-0 items-start gap-3.5 border border-[#D8E2DA] bg-white p-[18px]"
            >

                <div class="flex h-10 w-10 shrink-0 items-center justify-center bg-[#E9F0EB] text-[#174B38]">

                    <x-admin-icon
                        :name="$category['icon']"
                        class="h-5 w-5"
                    />

                </div>

                <div class="min-w-0 flex-1">

                    <h2 class="text-[12px] font-medium leading-5 text-[#173D30]">

                        {{ $category['title'] }}

                    </h2>

                    <p class="text-[10px] leading-4 text-[#74877B]">

                        {{ $category['description'] }}

                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-1">

                        @if (array_key_exists($category['key'], \App\Services\CategorizedReportService::CATEGORIES))
                            @foreach (['admin.reports.index' => 'Lihat', 'admin.reports.export.excel' => 'Excel', 'admin.reports.export.pdf' => 'PDF'] as $route => $label)
                                <a href="{{ route($route, array_merge($exportFilters, ['category' => $category['key']])) }}"
                                   class="border border-[#D8E2DA] px-2.5 py-1.5 text-[10px] {{ $label === 'Lihat' ? 'bg-[#174B38] text-white' : 'bg-white text-[#405A4D]' }} hover:underline">
                                    {{ $label }}
                                </a>
                            @endforeach
                        @else
                        <button
                            type="button"
                            disabled
                            title="Laporan kategori sedang dikembangkan"
                            class="border border-[#174B38] bg-[#174B38] px-2.5 py-1.5 text-[10px] text-white disabled:cursor-not-allowed disabled:opacity-60"
                        >

                            Lihat

                        </button>

                        <button
                            type="button"
                            disabled
                            title="Export kategori sedang dikembangkan"
                            class="border border-[#D8E2DA] bg-white px-2.5 py-1.5 text-[10px] text-[#405A4D] disabled:cursor-not-allowed disabled:opacity-60"
                        >

                            Excel

                        </button>

                        <button
                            type="button"
                            disabled
                            title="Export kategori sedang dikembangkan"
                            class="border border-[#D8E2DA] bg-white px-2.5 py-1.5 text-[10px] text-[#405A4D] disabled:cursor-not-allowed disabled:opacity-60"
                        >

                            PDF

                        </button>

                            <p class="w-full text-[10px] text-[#74877B]">Sumber data belum tersedia.</p>
                        @endif
                    </div>

                </div>

            </article>

        @endforeach

    </section>

    {{-- EXISTING COMBINED REPORT --}}
    <div class="mt-4">

        <a
            href="{{ route('admin.reports.index', array_merge($exportFilters, ['view' => 'detail'])) }}"
            class="inline-flex items-center gap-2 border border-[#174B38] bg-white px-3 py-2 text-[11px] font-semibold text-[#174B38] hover:bg-[#E9F0EB]"
        >

            Lihat Laporan Operasional Lengkap

            <x-admin-icon name="arrow" class="h-3 w-3"/>

        </a>

    </div>

</div>

@endsection

@push('scripts')

<script>
    (() => {
        const form = document.getElementById('report-period-form');

        if (!form) {
            return;
        }

        const from = form.querySelector('#from');
        const until = form.querySelector('#until');

        const validateDates = () => {
            until.setCustomValidity('');

            if (!from.value || !until.value) {
                return false;
            }

            if (until.value < from.value) {
                until.setCustomValidity(
                    'Tanggal akhir tidak boleh lebih awal dari tanggal awal.'
                );

                until.reportValidity();
                return false;
            }

            const days =
                (Date.parse(until.value) - Date.parse(from.value))
                / 86400000 + 1;

            if (days > 366) {
                until.setCustomValidity(
                    'Periode laporan maksimal 366 hari.'
                );

                until.reportValidity();
                return false;
            }

            return form.checkValidity();
        };

        [from, until].forEach(input => {
            input.addEventListener('change', () => {
                if (validateDates()) {
                    form.requestSubmit();
                }
            });
        });

        form.addEventListener('submit', event => {
            if (!validateDates()) {
                event.preventDefault();
            }
        });
    })();
</script>

@endpush
