@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-2">E-Tiket Saya</h1>
    <p class="text-gray-600 mb-6">
        Kode Pesanan: {{ $order->code }}
    </p>

    @forelse ($order->issuedTickets as $ticket)
        <div class="border rounded-xl p-5 mb-5 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-lg">E-Tiket</h2>
                <span class="text-sm font-semibold text-green-600">
                    {{ strtoupper($ticket->status) }}
                </span>
            </div>

            <p>
                <strong>Kode Tiket:</strong>
                {{ $ticket->ticket_code }}
            </p>

            <p>
                <strong>Nama Pengunjung:</strong>
                {{ $ticket->visitor->name }}
            </p>

            <p>
                <strong>Tanggal Kunjungan:</strong>
                {{ $order->visit_date->format('d-m-Y') }}
            </p>

            <p>
                <strong>Jenis Tiket:</strong>
                {{ $ticket->item->ticket_name }}
            </p>

            <p>
                <strong>Kategori:</strong>
                {{ ucfirst($ticket->visitor->age_category) }}
            </p>

            <div class="mt-4">
                <p class="text-sm text-gray-500">
                    QR Code akan ditampilkan pada tahap integrasi QR.
                </p>
            </div>
        </div>
    @empty
        <div class="border rounded-xl p-5">
            <p class="text-gray-600">
                E-Tiket belum diterbitkan. Silakan hubungi petugas jika pembayaran sudah lunas.
            </p>
        </div>
    @endforelse
</div>
@endsection
