@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Kebun Raya Mangrove Surabaya')

@push('styles')
    @include('wisatawan.ticket-purchase.styles')

    <style>
        /* Styling khusus Riwayat Pesanan */
        .history-tabs {
            display: flex;
            gap: 0;
            margin-top: 44px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--color-border);
        }

        .history-tab {
            display: inline-block;
            padding: 12px 24px 15px;
            font-size: 15px;
            color: var(--color-muted);
        }

        .history-tab-active {
            color: var(--color-primary);
            font-weight: 600;
            border-bottom: 2px solid var(--color-primary);
        }

        .history-order-card {
            padding: 24px 18px 18px;
            margin-bottom: 12px;
        }

        .history-order-grid {
            display: grid;
            grid-template-columns:
                minmax(0, 1.45fr)
                minmax(0, 1.05fr)
                minmax(0, 1fr)
                minmax(0, 0.7fr)
                minmax(0, 1fr)
                minmax(0, 1.55fr);
            gap: 16px;
            align-items: start;
        }

        .history-column {
            min-width: 0;
        }

        .history-label {
            display: block;
            margin-bottom: 8px;
            color: var(--color-muted);
            font-size: 11px;
            font-weight: 400;
            line-height: 1.4;
        }

        .history-value {
            display: block;
            color: var(--color-text);
            font-size: 13px;
            font-weight: 600;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .history-date {
            display: block;
            margin-bottom: 8px;
            color: var(--color-muted);
            font-size: 11px;
            line-height: 1.4;
        }

        .history-code {
            display: block;
            color: var(--color-text);
            font-size: 13px;
            font-weight: 500;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .history-badges {
            display: flex;
            justify-content: flex-end;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 8px;
        }

        .history-badge {
            display: inline-block;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1.4;
            text-align: center;
            white-space: nowrap;
        }

        .history-badge-paid {
            background: #edf5e9;
            color: #3f8254;
        }

        .history-badge-pending {
            background: #fbf1d9;
            color: #946b26;
        }

        .history-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            margin-top: 20px;
        }

        .history-action {
            min-height: 32px;
            padding: 7px 11px;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.4;
            border-radius: 0;
        }

        .history-action:hover {
            background: #edf5ee;
        }

        .history-action-disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .history-action-disabled:hover {
            background: transparent;
        }

        .history-empty {
            padding: 30px 24px;
        }

        .history-pagination {
            margin-top: 24px;
        }

        @media (max-width: 1100px) {
            .history-order-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 20px;
            }

            .history-badges {
                justify-content: flex-start;
            }
        }

        @media (max-width: 640px) {
            .history-tabs {
                margin-top: 30px;
            }

            .history-order-card {
                padding: 20px 16px;
            }

            .history-order-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 18px 12px;
            }

            .history-badges {
                justify-content: flex-start;
            }

            .history-code {
                font-size: 12px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="purchase-page krm-container">

        {{-- Judul halaman --}}
        <div class="purchase-heading">
            <h1>Riwayat Pesanan</h1>
        </div>

        {{-- Tab riwayat --}}
        <div class="history-tabs" aria-label="Kategori riwayat">

            <span class="history-tab history-tab-active"
                  aria-current="page">
                Tiket
            </span>

            <span class="history-tab cursor-not-allowed"
                  aria-disabled="true"
                  title="Riwayat reservasi belum tersedia">
                Reservasi
            </span>

        </div>

        {{-- Daftar transaksi tiket --}}
        @forelse ($orders as $order)

            @php
                $paymentLabel = $order->payment?->statusLabel()
                    ?? ($order->status === 'paid'
                        ? 'Lunas'
                        : ($order->status === 'pending_payment'
                            ? 'Menunggu pembayaran'
                            : ucwords(str_replace('_', ' ', (string) $order->status))));

                $isPaid = $order->status === 'paid'
                    || $paymentLabel === 'Lunas';

                $ticketCount = $order->items->sum('adult_quantity')
                    + $order->items->sum('child_quantity');
            @endphp

            <article class="krm-card history-order-card">

                <div class="history-order-grid">

                    {{-- Tanggal pemesanan dan nomor transaksi --}}
                    <div class="history-column">
                        <span class="history-date">
                            {{ $order->created_at->locale('id')->translatedFormat('d M Y') }}
                        </span>

                        <span class="history-code"
                              title="Kode transaksi: {{ $order->code }}"
                              aria-label="Kode transaksi {{ $order->code }}">
                            {{ \Illuminate\Support\Str::limit($order->code, 20) }}
                        </span>
                    </div>

                    {{-- Jenis tiket --}}
                    <div class="history-column">
                        <span class="history-label">
                            Jenis Tiket
                        </span>

                        <span class="history-value">
                            {{ $order->items->pluck('ticket_name')->unique()->implode(', ') }}
                        </span>
                    </div>

                    {{-- Tanggal kunjungan --}}
                    <div class="history-column">
                        <span class="history-label">
                            Tgl Kunjungan
                        </span>

                        <span class="history-value">
                            {{ $order->visit_date->locale('id')->translatedFormat('d M Y') }}
                        </span>
                    </div>

                    {{-- Jumlah tiket --}}
                    <div class="history-column">
                        <span class="history-label">
                            Jumlah
                        </span>

                        <span class="history-value">
                            {{ $ticketCount }} tiket
                        </span>
                    </div>

                    {{-- Total pesanan --}}
                    <div class="history-column">
                        <span class="history-label">
                            Total
                        </span>

                        <span class="history-value">
                            Rp {{ number_format((float) $order->total, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Status pembayaran --}}
                    <div class="history-column">
                        <div class="history-badges">

                            <span class="history-badge {{ $isPaid ? 'history-badge-paid' : 'history-badge-pending' }}">
                                {{ $paymentLabel }}
                            </span>

                        </div>
                    </div>

                </div>

                {{-- Tombol aksi --}}
                <div class="history-actions">

                    <a href="{{ route('ticket-purchase.show', $order->code) }}"
                       class="krm-btn history-action">
                        Lihat Detail
                    </a>

                    <button type="button"
                            class="krm-btn history-action history-action-disabled"
                            disabled
                            title="Download E-Tiket belum tersedia sampai integrasi penerbitan E-Tiket selesai">
                        Download E-Tiket
                    </button>

                </div>

            </article>

        @empty

            {{-- Ketika wisatawan belum memiliki pesanan --}}
            <div class="krm-card history-empty">

                <h2 class="text-base font-semibold text-[var(--color-primary)]">
                    Belum ada pesanan
                </h2>

                <p class="mt-3 text-sm text-[var(--color-muted)]">
                    Pesanan yang kamu simpan akan muncul di sini.
                </p>

            </div>

        @endforelse

        {{-- Pagination --}}
        <div class="history-pagination">
            {{ $orders->links() }}
        </div>

    </div>
@endsection