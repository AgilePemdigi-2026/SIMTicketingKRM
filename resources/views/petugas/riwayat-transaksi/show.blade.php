@extends('layouts.petugas')

@section('title', 'Detail Transaksi')

@section('content')
<style>
    .detail-page { padding:32px 24px 60px; }
    .detail-head {
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:16px;
        margin-bottom:18px;
    }
    .detail-title {
        margin:0;
        font-size:32px;
        font-weight:700;
        color:#163b31;
    }
    .detail-line { border-top:1px solid #cfdcd3; margin:18px 0 24px; }

    .detail-btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:48px;
        padding:0 18px;
        border:1px solid #0b5a43;
        background:#fff;
        color:#0b5a43;
        text-decoration:none;
        font-weight:600;
    }

    .detail-grid {
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap:24px;
        margin-bottom:28px;
    }

    .detail-card {
        border:1px solid #d6e1d8;
        background:#fff;
        padding:20px;
    }

    .detail-card h2 {
        margin:0 0 18px;
        font-size:16px;
        font-weight:700;
        text-transform:uppercase;
        letter-spacing:.04em;
        color:#173c32;
    }

    .detail-row {
        display:grid;
        grid-template-columns: 180px 1fr;
        gap:16px;
        padding:10px 0;
        border-bottom:1px solid #edf2ee;
        font-size:14px;
    }

    .detail-row:last-child { border-bottom:none; }

    .detail-label { color:#778b83; }
    .detail-value { color:#193b31; font-weight:600; text-align:right; }

    .detail-actions {
        display:flex;
        gap:12px;
        flex-wrap:wrap;
        margin-top:10px;
    }

    .detail-btn.primary {
        background:#0b5a43;
        color:#fff;
    }

    @media (max-width: 992px) {
        .detail-grid { grid-template-columns: 1fr; }
        .detail-head { flex-direction:column; align-items:stretch; }
    }

    /* Tampilan khusus saat mencetak Detail Transaksi */
    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    @media print {
        /* Sembunyikan navigasi portal */
        body > header,
        body > a[href="#main-content"] {
            display: none !important;
        }

        body {
            background: #fff !important;
            color: #193b31;
            margin: 0;
        }

        main#main-content {
            min-height: 0 !important;
            padding: 0 !important;
        }

        .detail-page {
            padding: 0 !important;
        }

        /* Judul transaksi */
        .detail-head {
            display: block;
            margin-bottom: 12px;
        }

        .detail-title {
            font-size: 17px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .detail-line {
            margin: 10px 0 16px;
        }

        /* Sembunyikan tombol navigasi dan cetak */
        .detail-head .detail-btn,
        .detail-actions {
            display: none !important;
        }

        /* Susunan empat kartu */
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 0;
        }

        .detail-card {
            padding: 12px;
            border: 1px solid #d6e1d8;
            break-inside: avoid;
            page-break-inside: avoid;
            min-width: 0;
        }

        .detail-card h2 {
            font-size: 11px;
            margin-bottom: 10px;
        }

        .detail-row {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 6px;
            padding: 7px 0;
            font-size: 10px;
            break-inside: avoid;
        }

        .detail-label,
        .detail-value {
            overflow-wrap: anywhere;
        }

        .detail-value {
            text-align: right;
        }
    }

</style>

<div class="detail-page">
    <div class="detail-head">
        <h1 class="detail-title">Detail Transaksi — {{ $order?->code ?? '-' }}</h1>

        <a href="{{ route('petugas.riwayat-transaksi.index') }}" class="detail-btn">
            ← Kembali
        </a>
    </div>

    <div class="detail-line"></div>

    <div class="detail-grid">
        <div class="detail-card">
            <h2>Informasi Pengunjung</h2>

            <div class="detail-row">
                <div class="detail-label">Nama</div>
                <div class="detail-value">{{ $order?->booker_name ?? '-' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">No. Identitas</div>
                <div class="detail-value">{{ $order?->booker_identity_number ?? '-' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">No. Telepon</div>
                <div class="detail-value">{{ $order?->booker_phone ?? '-' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Jenis Wisatawan</div>
                <div class="detail-value">{{ ucfirst($order?->visitor_type ?? '-') }}</div>
            </div>
        </div>

        <div class="detail-card">
            <h2>Informasi Tiket</h2>

            <div class="detail-row">
                <div class="detail-label">Jenis Tiket</div>
                <div class="detail-value">{{ $order?->items?->pluck('ticket_name')->filter()->unique()->implode(', ') ?: '-' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Jumlah Tiket</div>
                <div class="detail-value">
                    @php
                        $items = $order?->items ?? collect();

                        $adultCount = $items->sum('adult_quantity');
                        $childCount = $items->sum('child_quantity');

                        $quantityLabel = implode(', ', array_filter([
                            $adultCount > 0 ? 'Dewasa x' . $adultCount : null,
                            $childCount > 0 ? 'Anak x' . $childCount : null,
                        ]));
                    @endphp
                    {{ $quantityLabel ?: '-' }}
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Tanggal Kunjungan</div>
                <div class="detail-value">{{ optional($order?->visit_date)->format('d M Y') ?? $order?->visit_date }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status Tiket</div>
                <div class="detail-value">{{ $ticketStatus }}</div>
            </div>
        </div>

        <div class="detail-card">
            <h2>Informasi Pembayaran</h2>

            <div class="detail-row">
                <div class="detail-label">Metode Pembayaran</div>
                <div class="detail-value">{{ strtoupper($payment->method) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Kanal</div>
                <div class="detail-value">{{ $channelLabel }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Total Tagihan</div>
                <div class="detail-value">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Uang Diterima</div>
                <div class="detail-value">
                    {{ $payment->cash_received_amount ? 'Rp ' . number_format((float) $payment->cash_received_amount, 0, ',', '.') : '-' }}
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Kembalian</div>
                <div class="detail-value">
                    {{ $payment->cash_change_amount ? 'Rp ' . number_format((float) $payment->cash_change_amount, 0, ',', '.') : '-' }}
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status Pembayaran</div>
                <div class="detail-value">
                    {{ $payment->status === 'paid' ? 'Lunas' : ucfirst($payment->status) }}
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Waktu Bayar</div>
                <div class="detail-value">
                    {{ $payment->paid_at
                        ? $payment->paid_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB'
                        : '-'
                    }}
                </div>
            </div>
        </div>

        <div class="detail-card">
            <h2>Informasi Pemrosesan</h2>

            <div class="detail-row">
                <div class="detail-label">Petugas Kasir</div>
                <div class="detail-value">
                    {{ $cashierName ?? '-' }}                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Petugas Validasi</div>
                <div class="detail-value">
                    {{ $validatorName ?? '-' }}
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Status Check-In</div>
                <div class="detail-value">{{ $ticketStatus }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Waktu Check-In</div>
                <div class="detail-value">
                    {{ $lastCheckInAt
                        ? $lastCheckInAt->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB'
                        : '-'
                    }}
                </div>
            </div>
        </div>
    </div>

    <div class="detail-actions">
        <button type="button" class="detail-btn primary" onclick="window.print()">
            🖨 Print Detail
        </button>
        <a href="{{ route('petugas.riwayat-transaksi.index') }}" class="detail-btn">
            ← Kembali ke Riwayat
        </a>
    </div>
</div>
@endsection