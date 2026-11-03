@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Kebun Raya Mangrove Surabaya')

@push('styles')
    @include('wisatawan.ticket-purchase.styles')

    <style>
        /* Khusus halaman Riwayat Pesanan agar tidak mengubah UI checkout. */
        .history-page {
            max-width: 1200px;
            padding-top: 64px;
            padding-bottom: 96px;
            min-height: 650px;
        }

        .history-page .history-title {
            display: inline-block;
            margin: 0 0 44px;
            padding: 0 0 5px;
            color: var(--color-text, #173a31);
            border-bottom: 3px solid #a5c969;
            font-size: clamp(24px, 2.6vw, 30px);
            font-weight: 700;
            line-height: 1.2;
        }

        .history-tabs {
            display: flex;
            gap: 4px;
            border-bottom: 1px solid var(--color-border, #d6e1d9);
            margin-bottom: 20px;
        }

        .history-tab {
            appearance: none;
            border: 0;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: 10px 23px 14px;
            margin: 0 0 -1px;
            background: transparent;
            color: var(--color-muted, #74857e);
            font: inherit;
            font-size: 15px;
            cursor: pointer;
        }

        .history-tab[aria-selected="true"] {
            color: var(--color-primary, #07503a);
            font-weight: 700;
            border-bottom-color: var(--color-primary, #07503a);
        }

        .history-tab:focus-visible,
        .history-action:focus-visible {
            outline: 2px solid var(--color-primary, #07503a);
            outline-offset: 3px;
        }

        .history-card {
            margin-bottom: 12px;
            padding: 18px 18px 17px;
            background: #fff;
            border: 1px solid var(--color-border, #d6e1d9);
        }

        .history-card-grid {
            display: grid;
            grid-template-columns: minmax(160px, 1.5fr) minmax(125px, 1.05fr) minmax(120px, .95fr) minmax(80px, .6fr) minmax(100px, .9fr) auto;
            gap: 12px 15px;
            align-items: start;
        }

        .history-card dt,
        .history-date {
            color: var(--color-muted, #7c8d85);
            font-size: 10px;
            font-weight: 400;
            line-height: 1.5;
        }

        .history-card dd {
            margin: 5px 0 0;
            color: var(--color-text, #173a31);
            font-size: 12px;
            font-weight: 600;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }

        .history-date { margin: 0 0 5px; }

        .history-code {
            color: var(--color-text, #173a31);
            font-size: 12px;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .history-statuses {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
            align-self: start;
            padding-top: 5px;
        }

        .history-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 27px;
            padding: 6px 9px;
            font-size: 10px;
            line-height: 1.3;
            text-align: center;
            white-space: nowrap;
            background: #fff2d9;
            color: #8b651d;
        }

        .history-status.is-paid {
            background: #e8f3e6;
            color: #287149;
        }

        .history-status.is-neutral {
            background: #eceef0;
            color: #737d84;
        }

        .history-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 7px;
            margin-top: 17px;
        }

        .history-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 6px 11px;
            border: 1px solid var(--color-primary, #07503a);
            color: var(--color-primary, #07503a);
            background: #fff;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            line-height: 1.2;
            cursor: pointer;
        }

        .history-action:hover:not(:disabled) {
            background: #eef5ef;
        }

        .history-action:disabled {
            border-color: #bdcfc5;
            color: #899b91;
            background: #f5f8f5;
            cursor: not-allowed;
        }

        .history-empty {
            border: 1px solid var(--color-border, #d6e1d9);
            background: #fff;
            padding: 34px 28px;
            color: var(--color-text, #173a31);
        }

        .history-empty h2 { margin: 0 0 8px; font-size: 18px; }
        .history-empty p { margin: 0 0 18px; color: var(--color-muted, #74857e); }
        .history-empty a { display: inline-block; }
        .history-pagination { margin-top: 24px; }

        @media (max-width: 1050px) {
            .history-card-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .history-statuses { justify-content: flex-start; }
        }

        @media (max-width: 640px) {
            .history-page { padding-top: 36px; padding-bottom: 64px; }
            .history-page .history-title { margin-bottom: 28px; }
            .history-card { padding: 16px; }
            .history-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 14px; }
            .history-statuses { grid-column: 1 / -1; }
            .history-action { min-height: 39px; }
        }
    </style>
@endpush

@section('content')
<div class="purchase-page krm-container history-page">
    <h1 class="history-title">Riwayat Pesanan</h1>

    <div class="history-tabs" role="tablist" aria-label="Jenis riwayat pesanan">
        <button
            type="button"
            class="history-tab"
            id="history-tab-tiket"
            role="tab"
            aria-selected="true"
            aria-controls="history-panel-tiket"
            tabindex="0"
        >Tiket</button>
        <button
            type="button"
            class="history-tab"
            id="history-tab-reservasi"
            role="tab"
            aria-selected="false"
            aria-controls="history-panel-reservasi"
            tabindex="-1"
        >Reservasi</button>
    </div>

    <section id="history-panel-tiket" role="tabpanel" aria-labelledby="history-tab-tiket">
        @forelse ($orders as $order)
            @php
                $isPaid = $order->status === 'paid' && $order->payment?->status === 'paid';

$latestAttempt = $order->payment?->method === 'qris'
    ? $order->payment->gatewayAttempts->first()
    : null;
            $paymentLabel = $isPaid
                ? 'Lunas'
                : match ($latestAttempt?->status) {
                    'expired' => 'Pembayaran Kedaluwarsa',
                    'failed' => 'Pembayaran Gagal',
                    'cancelled' => 'Pembayaran Dibatalkan',
                    default => $order->status === 'pending_payment'
                        ? ($order->payment?->statusLabel() ?? 'Menunggu pembayaran')
                        : ($order->payment?->statusLabel()
                            ?? ucfirst(str_replace('_', ' ', $order->status))),
                };
                $ticketNames = $order->items->pluck('ticket_name')->filter()->unique()->implode(', ');
                $ticketCount = $order->items->sum('adult_quantity') + $order->items->sum('child_quantity');
                $displayCode = 'TRX-' . $order->created_at->format('Y') . '-'
                    . str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
                $detailUrl = $isPaid
                    ? route('ticket-payment.e-ticket-placeholder', $order->code)
                    : ($order->status === 'pending_payment'
                        ? route('ticket-payment.show', $order->code)
                        : route('ticket-purchase.show', $order->code));
            @endphp

            <article class="history-card" aria-label="Pesanan {{ $displayCode }}">
                <div class="history-card-grid">
                    <div>
                        <p class="history-date">{{ $order->created_at->locale('id')->translatedFormat('d M Y') }}</p>
                        <div class="history-code" title="Nomor pesanan asli: {{ $order->code }}">
                            {{ $displayCode }}
                        </div>
                    </div>

                    <dl>
                        <dt>Jenis Tiket</dt>
                        <dd>{{ $ticketNames ?: 'Tiket' }}</dd>
                    </dl>

                    <dl>
                        <dt>Tgl Kunjungan</dt>
                        <dd>{{ $order->visit_date->locale('id')->translatedFormat('d M Y') }}</dd>
                    </dl>

                    <dl>
                        <dt>Jumlah</dt>
                        <dd>{{ $ticketCount }} tiket</dd>
                    </dl>

                    <dl>
                        <dt>Total</dt>
                        <dd>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</dd>
                    </dl>

                    <div class="history-statuses" aria-label="Status pesanan">
                        <span class="history-status {{ $isPaid ? 'is-paid' : '' }}">{{ $paymentLabel }}</span>
                        <span class="history-status is-neutral" title="Status check-in menunggu integrasi modul validasi tiket">Check-in belum tersedia</span>
                    </div>
                </div>

                <div class="history-actions">
                    <a class="history-action" href="{{ $detailUrl }}">Lihat Detail</a>
                    <button
                        class="history-action"
                        type="button"
                        disabled
                        title="Download E-Tiket tersedia setelah modul penerbitan diintegrasikan"
                    >Download E-Tiket</button>
                </div>
            </article>
        @empty
            <div class="history-empty">
                <h2>Belum ada pesanan tiket</h2>
                <p>Pesanan tiket yang kamu buat akan muncul di halaman ini.</p>
                <a class="krm-btn krm-btn-primary" href="{{ route('ticket-purchase.index') }}">Beli Tiket</a>
            </div>
        @endforelse

        <div class="history-pagination">{{ $orders->links() }}</div>
    </section>

    <section id="history-panel-reservasi" role="tabpanel" aria-labelledby="history-tab-reservasi" hidden>
        <div class="history-empty">
            <h2>Riwayat Reservasi</h2>
            <p>Data reservasi belum terhubung ke halaman ini. Riwayat tiket tetap bisa dilihat melalui tab Tiket.</p>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const tabs = [
        document.getElementById('history-tab-tiket'),
        document.getElementById('history-tab-reservasi')
    ];
    const panels = [
        document.getElementById('history-panel-tiket'),
        document.getElementById('history-panel-reservasi')
    ];
    if (tabs.some(tab => !tab) || panels.some(panel => !panel)) return;

    const activate = index => {
        tabs.forEach((tab, i) => {
            const selected = i === index;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
            panels[i].hidden = !selected;
        });
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => activate(index));
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const next = event.key === 'Home' ? 0
                : event.key === 'End' ? tabs.length - 1
                : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
            activate(next);
            tabs[next].focus();
        });
    });
})();
</script>
@endpush