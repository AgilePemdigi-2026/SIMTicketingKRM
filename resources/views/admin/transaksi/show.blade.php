@extends('layouts.admin')

@section('title', 'Detail Transaksi - ' . $order->code)

@section('content')
<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <h3 class="text-xl font-bold text-[#073B2D]">
                Detail Transaksi - {{ $order->code }}
            </h3>
            <p class="mt-1 text-sm text-[#718579]">
                Informasi lengkap transaksi tiket wisatawan.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
    <a href="{{ route('admin.transaksi.index') }}"
       class="inline-flex items-center justify-center rounded-lg
              border border-[#D7E3DA] bg-white px-5 py-2.5
              text-sm font-bold text-[#315B45]
              hover:bg-[#F4F8F5]">
        Kembali
    </a>

    <button type="button"
            onclick="window.print()"
            class="inline-flex items-center justify-center rounded-lg
                   bg-[#E8F5EC] px-5 py-2.5
                   text-sm font-bold text-[#267346]
                   hover:bg-[#D5EBDD]">
        <span aria-hidden="true">🖨</span>
        Print Detail
    </button>
</div>
    </div>

    {{-- INFORMASI TRANSAKSI --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

        {{-- INFORMASI PENGUNJUNG --}}
        <div class="rounded-xl border border-[#E0E9E2] bg-white p-5 shadow-sm">
            <h4 class="mb-5 border-b border-[#E8EFE9] pb-3 font-bold text-[#073B2D]">
                Informasi Pengunjung
            </h4>

            <div class="space-y-4 text-sm">
                <div>
                    <p class="text-[#829187]">Nama Pengunjung</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->booker_name ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Nomor Identitas</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->booker_identity_number ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Nomor Telepon</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->booker_phone ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Email</p>
                    <p class="mt-1 break-all font-semibold text-[#244735]">
                        {{ $order->booker_email ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Jenis Pengunjung</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->visitor_type ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- INFORMASI TRANSAKSI --}}
        <div class="rounded-xl border border-[#E0E9E2] bg-white p-5 shadow-sm">
            <h4 class="mb-5 border-b border-[#E8EFE9] pb-3 font-bold text-[#073B2D]">
                Informasi Transaksi
            </h4>

            <div class="space-y-4 text-sm">
                <div>
                    <p class="text-[#829187]">Nomor Transaksi</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->code }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Tanggal Pemesanan</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->created_at?->format('d-m-Y H:i') ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Jenis Tiket</p>
                    <div class="mt-1 space-y-1 font-semibold text-[#244735]">
                        @forelse ($order->items as $item)
                            <p>{{ $item->ticket_name }}</p>
                        @empty
                            <p>-</p>
                        @endforelse
                    </div>
                </div>

                <div>
                    <p class="text-[#829187]">Jumlah Tiket</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->items->sum(fn ($item) => (int) $item->adult_quantity + (int) $item->child_quantity) }}
                        tiket
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Total Pembayaran</p>
                    <p class="mt-1 font-bold text-[#073B2D]">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Metode Pembayaran</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ strtoupper($payment->method ?? '-') }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Saluran Transaksi</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->sale_channel === 'counter' ? 'Loket' : 'Online' }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Status Pembayaran</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $payment->statusLabel() }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Tanggal Kunjungan</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->visit_date ? \Carbon\Carbon::parse($order->visit_date)->format('d-m-Y') : '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- INFORMASI VALIDASI --}}
        <div class="rounded-xl border border-[#E0E9E2] bg-white p-5 shadow-sm">
            <h4 class="mb-5 border-b border-[#E8EFE9] pb-3 font-bold text-[#073B2D]">
                Informasi Validasi
            </h4>

            <div class="space-y-4 text-sm">
                <div>
                    <p class="text-[#829187]">Status Tiket</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $ticketStatus }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Waktu Check-In Terakhir</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $lastCheckInAt ? \Carbon\Carbon::parse($lastCheckInAt)->format('d-m-Y H:i') : '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Kasir</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $cashierName ?? '-' }}
                    </p>
                </div>

                <div>
                    <p class="text-[#829187]">Petugas Validasi</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $validatorNames->isNotEmpty() ? $validatorNames->implode(', ') : '-' }}
                    </p>
                </div>
<div>
    <p class="text-[#829187]">QR E-Tiket</p>

    @forelse ($tickets as $ticket)
        <div class="mt-3 rounded-xl border border-[#E0E9E2] p-4">

            <div class="flex flex-col items-center gap-3">

                @if ($ticket->qr_code_data_uri ?? false)
                    <img
                        src="{{ $ticket->qr_code_data_uri }}"
                        alt="QR E-Tiket {{ $ticket->ticket_code }}"
                        class="h-40 w-40 object-contain"
                    />
                @else
                    <div class="flex h-40 w-40 items-center justify-center rounded-lg bg-[#F4F8F5] text-center text-xs text-[#829187]">
                        QR E-Tiket belum tersedia
                    </div>
                @endif

                <p class="break-all text-center font-mono text-xs font-semibold text-[#315B45]">
                    {{ $ticket->ticket_code }}
                </p>

                @if ($ticket->status === 'used')
                    <span class="rounded-full bg-[#E8F5EC] px-3 py-1 text-xs font-bold text-[#267346]">
                        SUDAH CHECK-IN
                    </span>

                    <p class="text-center text-xs text-[#829187]">
                        {{ $ticket->used_at?->format('d-m-Y H:i') ?? '-' }}
                    </p>
                @else
                    <span class="rounded-full bg-[#FFF4DC] px-3 py-1 text-xs font-bold text-[#946200]">
                        BELUM CHECK-IN
                    </span>
                @endif

            </div>
        </div>
    @empty
        <div class="mt-3 rounded-lg bg-[#F4F8F5] p-4 text-sm text-[#829187]">
            E-Tiket belum diterbitkan karena pembayaran belum lunas.
        </div>
    @endforelse
</div>


                <div>
                    <p class="text-[#829187]">Nomor Transaksi</p>
                    <p class="mt-1 font-semibold text-[#244735]">
                        {{ $order->code }}
                    </p>
                </div>
            </div>
        </div>

    </div>

    {{-- RINCIAN TIKET --}}
    <div class="overflow-hidden rounded-xl border border-[#E0E9E2] bg-white shadow-sm">
        <div class="border-b border-[#E8EFE9] px-5 py-4">
            <h4 class="font-bold text-[#073B2D]">Rincian Tiket</h4>
            <p class="mt-1 text-sm text-[#829187]">
                Daftar tiket dalam transaksi ini.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[650px] text-left text-sm">
                <thead class="bg-[#F4F8F5] text-xs uppercase text-[#718579]">
                    <tr>
                        <th class="px-5 py-3">No.</th>
                        <th class="px-5 py-3">Nama Tiket</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">Jumlah Dewasa</th>
                        <th class="px-5 py-3">Jumlah Anak</th>
                        <th class="px-5 py-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8EFE9] text-[#315B45]">
                    @forelse ($order->items as $item)
                        <tr>
                            <td class="px-5 py-4">{{ $loop->iteration }}</td>
                            <td class="px-5 py-4 font-semibold">
                                {{ $item->ticket_name }}
                            </td>
                            <td class="px-5 py-4">
                                {{ $item->ticket_category ?? '-' }}
                            </td>
                            <td class="px-5 py-4">
                                {{ $item->adult_quantity }}
                            </td>
                            <td class="px-5 py-4">
                                {{ $item->child_quantity }}
                            </td>
                            <td class="px-5 py-4 font-semibold">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-[#829187]">
                                Belum ada rincian tiket.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
    @media print {
        aside, nav, header, button, a {
            display: none !important;
        }

        body {
            background: white !important;
        }

        main {
            width: 100% !important;
            padding: 0 !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }

        .rounded-xl {
            break-inside: avoid;
        }
    }
</style>
@endsection
