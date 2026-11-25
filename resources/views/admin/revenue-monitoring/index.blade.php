@extends('layouts.admin')

@section('title', 'Monitoring Pendapatan')

@section('content')
@php
    $summary = $summary ?? [];
    $period = $period ?? [];

    $from = request('from', $period['from'] ?? now('Asia/Jakarta')->startOfMonth()->toDateString());
    $until = request('until', $period['until'] ?? now('Asia/Jakarta')->toDateString());

    $selectedStatuses = request('statuses', []);
    $selectedMethods = request('methods', []);

    if (!is_array($selectedStatuses)) $selectedStatuses = [];
    if (!is_array($selectedMethods)) $selectedMethods = [];

    $statusOptions = [
        'paid' => 'Lunas',
        'pending' => 'Menunggu Pembayaran',
        'cancelled' => 'Dibatalkan',
        'expired' => 'Kedaluwarsa',
    ];

    $methodOptions = [
        'qris' => 'QRIS',
        'cash' => 'Cash',
    ];

    $exportQuery = request()->query();
@endphp

<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-xl font-bold text-gray-800">
                Monitoring Pendapatan
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Ringkasan transaksi, pendapatan, dan pengunjung.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('admin.revenue-monitoring.export.excel', $exportQuery) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2.5 text-sm font-semibold text-emerald-600 hover:bg-emerald-700"
            >
                <span>↓</span>
                Export Excel
            </a>

            <a
                href="{{ route('admin.revenue-monitoring.export.pdf', $exportQuery) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"
            >
                <span>↓</span>
                Export PDF
            </a>
        </div>
    </div>

    {{-- MAIN LAYOUT --}}
    <div class="grid grid-cols-1 items-start gap-5 xl:grid-cols-[270px_minmax(0,1fr)]">

        {{-- FILTER PANEL --}}
        <aside class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <form
                action="{{ route('admin.revenue-monitoring.index') }}"
                method="GET"
                id="revenue-filter-form"
                class="space-y-5"
            >
                <div class="flex items-center justify-between">
                    <h4 class="font-bold text-gray-800">Filter Data</h4>

                    <a
                        href="{{ route('admin.revenue-monitoring.index') }}"
                        class="text-xs font-semibold text-emerald-700 hover:text-emerald-800"
                    >
                        Reset
                    </a>
                </div>

                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Rentang Tanggal
                    </p>

                    <div class="grid grid-cols-3 gap-1 rounded-lg bg-gray-100 p-1">
                        <button type="button" data-period="day"
                            class="period-button rounded-md px-2 py-2 text-xs font-semibold text-gray-600 hover:bg-white">
                            Hari
                        </button>
                        <button type="button" data-period="week"
                            class="period-button rounded-md px-2 py-2 text-xs font-semibold text-gray-600 hover:bg-white">
                            Minggu
                        </button>
                        <button type="button" data-period="month"
                            class="period-button rounded-md px-2 py-2 text-xs font-semibold text-gray-600 hover:bg-white">
                            Bulan
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label for="filter-from" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Dari
                        </label>
                        <input
                            type="date"
                            name="from"
                            id="filter-from"
                            value="{{ $from }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        >
                    </div>

                    <div>
                        <label for="filter-until" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Sampai
                        </label>
                        <input
                            type="date"
                            name="until"
                            id="filter-until"
                            value="{{ $until }}"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100"
                        >
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-sm font-bold text-gray-800">Status Pembayaran</p>
                        <button type="button" id="select-all-status"
                            class="text-xs font-medium text-emerald-700">
                            Semua
                        </button>
                    </div>

                    <div class="space-y-3">
                        @foreach($statusOptions as $value => $label)
                            <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-600">
                                <input
                                    type="checkbox"
                                    name="statuses[]"
                                    value="{{ $value }}"
                                    @checked(in_array($value, $selectedStatuses))
                                    class="status-checkbox h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                >
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-4">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-sm font-bold text-gray-800">Metode Pembayaran</p>
                        <button type="button" id="select-all-methods"
                            class="text-xs font-medium text-emerald-700">
                            Semua
                        </button>
                    </div>

                    <div class="space-y-3">
                        @foreach($methodOptions as $value => $label)
                            <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-600">
                                <input
                                    type="checkbox"
                                    name="methods[]"
                                    value="{{ $value }}"
                                    @checked(in_array($value, $selectedMethods))
                                    class="method-checkbox h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                >
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700"
                >
                    Terapkan Filter
                </button>
            </form>
        </aside>

        {{-- DASHBOARD --}}
        <section class="min-w-0 space-y-5">

            {{-- KPI CARDS --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <span class="text-lg">▤</span>
                    </div>
                    <p class="text-xs font-medium text-gray-500">Total Transaksi</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ number_format($summary['total_transaksi'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                        <span class="text-lg">▣</span>
                    </div>
                    <p class="text-xs font-medium text-gray-500">Total Tiket</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ number_format($summary['total_tiket'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <span class="text-lg">Rp</span>
                    </div>
                    <p class="text-xs font-medium text-gray-500">Total Pendapatan</p>
                    <p class="mt-1 break-words text-xl font-bold text-emerald-700">
                        Rp {{ number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600">
                        <span class="text-lg">×</span>
                    </div>
                    <p class="text-xs font-medium text-gray-500">Transaksi Batal</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ number_format($summary['transaksi_batal'] ?? $summary['transaksi_dibatalkan'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <span class="text-lg">◷</span>
                    </div>
                    <p class="text-xs font-medium text-gray-500">Belum Dibayar</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">
                        {{ number_format($summary['transaksi_belum_dibayar'] ?? $summary['transaksi_unpaid'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>

            </div>

            {{-- TWO CHARTS --}}
            <div class="grid grid-cols-1 gap-5 2xl:grid-cols-2">

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h4 class="font-bold text-gray-800">Tren Pendapatan</h4>
                        <p class="mt-1 text-xs text-gray-500">
                            Periode {{ $from }} sampai {{ $until }}
                        </p>
                    </div>
                    <div class="relative h-72">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h4 class="font-bold text-gray-800">Perbandingan Metode Pembayaran</h4>
                        <p class="mt-1 text-xs text-gray-500">
                            Periode {{ $from }} sampai {{ $until }}
                        </p>
                    </div>
                    <div class="relative h-72">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- FULL WIDTH CHART --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h4 class="font-bold text-gray-800">
                        Statistik Pengunjung & Pendapatan
                    </h4>
                    <p class="mt-1 text-xs text-gray-500">
                        Jumlah pengunjung dan pendapatan berdasarkan tanggal kunjungan.
                    </p>
                </div>

                <div class="relative h-80">
                    <canvas id="visitorRevenueChart"></canvas>
                </div>
            </div>

        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    .period-button.active {
        background: white;
        color: #047857;
        box-shadow: 0 1px 3px rgba(0,0,0,.10);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('revenue-filter-form');
    const fromInput = document.getElementById('filter-from');
    const untilInput = document.getElementById('filter-until');

    // Preset tanggal berdasarkan kalender Asia/Jakarta.
    const jakartaNow = new Date(
        new Date().toLocaleString('en-US', { timeZone: 'Asia/Jakarta' })
    );

    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };

    document.querySelectorAll('.period-button').forEach(button => {
        button.addEventListener('click', function () {
            const end = new Date(jakartaNow);
            const start = new Date(jakartaNow);

            if (this.dataset.period === 'week') {
                const day = start.getDay();
                start.setDate(start.getDate() - ((day + 6) % 7));
            }

            if (this.dataset.period === 'month') {
                start.setDate(1);
            }

            fromInput.value = formatDate(start);
            untilInput.value = formatDate(end);

            document.querySelectorAll('.period-button').forEach(item => {
                item.classList.remove('active');
            });

            this.classList.add('active');
        });
    });

    document.getElementById('select-all-status').addEventListener('click', () => {
        document.querySelectorAll('.status-checkbox').forEach(item => {
            item.checked = false;
        });
    });

    document.getElementById('select-all-methods').addEventListener('click', () => {
        document.querySelectorAll('.method-checkbox').forEach(item => {
            item.checked = false;
        });
    });

    form.addEventListener('submit', function (event) {
        if (fromInput.value > untilInput.value) {
            event.preventDefault();
            alert('Tanggal awal tidak boleh melebihi tanggal akhir.');
        }
    });

    const rupiah = value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value);

    const dailyRevenue = @json($daily_revenue ?? []);
    const paymentMethods = @json($payment_methods ?? []);
    const dailyVisitors = @json($daily_visitors ?? []);

    const revenueLabels = dailyRevenue.map(item => item.date);
    const revenueValues = dailyRevenue.map(item => Number(item.revenue || 0));

    new Chart(document.getElementById('revenueTrendChart'), {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Pendapatan',
                data: revenueValues,
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.12)',
                fill: true,
                tension: 0.35,
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => rupiah(context.raw)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => rupiah(value)
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    const methodLabels = Object.keys(paymentMethods).map(
        key => key.toUpperCase()
    );

    const methodValues = Object.values(paymentMethods).map(
        item => Number(item.pendapatan || 0)
    );

    new Chart(document.getElementById('paymentMethodChart'), {
        type: 'bar',
        data: {
            labels: methodLabels,
            datasets: [{
                label: 'Pendapatan',
                data: methodValues,
                backgroundColor: ['#059669', '#3B82F6'],
                borderRadius: 7,
                maxBarThickness: 65
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: context => rupiah(context.raw)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => rupiah(value)
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    const visitorMap = new Map(
        dailyVisitors.map(item => [
            item.date,
            Number(item.visitors || 0)
        ])
    );

    const visitorLabels = dailyRevenue.map(item => item.date);
    const visitorValues = visitorLabels.map(
        date => visitorMap.get(date) || 0
    );

    new Chart(document.getElementById('visitorRevenueChart'), {
        type: 'line',
        data: {
            labels: visitorLabels,
            datasets: [
                {
                    label: 'Pengunjung',
                    data: visitorValues,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                    yAxisID: 'y',
                    tension: 0.35,
                    borderWidth: 2
                },
                {
                    label: 'Pendapatan',
                    data: revenueValues,
                    borderColor: '#059669',
                    backgroundColor: 'rgba(5, 150, 105, 0.08)',
                    yAxisID: 'y1',
                    tension: 0.35,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: context => context.dataset.label === 'Pendapatan'
                            ? rupiah(context.raw)
                            : context.dataset.label + ': ' + context.raw
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Jumlah Pengunjung'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        callback: value => rupiah(value)
                    },
                    title: {
                        display: true,
                        text: 'Pendapatan'
                    }
                }
            }
        }
    });
});
</script>
@endpush
