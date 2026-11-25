@extends('layouts.admin')

@section('title', 'Manajemen Transaksi - Kebun Raya Mangrove')

@section('content')
<div class="mx-auto max-w-[1500px]">

    {{-- HEADER --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-[#D4DED7] pb-6">
        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>

            <h1 class="text-2xl font-semibold text-[#173D30]">
                Manajemen Transaksi
            </h1>

            <p class="mt-2 text-sm text-[#74877B]">
                Pantau seluruh transaksi pembelian tiket wisatawan.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="window.print()"
                class="inline-flex items-center justify-center border border-[#CBD9CF] px-4 py-3 text-sm font-semibold text-[#355D47] transition hover:bg-[#EAF2EC]">
                Print
            </button>

            <button type="button"
                class="inline-flex items-center justify-center border border-[#CBD9CF] px-4 py-3 text-sm font-semibold text-[#355D47] transition hover:bg-[#EAF2EC]">
                Export Excel
            </button>

            <button type="button"
                class="inline-flex items-center justify-center bg-[#064631] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#237052]">
                Export PDF
            </button>
        </div>
    </div>

    {{-- FILTER TRANSAKSI --}}
    <div class="mb-6 border border-[#DCE5DE] bg-white">

        <div class="border-b border-[#E2E9E4] px-5 py-5">
            <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                Filter Transaksi
            </h2>

            <p class="mt-1 text-xs text-[#819187]">
                Cari dan saring transaksi berdasarkan tanggal, status, dan metode pembayaran.
            </p>
        </div>

        <form method="GET" action="{{ route('admin.transaksi.index') }}" class="p-5">

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">

                {{-- TANGGAL KUNJUNGAN --}}
                <div>
                    <label for="visit_date" class="mb-2 block text-xs font-semibold text-[#4A695A]">
                        Tanggal Kunjungan
                    </label>

                    <input type="date" id="visit_date" name="visit_date"
                        value="{{ request('visit_date') }}"
                        class="min-h-11 w-full border border-[#CBD9CF] bg-white px-3 text-sm text-[#26463B] outline-none focus:border-[#237052]">
                </div>

                {{-- STATUS PEMBAYARAN --}}
                <div>
                    <label for="payment_status" class="mb-2 block text-xs font-semibold text-[#4A695A]">
                        Status Pembayaran
                    </label>

                    <select id="payment_status" name="payment_status"
                        class="min-h-11 w-full border border-[#CBD9CF] bg-white px-3 text-sm text-[#26463B]">
                        <option value="semua">Semua Status</option>
                        <option value="paid" @selected(request('payment_status') === 'paid')>Lunas</option>
                        <option value="pending" @selected(request('payment_status') === 'pending')>Menunggu Pembayaran</option>
                        <option value="under_review" @selected(request('payment_status') === 'under_review')>Dalam Pemeriksaan</option>
                        <option value="rejected" @selected(request('payment_status') === 'rejected')>Ditolak</option>
                        <option value="failed" @selected(request('payment_status') === 'failed')>Gagal</option>
                        <option value="expired" @selected(request('payment_status') === 'expired')>Kedaluwarsa</option>
                    </select>
                </div>

                {{-- STATUS TIKET --}}
                <div>
                    <label for="ticket_status" class="mb-2 block text-xs font-semibold text-[#4A695A]">
                        Status Tiket
                    </label>

                    <select id="ticket_status" name="ticket_status"
                        class="min-h-11 w-full border border-[#CBD9CF] bg-white px-3 text-sm text-[#26463B]">
                        <option value="semua">Semua Status</option>
                        <option value="belum_terbit" @selected(request('ticket_status') === 'belum_terbit')>E-Tiket Belum Terbit</option>
                        <option value="belum_checkin" @selected(request('ticket_status') === 'belum_checkin')>Belum Check-In</option>
                        <option value="sebagian_checkin" @selected(request('ticket_status') === 'sebagian_checkin')>Sebagian Check-In</option>
                        <option value="sudah_checkin" @selected(request('ticket_status') === 'sudah_checkin')>Sudah Check-In</option>
                    </select>
                </div>

                {{-- METODE PEMBAYARAN --}}
                <div>
                    <label for="method" class="mb-2 block text-xs font-semibold text-[#4A695A]">
                        Metode Pembayaran
                    </label>

                    <select id="method" name="method"
                        class="min-h-11 w-full border border-[#CBD9CF] bg-white px-3 text-sm text-[#26463B]">
                        <option value="semua">Semua Metode</option>
                        <option value="cash" @selected(request('method') === 'cash')>Cash</option>
                        <option value="qris" @selected(request('method') === 'qris')>QRIS</option>
                    </select>
                </div>

                {{-- PENCARIAN --}}
                <div class="md:col-span-2">
                    <label for="search" class="mb-2 block text-xs font-semibold text-[#4A695A]">
                        Cari Transaksi
                    </label>

                    <input type="text" id="search" name="search"
                        value="{{ request('search') }}"
                        placeholder="Nomor transaksi atau nama pengunjung..."
                        class="min-h-11 w-full border border-[#CBD9CF] bg-white px-3 text-sm text-[#26463B] outline-none focus:border-[#237052]">
                </div>

            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                <button type="submit"
                    class="inline-flex min-h-10 items-center justify-center bg-[#064631] px-5 py-2 text-sm font-semibold text-white transition hover:bg-[#237052]">
                    Terapkan Filter
                </button>

                <a href="{{ route('admin.transaksi.index') }}"
                    class="inline-flex min-h-10 items-center justify-center border border-[#CBD9CF] px-5 py-2 text-sm font-semibold text-[#526B5C] transition hover:bg-[#F1F5F2]">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- DAFTAR TRANSAKSI --}}
    <div class="overflow-hidden border border-[#DCE5DE] bg-white">

        <div class="border-b border-[#E2E9E4] px-5 py-5">
            <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                Daftar Transaksi
            </h2>

            <p class="mt-1 text-xs text-[#819187]">
                Seluruh data transaksi tiket wisatawan yang tercatat dalam sistem.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1450px] border-collapse text-left text-xs">

                <thead class="bg-[#E9F0EB] text-[#4A695A]">
                    <tr class="uppercase tracking-[0.05em]">
                        <th class="px-4 py-4 font-bold">No.</th>
                        <th class="px-4 py-4 font-bold">No. Transaksi</th>
                        <th class="px-4 py-4 font-bold">Pengunjung</th>
                        <th class="px-4 py-4 font-bold">Tanggal Kunjungan</th>
                        <th class="px-4 py-4 font-bold">Jenis Tiket</th>
                        <th class="px-4 py-4 font-bold">Jumlah</th>
                        <th class="px-4 py-4 font-bold">Total</th>
                        <th class="px-4 py-4 font-bold">Metode Pembayaran</th>
                        <th class="px-4 py-4 font-bold">Status Pembayaran</th>
                        <th class="px-4 py-4 font-bold">Status Tiket</th>
                        <th class="px-4 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">

                    @forelse ($payments as $payment)
                        @php
                            $order = $payment->order;

                            $ticketCount = $order?->items?->sum(function ($item) {
                                return (int) $item->adult_quantity + (int) $item->child_quantity;
                            }) ?? 0;

                            $issuedTickets = $order?->issuedTickets ?? collect();
                            $totalIssued = $issuedTickets->count();
                            $usedTickets = $issuedTickets->where('status', 'used')->count();

                            if ($totalIssued === 0) {
                                $ticketLabel = 'E-Tiket Belum Terbit';
                                $ticketClass = 'bg-[#F0F2F0] text-[#718076]';
                            } elseif ($usedTickets === 0) {
                                $ticketLabel = 'Belum Check-In';
                                $ticketClass = 'bg-[#FFF5DF] text-[#996C00]';
                            } elseif ($usedTickets === $totalIssued) {
                                $ticketLabel = 'Sudah Check-In';
                                $ticketClass = 'bg-[#E0F1E5] text-[#217548]';
                            } else {
                                $ticketLabel = 'Sebagian Check-In';
                                $ticketClass = 'bg-[#E8F0FC] text-[#41689C]';
                            }

                            $paymentLabels = [
                                'paid' => 'Lunas',
                                'pending' => 'Menunggu Pembayaran',
                                'under_review' => 'Dalam Pemeriksaan',
                                'rejected' => 'Ditolak',
                                'failed' => 'Gagal',
                                'expired' => 'Kedaluwarsa',
                            ];

                            $paymentLabel = $paymentLabels[$payment->status]
                                ?? ucfirst($payment->status);

                            $paymentClass = match ($payment->status) {
                                'paid' => 'bg-[#E0F1E5] text-[#217548]',
                                'pending', 'under_review' => 'bg-[#FFF5DF] text-[#996C00]',
                                'rejected', 'failed', 'expired' => 'bg-[#FCEAE6] text-[#B65B36]',
                                default => 'bg-[#F0F2F0] text-[#718076]',
                            };

                            $methodLabel = strtoupper($payment->method) === 'CASH'
                                ? 'Cash'
                                : strtoupper($payment->method);
                        @endphp

                        <tr class="transition hover:bg-[#F8FAF8]">

                            <td class="px-4 py-4 text-[#819187]">
                                {{ $payments->firstItem() + $loop->index }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-[#173D30]">
                                {{ $order?->code ?? $payment->code ?? '-' }}
                            </td>

                            <td class="px-4 py-4 font-medium">
                                {{ $order?->booker_name ?? '-' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $order?->visit_date ? \Carbon\Carbon::parse($order->visit_date)->format('d M Y') : '-' }}
                            </td>

                            <td class="max-w-[190px] px-4 py-4 leading-5">
                                {{ $order?->items?->pluck('ticket_name')->filter()->unique()->implode(', ') ?: '-' }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 text-center">
                                {{ $ticketCount }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-[#174E38]">
                                Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                            </td>

                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $methodLabel }}
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-block whitespace-nowrap px-3 py-1.5 font-semibold {{ $paymentClass }}">
                                    {{ $paymentLabel }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <span class="inline-block whitespace-nowrap px-3 py-1.5 font-semibold {{ $ticketClass }}">
                                    {{ $ticketLabel }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <a href="{{ route('admin.transaksi.show', $payment->id) }}"
                                    class="inline-flex min-h-9 items-center justify-center border border-[#CBD9CF] px-3 py-2 font-medium text-[#355D47] transition hover:bg-[#EAF2EC]">
                                    Detail
                                </a>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-12 text-center text-sm text-[#84958A]">
                                Belum ada data transaksi.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="flex flex-wrap items-center justify-between gap-4 border-t border-[#E2E9E4] px-5 py-4 text-xs text-[#819187]">

            <div>
                Menampilkan {{ $payments->firstItem() ?? 0 }}–{{ $payments->lastItem() ?? 0 }}
                dari {{ $payments->total() }} transaksi
            </div>

            <div>
                {{ $payments->links() }}
            </div>

        </div>
    </div>

</div>
@endsection
