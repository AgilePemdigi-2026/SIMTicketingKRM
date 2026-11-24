@extends('layouts.petugas')

@section('title', 'Riwayat Transaksi')

@section('content')
<style>
    .history-page { padding: 32px 24px 60px; }
    .history-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:20px; }
    .history-title { font-size: 32px; font-weight: 700; color:#163b31; margin:0; }
    .history-subtitle { margin-top:8px; color:#7b8f87; font-size:14px; }
    .history-line { border-top:1px solid #cfdcd3; margin:18px 0 24px; }

    .history-btn {
        display:inline-flex; align-items:center; justify-content:center;
        min-height:48px; padding:0 22px;
        border:1px solid #0b5a43; background:#fff; color:#0b5a43;
        font-weight:600; text-decoration:none;
    }

    .history-card {
        border:1px solid #d5e0d8;
        background:#fff;
        padding:24px;
        margin-bottom:24px;
    }

    .history-filter-grid {
        display:grid;
        grid-template-columns: 1.1fr 1fr 1fr 1fr 1fr;
        gap:14px;
        align-items:end;
    }

    .history-filter-buttons {
        display:flex;
        gap:8px;
    }

    .history-pill {
        min-height:48px;
        padding:0 16px;
        border:1px solid #cfdcd3;
        background:#fff;
        color:#23473d;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size:14px;
    }

    .history-pill.active {
        background:#0b5a43;
        border-color:#0b5a43;
        color:#fff;
    }

    .history-field label {
        display:block;
        font-size:13px;
        color:#4a665c;
        margin-bottom:8px;
        font-weight:600;
    }

    .history-control {
        width:100%;
        min-height:50px;
        border:1px solid #cfdcd3;
        background:#fff;
        padding:0 14px;
        color:#26463b;
        font-size:14px;
    }

    .history-tabs {
        display:flex;
        gap:18px;
        border-bottom:1px solid #d9e3dc;
        margin-bottom:16px;
    }

    .history-tab {
        text-decoration:none;
        padding:10px 0 12px;
        color:#5c736b;
        font-weight:600;
        border-bottom:3px solid transparent;
    }

    .history-tab.active {
        color:#0b5a43;
        border-color:#84b65f;
    }

    .history-table-wrap {
        overflow:auto;
    }

    .history-table {
        width:100%;
        border-collapse:collapse;
    }

    .history-table th, .history-table td {
        border:1px solid #d9e3dc;
        padding:14px 12px;
        text-align:left;
        vertical-align:middle;
        font-size:14px;
    }

    .history-table th {
        background:#f5f8f5;
        color:#4b665e;
        font-size:13px;
        font-weight:700;
        text-transform:uppercase;
    }

    .history-badge {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:30px;
        padding:0 12px;
        font-size:13px;
        font-weight:600;
        border:1px solid;
    }

    .history-badge.paid {
        background:#ebf5ec;
        border-color:#cbe1ce;
        color:#246b43;
    }

    .history-badge.pending {
        background:#fff5df;
        border-color:#ecd494;
        color:#996c00;
    }

    .history-badge.failed {
        background:#fdecec;
        border-color:#efc4c4;
        color:#a34848;
    }

    .history-action {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:38px;
        padding:0 16px;
        border:1px solid #bfd0c5;
        background:#fff;
        color:#2d5b4d;
        text-decoration:none;
        font-size:14px;
    }

    .history-footer {
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:16px;
        margin-top:18px;
        color:#7b8f87;
        font-size:14px;
    }

    .history-pagination .pagination {
        margin:0;
    }

    @media (max-width: 1100px) {
        .history-filter-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .history-head {
            flex-direction:column;
            align-items:stretch;
        }
        .history-filter-grid {
            grid-template-columns: 1fr;
        }
    }

        /* Sembunyikan ringkasan bawaan pagination Laravel */
    .history-pagination nav > div:nth-child(2) > div:first-child {
        display: none !important;
    }

    /* Pertahankan tombol pagination di sebelah kanan */
    .history-pagination nav > div:nth-child(2) {
        justify-content: flex-end !important;
    }
</style>

<div class="history-page">
    <div class="history-head">
        <div>
            <h1 class="history-title">Riwayat Transaksi</h1>
            <div class="history-subtitle">{{ $todayCount }} transaksi hari ini</div>
        </div>

        <a href="{{ route('petugas.riwayat-transaksi.export', request()->except('page')) }}"
        class="history-btn">
            Export
        </a>
    </div>

    <div class="history-line"></div>

    <div class="history-card">
        @if ($errors->any())
            <div style="padding:12px 16px; margin-bottom:16px;
                        background:#fff4e5; border:1px solid #e7c98b;
                        color:#825600; font-size:14px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        <form method="GET" action="{{ route('petugas.riwayat-transaksi.index') }}">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="hidden" name="period" id="history-period" value="{{ $period }}">

            <div class="history-filter-grid">
                <div class="history-field">
                    <label>Periode</label>
                    <div class="history-filter-buttons">
                        <a href="{{ route('petugas.riwayat-transaksi.index', array_merge(request()->except('page'), ['period' => 'hari-ini', 'tab' => $tab])) }}"
                           class="history-pill {{ $period === 'hari-ini' ? 'active' : '' }}">
                            Hari Ini
                        </a>
                        <a href="{{ route('petugas.riwayat-transaksi.index', array_merge(request()->except('page'), ['period' => 'kemarin', 'tab' => $tab])) }}"
                           class="history-pill {{ $period === 'kemarin' ? 'active' : '' }}">
                            Kemarin
                        </a>
                        <button type="button"
                                id="history-custom-button"
                                class="history-pill {{ $period === 'custom' ? 'active' : '' }}">
                            Custom
                        </button>
                    </div>
                </div>

                <div class="history-field">
                    <label for="history-from">Dari</label>
                    <input type="date"
                        id="history-from"
                        class="history-control"
                        name="from"
                        value="{{ request('from') }}">
                </div>

                <div class="history-field">
                    <label for="history-until">Sampai</label>
                    <input type="date"
                        id="history-until"
                        class="history-control"
                        name="until"
                        value="{{ request('until') }}">
                </div>

                <div class="history-field">
                    <label>Metode Bayar</label>
                    <select class="history-control" name="method">
                        <option value="semua" {{ request('method') === 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="qris" {{ request('method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    </select>
                </div>

                <div class="history-field">
                    <label>Status</label>
                    <select class="history-control" name="status">
                        <option value="semua" {{ request('status') === 'semua' ? 'selected' : '' }}>Semua</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:16px;">
                <button type="submit" class="history-btn">Terapkan Filter</button>
            </div>
        </form>
    </div>

    <div class="history-tabs">
        <a href="{{ route('petugas.riwayat-transaksi.index', array_merge(request()->except('page', 'tab'), ['tab' => 'semua'])) }}"
           class="history-tab {{ $tab === 'semua' ? 'active' : '' }}">Semua</a>

        <a href="{{ route('petugas.riwayat-transaksi.index', array_merge(request()->except('page', 'tab'), ['tab' => 'loket'])) }}"
           class="history-tab {{ $tab === 'loket' ? 'active' : '' }}">Loket</a>

        <a href="{{ route('petugas.riwayat-transaksi.index', array_merge(request()->except('page', 'tab'), ['tab' => 'online'])) }}"
           class="history-tab {{ $tab === 'online' ? 'active' : '' }}">Online</a>
    </div>

    <div class="history-table-wrap">
        <table class="history-table">
            <thead>
                <tr>
                    <th>No. Transaksi</th>
                    <th>Nama Pengunjung</th>
                    <th>Jenis Tiket</th>
                    <th>Metode</th>
                    <th>Total</th>
                    <th>Status Pembayaran</th>
                    <th>Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    @php
                        $order = $payment->order;
                        $statusClass = match($payment->status) {
                            'paid' => 'paid',
                            'pending' => 'pending',
                            'failed', 'expired' => 'failed',
                            default => 'pending',
                        };

                        $statusLabel = match($payment->status) {
                            'paid' => 'Lunas',
                            'pending' => 'Menunggu Pembayaran',
                            'failed' => 'Gagal',
                            'expired' => 'Kedaluwarsa',
                            default => ucfirst($payment->status),
                        };

                        $channelText = $order?->sale_channel === 'counter' ? 'Loket' : 'Online';
                        $methodText = strtoupper($payment->method) === 'CASH'
                            ? ($channelText === 'Loket' ? 'Cash' : 'Cash · Online')
                            : 'QRIS';
                    @endphp
                    <tr>
                        <td>{{ $order?->code ?? '-' }}</td>
                        <td>{{ $order?->booker_name ?? '-' }}</td>
                        <td>{{ $order?->items?->pluck('ticket_name')->filter()->unique()->implode(', ') ?: '-' }}</td>
                        <td>{{ $methodText }}</td>
                        <td>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="history-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            {{ $payment->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
                        </td>
                        <td>
                            <a href="{{ route('petugas.riwayat-transaksi.show', $payment->id) }}" class="history-action">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; color:#7b8f87;">
                            Belum ada data transaksi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="history-footer">
        <div>
            Menampilkan {{ $payments->firstItem() ?? 0 }}&ndash;{{ $payments->lastItem() ?? 0 }} dari {{ $payments->total() }} transaksi
        </div>

        <div class="history-pagination">
            {{ $payments->links() }}
        </div>
    </div>
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const periodInput = document.getElementById('history-period');
    const customButton = document.getElementById('history-custom-button');
    const fromInput = document.getElementById('history-from');
    const untilInput = document.getElementById('history-until');

    function updateCustomFilter() {
        const isCustom = periodInput.value === 'custom';

        fromInput.required = isCustom;
        untilInput.required = isCustom;

        untilInput.min = isCustom ? fromInput.value : '';

        customButton.classList.toggle('active', isCustom);
    }

    customButton.addEventListener('click', function () {
        periodInput.value = 'custom';

        document.querySelectorAll('.history-filter-buttons .history-pill')
            .forEach(button => button.classList.remove('active'));

        updateCustomFilter();
        fromInput.focus();
    });

    fromInput.addEventListener('change', updateCustomFilter);

    updateCustomFilter();
});
</script>
@endpush
@endsection