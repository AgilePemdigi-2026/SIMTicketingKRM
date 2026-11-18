@extends('layouts.app')

@section('title', 'Reservasi - Pembayaran QRIS')

@push('styles')
<style>
    .rv-payment-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
        align-items: stretch;
    }

    @media (min-width: 1024px) {
        .rv-payment-layout {
            grid-template-columns:
                minmax(0, 2.2fr) minmax(0, 1fr);
        }
    }

    .rv-payment-layout > * {
        min-width: 0;
    }

    .rv-payment-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 8px 0;
        font-size: 13px;
    }

    .rv-payment-row span:last-child {
        font-weight: 600;
        color: #193B31;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .rv-qr-placeholder {
        width: 194px;
        height: 194px;
        max-width: 100%;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        border: 1px dashed #A8BDB1;
        background: #F7FAF7;
        color: #557066;
        text-align: center;
        padding: 16px;
    }

    .rv-qr-icon {
        width: 48px;
        height: 48px;
        border: 3px solid #07513D;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px;
        padding: 5px;
    }

    .rv-qr-icon span {
        background: #07513D;
    }
</style>
@endpush

@section('content')
@php
    $langkah = [
        'Pilih Fasilitas',
        'Pilih Tanggal',
        'Data Reservasi',
        'Pembayaran',
        'Invoice',
    ];

    $tanggalFormatted = \Carbon\Carbon::parse($tanggal)
        ->locale('id')
        ->translatedFormat('d M Y');

    $waktuMulai = substr($sesi->jam_mulai, 0, 5);
    $waktuSelesai = substr($sesi->jam_selesai, 0, 5);

    $totalBiaya = (float) $reservasi->total_amount;

    $pembayaran = $reservasi->payment;

    $attempt = $pembayaran?->attempts
        ?->sortByDesc('id')
        ->first();
@endphp

<div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

    {{-- =====================================
         PROGRESS RESERVASI
    ====================================== --}}
    <div class="mx-auto mb-14 max-w-3xl">

        <div class="relative flex items-start justify-between">

            <div class="absolute left-[10%] right-[10%] top-4 h-px bg-gray-200"></div>

            @foreach ($langkah as $index => $nama)

                <div class="relative z-10 flex flex-1 flex-col items-center gap-2 text-center">

                    <div
                        class="flex h-8 w-8 items-center justify-center border text-sm font-semibold
                        {{ $index <= 3
                            ? 'border-[#07513D] bg-[#07513D] text-white'
                            : 'border-gray-200 bg-[#FBFCF8] text-gray-400' }}"
                        @if ($index === 3)
                            aria-current="step"
                        @endif
                    >
                        {{ $index < 3 ? '✓' : $index + 1 }}
                    </div>

                    <span
                        class="text-[10px] sm:text-xs
                        {{ $index <= 3
                            ? 'font-semibold text-[#07513D]'
                            : 'text-gray-400' }}"
                    >
                        {{ $nama }}
                    </span>

                </div>

            @endforeach

        </div>
    </div>

    {{-- =====================================
         JUDUL
    ====================================== --}}
    <div class="mb-8">

        <h1 class="text-2xl font-bold text-[#193B31]">
            Pembayaran QRIS
        </h1>

        <div class="mt-2 h-1 w-56 bg-[#D9E8BE]"></div>

    </div>

    {{-- Pemberitahuan mode pratinjau --}}
    <div class="mb-6 border border-[#D6E0D9] bg-[#F0F7F0] px-4 py-3 text-sm text-[#193B31]" role="status">
        <strong>Reservasi berhasil dibuat.</strong>

        <p class="mt-1">
            Nomor Invoice:
            <span class="font-semibold">
                {{ $reservasi->invoice_number }}
            </span>
        </p>

        <p class="mt-1">
            Status Pembayaran:
            <span class="font-semibold">
                {{ $pembayaran?->status === 'paid'
                    ? 'Lunas'
                    : ($pembayaran ? 'Menunggu Pembayaran' : 'Belum Dibuat') }}
            </span>
        </p>
    </div>

    {{-- =====================================
         PRATINJAU INVOICE DAN QRIS
    ====================================== --}}
    <div class="rv-payment-layout">

        {{-- KOLOM KIRI --}}
        <section class="border border-gray-200 bg-white p-5 sm:p-6">

            <h2 class="mb-5 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                Rincian Reservasi
            </h2>

            <div class="rv-payment-row">
                <span class="text-gray-500">No. Invoice</span>
                <span>{{ $reservasi->invoice_number }}</span>
            </div>

            <div class="rv-payment-row">
                <span class="text-gray-500">Fasilitas</span>
                <span>{{ $fasilitas->nama_fasilitas }}</span>
            </div>

            <div class="rv-payment-row">
                <span class="text-gray-500">Tanggal</span>
                <span>{{ $tanggalFormatted }}</span>
            </div>

            <div class="rv-payment-row">
                <span class="text-gray-500">Waktu</span>
                <span>
                    {{ $waktuMulai }}–{{ $waktuSelesai }} WIB
                </span>
            </div>

            <div class="rv-payment-row">
                <span class="text-gray-500">Pemesan</span>
                <span>{{ $reservasi->customer_name }}</span>
            </div>

            <div class="rv-payment-row mt-2 border-t border-[#8FA99B] pt-4">

                <span class="font-bold text-[#193B31]">
                    Total
                </span>

                <span class="text-base font-bold text-[#07513D]">
                    Rp {{ number_format($totalBiaya, 0, ',', '.') }}
                </span>

            </div>

            <p class="mt-5 text-xs leading-relaxed text-gray-500">
                Total pembayaran dihitung berdasarkan
                {{ $reservasi->participant_count }} peserta
                dengan tarif Rp {{ number_format((float) $reservasi->unit_price, 0, ',', '.') }}
                per peserta.
            </p>

        </section>


        {{-- KOLOM KANAN - QRIS --}}
        @php
            $sudahLunas = $pembayaran?->status === 'paid';

            $qrisAktif = ! $sudahLunas
                && $attempt?->status === 'pending'
                && filled($attempt?->qr_image_url)
                && $attempt?->expires_at?->isFuture();

            $bolehBuatQris = ! $sudahLunas
                && (
                    ! $attempt
                    || in_array(
                        $attempt->status,
                        ['expired', 'failed', 'cancelled'],
                        true
                    )
                );

            $statusPembayaran = match (true) {
                $sudahLunas => 'Lunas',
                $attempt?->status === 'pending'
                    => 'Menunggu Pembayaran',
                $attempt?->status === 'expired'
                    => 'QRIS Kedaluwarsa',
                $attempt?->status === 'failed'
                    => 'Pembayaran Gagal',
                $attempt?->status === 'cancelled'
                    => 'Pembayaran Dibatalkan',
                $attempt !== null
                    => 'Menunggu Pemeriksaan',
                default => 'Belum Dibuat',
            };
        @endphp

        <aside
            id="rv-qris-widget"
            class="border border-gray-200 bg-white p-5 text-center sm:p-6"
            data-create-url="{{ route('facility-reservation.payment.qris.create', ['code' => $reservasi->code]) }}"
            data-sync-url="{{ route('facility-reservation.payment.qris.sync', ['code' => $reservasi->code]) }}"
            data-csrf="{{ csrf_token() }}"
        >
            <h2 class="mb-6 text-sm font-bold uppercase text-[#193B31]">
                Scan untuk Membayar
            </h2>

            @if ($sudahLunas)

                <div class="border border-green-200 bg-green-50 p-5">
                    <p class="font-bold text-green-800">
                        Pembayaran Berhasil
                    </p>
                    <p class="mt-2 text-xs text-green-700">
                        Pembayaran reservasi telah terkonfirmasi Lunas.
                    </p>
                </div>

            @elseif ($qrisAktif)

                {{-- QRIS asli dari Midtrans --}}
                <div class="mx-auto flex justify-center">
                    <img
                        src="{{ $attempt->qr_image_url }}"
                        alt="QRIS pembayaran reservasi"
                        width="194"
                        height="194"
                        class="h-[194px] w-[194px] border border-gray-200 object-contain"
                        referrerpolicy="no-referrer"
                    >
                </div>

            {{-- URL QRIS untuk Midtrans Payment Simulator --}}
            @if (filled($attempt->qr_image_url))
                <div class="mx-auto mt-4 w-full max-w-sm text-left">
                    <p class="mb-2 text-xs font-semibold text-[#07513D]">
                        URL QRIS Midtrans Sandbox
                    </p>

                    <textarea
                        id="reservation-qris-url"
                        readonly
                        rows="3"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-xs text-gray-700"
                        style="resize: none; overflow-wrap: anywhere;"
                    >{{ $attempt->qr_image_url }}</textarea>

                    <button
                        type="button"
                        id="copy-reservation-qris"
                        class="mt-2 w-full border border-[#07513D] px-4 py-2 text-xs font-semibold text-[#07513D] hover:bg-[#F0F7F0]"
                    >
                        Copy QRIS URL
                    </button>

                    <p class="mt-2 text-xs text-gray-500">
                        Salin URL ini ke Midtrans QRIS Payment Simulator
                        untuk menguji pembayaran Sandbox.
                    </p>
                </div>
            @endif

                <p class="mt-3 text-xs text-gray-500">
                    Scan QRIS menggunakan aplikasi pembayaran.
                </p>

            @else

                {{-- Tampilan sebelum QRIS tersedia --}}
                <div class="rv-qr-placeholder">

                    <div class="rv-qr-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>

                    <p class="text-xs font-semibold">
                        QRIS Belum Tersedia
                    </p>

                </div>

            @endif

            {{-- COUNTDOWN --}}
            <div class="mt-6">

                <p class="mb-2 text-xs text-gray-500">
                    Batas Waktu Pembayaran
                </p>

                <p
                    id="rv-qris-countdown"
                    data-expires-at="{{ $attempt?->expires_at?->toIso8601String() }}"
                    class="text-2xl font-bold text-[#193B31]"
                >
                    {{ $sudahLunas ? 'Selesai' : '--:--' }}
                </p>

            </div>

            <p class="mt-4 text-xs leading-relaxed text-gray-500">
                Pembayaran reservasi menggunakan Midtrans QRIS Sandbox.
                Status pembayaran diperbarui berdasarkan hasil verifikasi
                transaksi dari Midtrans.
            </p>

            {{-- STATUS PEMBAYARAN --}}
            <div class="mt-5 border-t border-gray-200 pt-4">

                <p class="text-xs text-gray-500">
                    Status Pembayaran
                </p>

                <p class="mt-1 text-sm font-semibold text-[#193B31]">
                    {{ $statusPembayaran }}
                </p>

            </div>

            {{-- TOMBOL PEMBAYARAN --}}
            <div class="mt-5 space-y-3">

                @if ($bolehBuatQris)

                    <button
                        type="button"
                        data-qris-action="create"
                        class="w-full bg-[#07513D] px-4 py-3 text-sm font-semibold text-white hover:bg-[#064332]"
                    >
                        {{ $attempt ? 'Buat QRIS Baru' : 'Buat QRIS Pembayaran' }}
                    </button>

                @endif

                @if ($attempt && ! $sudahLunas)

                    <button
                        type="button"
                        data-qris-action="sync"
                        class="w-full border border-[#07513D] px-4 py-3 text-sm font-semibold text-[#07513D] hover:bg-[#F0F7F0]"
                    >
                        Cek Status Pembayaran
                    </button>

                @endif

            </div>

            <p
                id="rv-qris-feedback"
                role="status"
                aria-live="polite"
                class="mt-4 text-xs text-[#193B31]"
            ></p>

        </aside>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const widget = document.getElementById('rv-qris-widget');

    if (!widget) return;

    const feedback = document.getElementById('rv-qris-feedback');
    const countdown = document.getElementById('rv-qris-countdown');
    const buttons = widget.querySelectorAll('[data-qris-action]');

    const copyButton = document.getElementById('copy-reservation-qris');
    const qrisUrl = document.getElementById('reservation-qris-url');

    copyButton?.addEventListener('click', async () => {
        if (!qrisUrl) return;

        try {
            await navigator.clipboard.writeText(qrisUrl.value);
            copyButton.textContent = 'Berhasil Disalin!';
        } catch (error) {
            qrisUrl.select();
            copyButton.textContent = 'Salin teks yang dipilih';
        }
    });

    // Countdown berdasarkan waktu kedaluwarsa Midtrans.
    const expiresAt = countdown?.dataset.expiresAt;

    if (expiresAt && countdown.textContent.trim() !== 'Selesai') {
        const expiry = new Date(expiresAt).getTime();

        if (Number.isFinite(expiry)) {
            const updateCountdown = () => {
                const remaining = Math.max(
                    0,
                    Math.floor((expiry - Date.now()) / 1000)
                );

                const minutes = Math.floor(remaining / 60);
                const seconds = remaining % 60;

                countdown.textContent =
                    String(minutes).padStart(2, '0')
                    + ':'
                    + String(seconds).padStart(2, '0');
            };

            updateCountdown();
            setInterval(updateCountdown, 1000);
        }
    }

    // Buat QRIS atau sinkronkan status pembayaran.
    buttons.forEach(button => {
        button.addEventListener('click', async () => {
            const action = button.dataset.qrisAction;

            const url = action === 'create'
                ? widget.dataset.createUrl
                : widget.dataset.syncUrl;

            buttons.forEach(item => item.disabled = true);

            feedback.textContent = action === 'create'
                ? 'Sedang menyiapkan QRIS Midtrans...'
                : 'Sedang memeriksa status pembayaran...';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': widget.dataset.csrf
                    },
                    body: JSON.stringify({})
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message
                        || 'Permintaan pembayaran gagal.'
                    );
                }

                // Muat ulang untuk mengambil status terbaru
                // langsung dari database PostgreSQL.
                window.location.reload();

            } catch (error) {
                feedback.textContent = error.message;

                buttons.forEach(item => item.disabled = false);
            }
        });
    });
});
</script>

    </div>

    {{-- =====================================
         NAVIGASI
    ====================================== --}}
    <div class="mt-6 flex flex-wrap items-center justify-end gap-3">

        <a
            href="{{ route('wisatawan.reservasi.data', [
                'fasilitas' => $fasilitas->id_fasilitas,
                'tanggal' => $tanggal,
                'sesi' => $sesi->id_sesi,
            ]) }}"
            class="border border-[#07513D] px-6 py-3 text-sm font-semibold text-[#07513D] hover:bg-[#F0F7F0]"
        >
            ← Kembali
        </a>

    @if ($pembayaran?->status === 'paid')

        <a
            href="{{ route('facility-reservation.payment.invoice', [
                'code' => $reservasi->code
            ]) }}"
            class="bg-[#07513D] px-6 py-3 text-sm font-semibold text-white hover:bg-[#064332]"
        >
            Pembayaran Selesai — Lihat Invoice
        </a>

    @else

        <button
            type="button"
            disabled
            class="cursor-not-allowed bg-[#07513D] px-6 py-3 text-sm font-semibold text-white opacity-50"
        >
            Pembayaran Selesai — Lihat Invoice
        </button>

    @endif

    </div>

    <p class="mt-3 text-right text-xs text-gray-500">
        Tombol invoice akan aktif hanya setelah pembayaran
        benar-benar dinyatakan lunas oleh sistem.
    </p>

</div>

{{-- Akses pengujian tampilan invoice --}}
<div class="mt-4 text-right">

    <a
        href="{{ route('wisatawan.reservasi.invoice.preview', [
            'fasilitas' => $fasilitas->id_fasilitas,
            'tanggal' => $tanggal,
            'sesi' => $sesi->id_sesi,
        ]) }}"
        class="text-sm font-semibold text-[#07513D] underline underline-offset-4"
    >
        Lihat Pratinjau Desain Invoice
    </a>

    <p class="mt-1 text-xs text-gray-500">
        Hanya untuk pengujian frontend, bukan bukti pembayaran.
    </p>

</div>

@endsection
