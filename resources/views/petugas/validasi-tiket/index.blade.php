@extends('layouts.app')

@section('title', 'Validasi E-Tiket - Petugas')

@push('styles')
    @include('partials.ticket-payment-styles')
@endpush

@section('content')
<div class="purchase-page krm-container">

    <div class="purchase-heading">
        <h1>Validasi E-Tiket</h1>
        <p>Periksa QR Token E-Tiket pengunjung dan lakukan check-in.</p>
    </div>

    <section class="krm-card payment-card">
        <h2>Periksa E-Tiket</h2>

        <div class="mt-4 mb-5">
            <p class="mb-3">
                Pindai QR Code E-Tiket menggunakan kamera.
            </p>

            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    id="start-qr-scanner"
                    class="krm-btn krm-btn-primary"
                >
                    Buka Kamera
                </button>

                <button
                    type="button"
                    id="stop-qr-scanner"
                    class="krm-btn"
                    disabled
                >
                    Tutup Kamera
                </button>
            </div>

            <div
                id="qr-reader"
                class="mt-4"
                style="max-width: 420px;"
            ></div>

            <p
                id="qr-scanner-message"
                class="mt-3 text-sm text-gray-600"
                role="status"
                aria-live="polite"
            ></p>
        </div>

        <p class="mb-4">
            Masukkan token QR dari E-Tiket yang telah diterbitkan.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded border border-red-300 bg-red-50 p-4 text-red-700">
                <strong>Gagal memproses tiket:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 rounded border border-green-300 bg-green-50 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('petugas.validasi-tiket.verify') }}">
            @csrf

            <div class="mb-4">
                <label for="qr_token" class="mb-2 block font-semibold">
                    Token QR E-Tiket
                </label>

                <input
                    type="text"
                    id="qr_token"
                    name="qr_token"
                    value="{{ old('qr_token', request('qr_token')) }}"
                    placeholder="Masukkan token QR E-Tiket"
                    maxlength="64"
                    required
                    autocomplete="off"
                    class="w-full rounded border p-3"
                >
            </div>

            <button type="submit" class="krm-btn krm-btn-primary">
                Periksa E-Tiket
            </button>
        </form>
    </section>

    @if ($result !== null)
        <section class="krm-card payment-card mt-6">
            <h2>Hasil Pemeriksaan</h2>

            <div
                class="mt-4 rounded border p-4"
                style="{{ $result['valid']
                    ? 'background:#ecfdf5; color:#065f46;'
                    : 'background:#fef2f2; color:#991b1b;' }}"
            >
                <strong>
                    {{ $result['valid'] ? 'TIKET VALID' : 'TIKET TIDAK VALID' }}
                </strong>

                <p class="mt-2">{{ $result['message'] }}</p>
            </div>

            @if ($ticket !== null)
                <div class="mt-5 space-y-2">
                    <p>
                        <strong>Kode Tiket:</strong>
                        {{ $ticket->ticket_code }}
                    </p>

                    <p>
                        <strong>Pengunjung:</strong>
                        {{ $ticket->visitor?->name ?? '-' }}
                    </p>

                    <p>
                        <strong>Jenis Tiket:</strong>
                        {{ $ticket->item?->ticket_name ?? '-' }}
                    </p>

                    <p>
                        <strong>Tanggal Kunjungan:</strong>
                        {{ $order?->visit_date?->format('d/m/Y') ?? '-' }}
                    </p>

                    <p>
                        <strong>Status:</strong>
                        {{ strtoupper($ticket->status) }}
                    </p>
                </div>
            @endif

            @if ($result['valid'] && $ticket !== null)
                <form
                    method="POST"
                    action="{{ route('petugas.validasi-tiket.check-in') }}"
                    class="mt-5"
                    onsubmit="return confirm('Yakin ingin melakukan check-in? Tiket akan ditandai sebagai sudah digunakan.')"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="qr_token"
                        value="{{ $ticket->qr_token }}"
                    >

                    <button type="submit" class="krm-btn krm-btn-primary">
                        Konfirmasi Check-in
                    </button>
                </form>
            @endif
        </section>
    @endif

    <div class="purchase-actions">
        <a href="{{ route('petugas.dashboard') }}" class="krm-btn">
            Kembali ke Dashboard
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const startButton = document.getElementById('start-qr-scanner');
    const stopButton = document.getElementById('stop-qr-scanner');
    const tokenInput = document.getElementById('qr_token');
    const message = document.getElementById('qr-scanner-message');

    if (!startButton || !stopButton || !tokenInput || !message) {
        return;
    }

    let scanner = null;
    let scanning = false;
    let starting = false;

    async function stopScanner() {
        if (!scanner || !scanning) return;

        try {
            await scanner.stop();
        } finally {
            scanning = false;
            startButton.disabled = false;
            stopButton.disabled = true;
        }
    }

    startButton.addEventListener('click', async function () {
        if (scanning || starting) return;

        if (typeof Html5Qrcode === 'undefined') {
            message.textContent =
                'Library scanner gagal dimuat. Periksa koneksi internet.';
            return;
        }

        starting = true;
        startButton.disabled = true;
        message.textContent = 'Meminta izin kamera...';

        scanner = new Html5Qrcode('qr-reader');

        try {
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                async function (decodedText) {
                    if (!scanning) return;

                    const token = decodedText.trim();

                    if (!/^[A-Za-z0-9]{64}$/.test(token)) {
                        message.textContent =
                            'QR terbaca, tetapi format token E-Tiket tidak sesuai.';
                        return;
                    }

                    scanning = false;
                    tokenInput.value = token;
                    message.textContent =
                        'QR berhasil dibaca. Klik Periksa E-Tiket untuk validasi.';

                    try {
                        await scanner.stop();
                    } catch (error) {
                        message.textContent +=
                            ' Kamera gagal dihentikan otomatis.';
                    }

                    startButton.disabled = false;
                    stopButton.disabled = true;
                },
                function () {
                    // QR belum terdeteksi. Lanjutkan pemindaian.
                }
            );

            if (scanner.getState() === Html5QrcodeScannerState.SCANNING) {
                scanning = true;
                stopButton.disabled = false;
                message.textContent = 'Arahkan kamera ke QR Code E-Tiket.';
            }
        } catch (error) {
            message.textContent =
                'Kamera tidak dapat dibuka. Periksa izin kamera browser.';
            startButton.disabled = false;
            stopButton.disabled = true;
        } finally {
            starting = false;
        }
    });

    stopButton.addEventListener('click', async function () {
        try {
            await stopScanner();
            message.textContent = 'Kamera ditutup.';
        } catch (error) {
            message.textContent = 'Gagal menutup kamera.';
        }
    });
});
</script>
@endpush
