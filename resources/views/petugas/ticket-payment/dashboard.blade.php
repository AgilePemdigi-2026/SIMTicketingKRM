@extends('layouts.petugas')

@section('title', 'Dashboard Operasional - KRM')

@push('styles')
<style>
    .dashboard-page {
        padding: 32px 24px 60px;
        color: #183d30;
    }

    .dashboard-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
    }

    .dashboard-title {
        margin: 0;
        font-size: 30px;
        font-weight: 700;
        color: #153e30;
    }

    .dashboard-subtitle {
        margin-top: 8px;
        font-size: 14px;
        color: #7a8b82;
    }

    .dashboard-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .dashboard-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        min-height: 44px;
        background: #fff;
        border: 1px solid #07513d;
        color: #07513d;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
    }

    .dashboard-button.primary {
        background: #07513d;
        color: #fff;
    }

    .dashboard-divider {
        border-top: 1px solid #a9bfb0;
        margin: 22px 0 24px;
    }

    /* Kartu statistik */
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .dashboard-stat-card {
        background: #fff;
        border: 1px solid #d6e1d8;
        padding: 20px;
        min-width: 0;
    }

    .dashboard-stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .dashboard-stat-label {
        font-size: 13px;
        color: #72857b;
        line-height: 1.5;
    }

    .dashboard-stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 42px;
        background: #edf4ec;
        color: #267554;
        font-size: 22px;
        font-weight: 600;
    }

    .dashboard-stat-number {
        margin-top: 12px;
        font-size: 32px;
        font-weight: 700;
        color: #083d2e;
    }

    .dashboard-stat-description {
        min-height: 34px;
        margin-top: 8px;
        font-size: 11px;
        line-height: 1.5;
        color: #81938a;
    }

    /* Pintasan layanan */
    .dashboard-shortcuts {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 34px;
    }

    .dashboard-shortcut {
        display: flex;
        align-items: center;
        gap: 16px;
        min-height: 96px;
        padding: 20px;
        background: #fff;
        border: 1px solid #d6e1d8;
        color: #153e30;
        text-decoration: none;
        transition: background .2s;
    }

    .dashboard-shortcut:hover {
        background: #eef5ef;
    }

    .dashboard-shortcut-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50px;
        height: 50px;
        flex-shrink: 0;
        background: #edf4ec;
        color: #247454;
    }

    .dashboard-shortcut-icon svg {
        width: 24px;
        height: 24px;
        stroke: currentColor;
        fill: none;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .dashboard-shortcut-text {
        flex: 1;
        min-width: 0;
    }

    .dashboard-shortcut-title {
        font-size: 15px;
        font-weight: 700;
    }

    .dashboard-shortcut-desc {
        margin-top: 5px;
        font-size: 12px;
        color: #7b8e83;
    }

    .dashboard-shortcut-arrow {
        font-size: 24px;
        color: #247454;
    }

    /* Aktivitas pengunjung */
    .dashboard-section-title {
        margin: 0 0 16px;
        font-size: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #153e30;
    }

    .dashboard-table-wrap {
        overflow-x: auto;
        border: 1px solid #d6e1d8;
    }

    .dashboard-table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
        font-size: 13px;
    }

    .dashboard-table th {
        background: #eaf0eb;
        padding: 15px 14px;
        text-align: left;
        text-transform: uppercase;
        color: #456459;
        font-size: 12px;
        font-weight: 700;
        border-right: 1px solid #d6e1d8;
    }

    .dashboard-table td {
        padding: 16px 14px;
        border-top: 1px solid #e0e8e1;
        border-right: 1px solid #e0e8e1;
        background: #fff;
        color: #26483a;
    }

    .dashboard-table th:last-child,
    .dashboard-table td:last-child {
        border-right: none;
    }

    .dashboard-status {
        display: inline-block;
        padding: 7px 12px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid;
        white-space: nowrap;
    }

    .dashboard-status.success {
        color: #246b48;
        background: #e7f4e9;
        border-color: #c9dfce;
    }

    .dashboard-status.neutral {
        color: #52665b;
        background: #eef1ef;
        border-color: #d7dfd9;
    }

    .dashboard-status.pending {
        color: #916000;
        background: #fff4dc;
        border-color: #eed49b;
    }

    .dashboard-status.danger {
        color: #9b3838;
        background: #fdecec;
        border-color: #e9c5c5;
    }

    .dashboard-table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 16px;
        font-size: 13px;
        color: #668377;
    }

    .dashboard-more {
        font-weight: 600;
        color: #176148;
        text-decoration: underline;
    }

    @media (max-width: 1150px) {
        .dashboard-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .dashboard-shortcuts {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .dashboard-page {
            padding: 24px 16px 40px;
        }

        .dashboard-title {
            font-size: 25px;
        }

        .dashboard-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-page">

    {{-- Header Dashboard --}}
    <div class="dashboard-heading">
        <div>
            <h1 class="dashboard-title">Dashboard Operasional</h1>

            <p class="dashboard-subtitle">
                {{ $nowWib->locale('id')->translatedFormat('l, d F Y') }}
                · Ringkasan Operasional Hari Ini
            </p>
        </div>

        <div class="dashboard-actions">
            <a href="{{ route('petugas.loket.index') }}"
               class="dashboard-button primary">
                + Jual Tiket
            </a>

            <a href="{{ route('petugas.validasi-tiket.index') }}"
               class="dashboard-button">
                Scan Tiket
            </a>
        </div>
    </div>

    <div class="dashboard-divider"></div>

    {{-- Lima Statistik Operasional --}}
    <div class="dashboard-stats">

        <div class="dashboard-stat-card">
            <div class="dashboard-stat-top">
                <span class="dashboard-stat-label">Pengunjung Hari Ini</span>
                <span class="dashboard-stat-icon">♙</span>
            </div>

            <div class="dashboard-stat-number">
                {{ number_format($visitorsToday, 0, ',', '.') }}
            </div>

            <div class="dashboard-stat-description">
                E-Tiket terbit untuk kunjungan hari ini
            </div>

        </div>

        <div class="dashboard-stat-card">
            <div class="dashboard-stat-top">
                <span class="dashboard-stat-label">Sudah Check-In</span>
                <span class="dashboard-stat-icon">✓</span>
            </div>

            <div class="dashboard-stat-number">
                {{ number_format($checkedInToday, 0, ',', '.') }}
            </div>

            <div class="dashboard-stat-description">
                E-Tiket yang digunakan hari ini
            </div>

        </div>

        <div class="dashboard-stat-card">
            <div class="dashboard-stat-top">
                <span class="dashboard-stat-label">Cash Booking Belum Bayar</span>
                <span class="dashboard-stat-icon">Rp</span>
            </div>

            <div class="dashboard-stat-number">
                {{ number_format($pendingCashBookings, 0, ',', '.') }}
            </div>

            <div class="dashboard-stat-description">
                Booking online Cash yang masih pending
            </div>

        </div>

        <div class="dashboard-stat-card">
            <div class="dashboard-stat-top">
                <span class="dashboard-stat-label">Transaksi Hari Ini</span>
                <span class="dashboard-stat-icon">▤</span>
            </div>

            <div class="dashboard-stat-number">
                {{ number_format($transactionsToday, 0, ',', '.') }}
            </div>

            <div class="dashboard-stat-description">
                Transaksi tiket yang dibuat hari ini
            </div>

        </div>

        <div class="dashboard-stat-card">
            <div class="dashboard-stat-top">
                <span class="dashboard-stat-label">Reservasi Hari Ini</span>
                <span class="dashboard-stat-icon">◷</span>
            </div>

            <div class="dashboard-stat-number">
                {{ number_format($reservationsToday, 0, ',', '.') }}
            </div>

            <div class="dashboard-stat-description">
                Reservasi fasilitas terbayar untuk hari ini
            </div>

        </div>

    </div>

    {{-- Pintasan Layanan --}}
    <div class="dashboard-shortcuts">

        <a href="{{ route('petugas.loket.index') }}"
           class="dashboard-shortcut">
            <span class="dashboard-shortcut-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M4 8.5V6a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2.5a2.5 2.5 0 0 0 0 5V18a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4.5a2.5 2.5 0 0 0 0-5Z"/>
                    <path d="M9 9h6"/>
                    <path d="M9 12h6"/>
                    <path d="M9 15h3"/>
                </svg>
            </span>

            <div class="dashboard-shortcut-text">
                <div class="dashboard-shortcut-title">
                    Jual Tiket Langsung
                </div>
                <div class="dashboard-shortcut-desc">
                    Pengunjung walk-in tanpa booking
                </div>
            </div>

            <span class="dashboard-shortcut-arrow">›</span>
        </a>

        <a href="{{ route('ticket-payment.staff.index') }}"
           class="dashboard-shortcut">
            <span class="dashboard-shortcut-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <rect x="3" y="6" width="18" height="12" rx="2"/>
                    <circle cx="12" cy="12" r="2.5"/>
                    <path d="M7 10h.01"/>
                    <path d="M17 14h.01"/>
                </svg>
            </span>

            <div class="dashboard-shortcut-text">
                <div class="dashboard-shortcut-title">
                    Bayar Cash Booking
                </div>
                <div class="dashboard-shortcut-desc">
                    Booking online, bayar tunai di loket
                </div>
            </div>

            <span class="dashboard-shortcut-arrow">›</span>
        </a>

        <a href="{{ route('petugas.validasi-tiket.index') }}"
           class="dashboard-shortcut">
            <span class="dashboard-shortcut-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <path d="M4 8V5a1 1 0 0 1 1-1h3"/>
                    <path d="M16 4h3a1 1 0 0 1 1 1v3"/>
                    <path d="M20 16v3a1 1 0 0 1-1 1h-3"/>
                    <path d="M8 20H5a1 1 0 0 1-1-1v-3"/>
                    <path d="M9 9h6"/>
                    <path d="M9 12h6"/>
                    <path d="M9 15h6"/>
                </svg>
            </span>

            <div class="dashboard-shortcut-text">
                <div class="dashboard-shortcut-title">
                    Scan / Validasi Tiket
                </div>
                <div class="dashboard-shortcut-desc">
                    QR Code check-in pengunjung
                </div>
            </div>

            <span class="dashboard-shortcut-arrow">›</span>
        </a>

    </div>

    {{-- Aktivitas Pengunjung Hari Ini --}}
    <section>
        <h2 class="dashboard-section-title">
            Aktivitas Pengunjung Hari Ini
        </h2>

        <div class="dashboard-table-wrap">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Nama Pengunjung</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Jenis Tiket</th>
                        <th>Status Tiket</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($activitiesToday as $activity)
                        @php
                            $status = $activity['status'];

                            $statusClass = match ($status) {
                                'Sudah Check-In' => 'success',
                                'Belum Digunakan' => 'neutral',
                                'Menunggu Pembayaran' => 'pending',
                                'Gagal', 'Kedaluwarsa' => 'danger',
                                default => 'neutral',
                            };
                        @endphp

                        <tr>
                            <td>
                                {{ $activity['name'] ?: '-' }}
                            </td>

                            <td>
                                {{ $activity['visit_date']?->locale('id')->translatedFormat('d M Y') ?? '-' }}
                            </td>

                            <td>
                                {{ $activity['ticket_name'] ?: '-' }}
                            </td>

                            <td>
                                <span class="dashboard-status {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"
                                style="text-align:center; padding:32px; color:#7b8f87;">
                                Belum ada aktivitas pengunjung untuk hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="dashboard-table-footer">
            <span>
                Menampilkan {{ $activitiesToday->count() }}
                dari {{ $activityTotal }} pengunjung terjadwal hari ini
            </span>

            <a href="{{ route('petugas.riwayat-transaksi.index') }}"
               class="dashboard-more">
                Lihat Semua →
            </a>
        </div>
    </section>

</div>
@endsection
