@extends('layouts.petugas')

@section('title', 'Scan & Validasi Tiket - Petugas')

@push('styles')
<style>
    .scan-page { width: min(100% - 36px, 1510px); margin: 0 auto; padding: 30px 0 70px; color: #153e32; font-family: 'Plus Jakarta Sans', sans-serif; }
    .scan-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; padding-bottom: 30px; margin-bottom: 24px; border-bottom: 1px solid #bdd0c3; }
    .scan-header h1 { margin: 0; font-size: clamp(23px, 2.1vw, 30px); line-height: 1.3; font-weight: 800; letter-spacing: -.6px; }
    .scan-header-tag { flex: none; padding: 9px 13px; color: #597166; border: 1px solid #d6e1d9; background: #f7faf7; font-size: 12px; font-weight: 600; }
    .scan-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 24px; align-items: start; }
    .scan-col { min-width: 0; display: grid; gap: 20px; }
    .scan-card { background: #fff; padding: 22px 24px; border: 1px solid #d7e3d9; min-width: 0; }
    .scan-section-heading { font-weight: 800; font-size: 14px; letter-spacing: .6px; margin: 0; }
    .scan-section-rule { border: 0; border-top: 1px solid #d7e2da; margin: 15px 0 18px; }
    .scan-camera { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 395px; overflow: hidden; border: 1px dashed #abcbb6; background: #eff6f1; }
    .scan-reader { position: absolute; inset: 0; z-index: 1; overflow: hidden; }
    .scan-reader video { height: 100% !important; width: 100% !important; object-fit: cover !important; }
    .scan-reader img { max-width: 100%; }
    .scan-camera-frame { position: absolute; width: min(51%, 275px); aspect-ratio: 1; left: 50%; top: 45%; transform: translate(-50%, -50%); z-index: 3; pointer-events: none; }
    .scan-corner { position: absolute; width: 53px; height: 53px; border-color: #277b59; border-style: solid; border-width: 0; }
    .scan-corner.tl { top: 0; left: 0; border-top-width: 3px; border-left-width: 3px; }
    .scan-corner.tr { top: 0; right: 0; border-top-width: 3px; border-right-width: 3px; }
    .scan-corner.bl { bottom: 0; left: 0; border-bottom-width: 3px; border-left-width: 3px; }
    .scan-corner.br { bottom: 0; right: 0; border-bottom-width: 3px; border-right-width: 3px; }
    .scan-camera-center { position: absolute; top: 46%; left: 50%; transform: translate(-50%, -50%); display: grid; justify-items: center; gap: 7px; color: #627e70; z-index: 2; pointer-events: none; text-align: center; font-size: 12px; }
    .scan-camera[data-active="true"] .scan-camera-center { display: none; }
    .scan-camera-actions { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; position: absolute; bottom: 16px; left: 12px; right: 12px; z-index: 4; gap: 8px; }
    .scan-btn { min-height: 42px; display: inline-flex; justify-content: center; align-items: center; gap: 9px; background: #07513d; color: #fff; border: 1px solid #07513d; padding: 10px 16px; font-family: inherit; font-size: 13px; font-weight: 750; cursor: pointer; text-decoration: none; border-radius: 0; }
    .scan-btn:hover:not(:disabled) { background: #063e30; color: #fff; }
    .scan-btn.secondary { color: #07513d; background: #fff; }
    .scan-btn.secondary:hover:not(:disabled) { background: #edf5ee; }
    .scan-btn:disabled { cursor: not-allowed; opacity: .55; }
    .scan-btn svg { width: 17px; height: 17px; flex: none; }
    .scan-message { margin: 0; padding: 12px 16px; font-size: 12px; color: #597166; min-height: 42px; }
    .scan-form-label { display: block; margin-bottom: 10px; font-size: 12px; font-weight: 650; color: #526d60; }
    .scan-manual-form { display: flex; gap: 8px; }
    .scan-input { flex: 1; min-width: 0; min-height: 44px; border: 1px solid #cbd9ce; background: #fff; padding: 10px 13px; font-size: 13px; color: #23493b; font-family: inherit; border-radius: 0; }
    .scan-input:focus { outline: 2px solid #9acba3; outline-offset: 1px; }
    .scan-hint { font-size: 11px; color: #84988e; margin: 11px 0 0; }
    .scan-empty { border: 1px dashed #c4d9cc; background: #fff; min-height: 100%; height: 580px; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 26px; text-align: center; gap: 12px; color: #718c7b; }
    .scan-empty .scan-empty-title { font-size: 17px; font-weight: 600; color: #5b7968; margin: 0; }
    .scan-empty p { font-size: 12px; line-height: 1.6; max-width: 350px; margin: 0; color: #9aafa1; }
    .scan-card-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
    .scan-badge { font-size: 11px; border: 1px solid #bfdcc8; background: #e2f2e8; color: #176b44; padding: 7px 10px; font-weight: 700; text-transform: uppercase; }
    .scan-badge.invalid { background: #fff0ee; color: #9f3833; border-color: #efc6bf; }
    .scan-details { display: grid; gap: 0; margin: 0; }
    .scan-details > div { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; padding: 12px 0; border-bottom: 1px solid #edf0ed; }
    .scan-details dt { font-size: 12px; color: #819489; flex: 0 0 42%; }
    .scan-details dd { font-size: 12px; color: #244638; font-weight: 750; text-align: right; overflow-wrap: anywhere; margin: 0; }
    .scan-checks { list-style: none; margin: 0; padding: 0; display: grid; gap: 14px; }
    .scan-checks li { display: flex; align-items: center; gap: 10px; font-size: 12px; color: #325849; }
    .scan-check-icon { display: grid; place-items: center; width: 22px; height: 22px; background: #e0f2e7; color: #188553; font-weight: 800; flex: none; }
    .scan-check-icon.no { background: #ffece9; color: #ae4940; }
    .scan-result-actions { display: flex; gap: 10px; }
    .scan-result-actions form { display: flex; flex: 1; }
    .scan-result-actions .scan-btn.primary { flex: 1; }
    .scan-feedback { border: 1px solid #efd4ca; background: #fff6f2; color: #a04133; padding: 15px 18px; font-size: 13px; line-height: 1.6; }
    .scan-feedback.good { border-color: #c4e0cb; background: #edf7f0; color: #27644a; }
    .scan-options { display: grid; gap: 10px; }
    .scan-option { display: flex; gap: 14px; align-items: center; justify-content: space-between; padding: 13px; border: 1px solid #d9e5dd; }
    .scan-option strong { font-size: 13px; }
    .scan-option span { display: block; margin-top: 5px; font-size: 11px; color: #789184; }
    .scan-option form { flex: none; }
    @media (max-width: 830px) { .scan-layout { grid-template-columns: 1fr; } .scan-empty { height: 280px; } .scan-camera { min-height: 360px; } }
    @media (max-width: 480px) { .scan-page { width: min(100% - 24px, 1510px); } .scan-card { padding: 17px; } .scan-header { padding-bottom: 20px; } .scan-camera { min-height: 330px; } .scan-camera-frame { width: min(65%, 255px); } .scan-manual-form { flex-direction: column; } .scan-details dt { flex-basis: 42%; } }

    /* ===== MODAL KONFIRMASI CHECK-IN ===== */
    .scan-modal-backdrop[hidden] {
        display: none !important;
    }

    .scan-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(7, 36, 27, 0.5);
    }

    .scan-modal-box {
        width: 100%;
        max-width: 470px;
        background: #ffffff;
        border: 1px solid #d7e3d9;
        border-radius: 8px;
        padding: 32px;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .scan-modal-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 20px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #e5f3e9;
        color: #18734f;
    }

    .scan-modal-title {
        margin: 0 0 12px;
        color: #153e32;
        font-size: 23px;
        font-weight: 800;
    }

    .scan-modal-description {
        margin: 0;
        color: #718079;
        font-size: 14px;
        line-height: 1.7;
    }

    .scan-modal-actions {
        display: flex;
        gap: 12px;
        margin-top: 28px;
    }

    .scan-modal-actions button {
        flex: 1;
    }

    @media (max-width: 480px) {
        .scan-modal-box {
            padding: 24px 18px;
        }
    }
</style>
@endpush

@section('content')
<div class="scan-page">
    <header class="scan-header">
        <h1>Scan &amp; Validasi Tiket</h1>
        <span class="scan-header-tag">QR Code Scanner</span>
    </header>

    @if ($errors->any())
        <div class="scan-feedback" role="alert" style="margin-bottom:20px;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="scan-layout">
        <div class="scan-col">
            <section aria-label="Scanner QR E-Tiket">
                <div id="scan-camera" class="scan-camera" data-active="false">
                    <div id="qr-reader" class="scan-reader"></div>
                    <div class="scan-camera-frame" aria-hidden="true">
                        <span class="scan-corner tl"></span><span class="scan-corner tr"></span>
                        <span class="scan-corner bl"></span><span class="scan-corner br"></span>
                    </div>
                    <div class="scan-camera-center" aria-hidden="true">
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5"/>
                            <path d="M9 12h6"/>
                        </svg>
                        <span>Arahkan kamera ke QR Code</span>
                    </div>
                    <div class="scan-camera-actions">
                        <button id="start-qr-scanner" type="button" class="scan-btn">Aktifkan Kamera</button>
                        <button id="stop-qr-scanner" type="button" class="scan-btn secondary" disabled>Tutup Kamera</button>
                    </div>
                </div>
                <p id="qr-scanner-message" class="scan-message" aria-live="polite" role="status">Kamera hanya digunakan untuk memindai QR E-Tiket final.</p>
            </section>

            <section class="scan-card">
                <h2 class="scan-section-heading">ATAU INPUT MANUAL</h2>
                <hr class="scan-section-rule">
                <form action="{{ route('petugas.validasi-tiket.index') }}" method="GET" class="scan-manual-form">
                    <label for="scan-manual-code" class="sr-only">Kode E-Tiket atau booking</label>
                    <input id="scan-manual-code" name="code" class="scan-input" value="{{ $code ?? '' }}" maxlength="100" autocomplete="off" placeholder="Masukkan kode E-Tiket / booking..." required>
                    <button class="scan-btn" type="submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg>
                        Cari
                    </button>
                </form>
                <p class="scan-hint">Bisa menggunakan kode E-Tiket, UUID booking, atau kode CASH-xxxx. Untuk booking berisi beberapa tiket, pilih pengunjung yang tepat.</p>
            </section>
        </div>

        <div class="scan-col">
            @if (($matches ?? collect())->count() > 1 && $ticket === null)
                <section class="scan-card">
                    <h2 class="scan-section-heading">PILIH E-TIKET PENGUNJUNG</h2>
                    <hr class="scan-section-rule">
                    <p class="scan-hint" style="margin:0 0 18px;">Ditemukan {{ $matches->count() }} tiket dalam booking. Check-in dilakukan per E-Tiket, bukan sekaligus.</p>
                    <div class="scan-options">
                        @foreach ($matches as $candidate)
                            <div class="scan-option">
                                <div>
                                    <strong>{{ $candidate->visitor?->name ?? 'Pengunjung' }}</strong>
                                    <span>{{ $candidate->item?->ticket_name ?? 'Tiket' }} · {{ $candidate->ticket_code }} · {{ strtoupper($candidate->status) }}</span>
                                </div>
                                <form method="POST" action="{{ route('petugas.validasi-tiket.verify') }}">
                                    @csrf
                                    <input type="hidden" name="qr_token" value="{{ $candidate->qr_token }}">
                                    <button type="submit" class="scan-btn">Periksa</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </section>
            @elseif ($ticket !== null && $result !== null)
                <section class="scan-card">
                    <div class="scan-card-header">
                        <h2 class="scan-section-heading">INFORMASI TIKET</h2>
                        <span class="scan-badge {{ $result['valid'] ? '' : 'invalid' }}">{{ $result['valid'] ? 'VALID' : 'TIDAK VALID' }}</span>
                    </div>
                    <hr class="scan-section-rule">
                    <dl class="scan-details">
                        <div><dt>Nama Pengunjung</dt><dd>{{ $ticket->visitor?->name ?? '-' }}</dd></div>
                        <div><dt>Kode Booking</dt><dd>{{ $order?->code ?? '-' }}</dd></div>
                        <div><dt>Kode E-Tiket</dt><dd>{{ $ticket->ticket_code }}</dd></div>
                        <div><dt>Jenis Tiket</dt><dd>{{ $ticket->item?->ticket_name ?? '-' }}</dd></div>
                        <div><dt>Jumlah Tiket</dt><dd>{{ $order?->issuedTickets?->count() ?? 1 }} tiket</dd></div>
                        <div><dt>Tanggal Kunjungan</dt><dd>{{ $order?->visit_date?->format('d M Y') ?? '-' }}</dd></div>
                        <div><dt>Status Pembayaran</dt><dd>{{ $order?->payment?->status === 'paid' ? 'LUNAS' : 'BELUM LUNAS' }}</dd></div>
                        <div><dt>Status Tiket</dt><dd>{{ $ticket->status === 'used' ? 'Sudah Digunakan' : ($ticket->status === 'active' ? 'Belum Digunakan' : strtoupper($ticket->status)) }}</dd></div>
                    </dl>
                </section>

                <section class="scan-card">
                    <h2 class="scan-section-heading">CHECKLIST VALIDASI</h2>
                    <hr class="scan-section-rule">
                    @php($checks = $result['checks'] ?? [])
                    <ul class="scan-checks">
                        @foreach ([
                            'ticket' => 'Tiket valid?',
                            'payment' => 'Pembayaran sudah lunas?',
                            'date' => 'Tanggal kunjungan sesuai hari ini?',
                            'unused' => 'Tiket belum pernah dipakai?',
                        ] as $key => $label)
                            <li>
                                <span class="scan-check-icon {{ ($checks[$key] ?? false) ? '' : 'no' }}" aria-hidden="true">{{ ($checks[$key] ?? false) ? '✓' : '×' }}</span>
                                <span>{{ $label }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>

                @if (! $result['valid'])
                    <div class="scan-feedback" role="alert">{{ $result['message'] }}</div>
                @endif

                <div class="scan-result-actions">
                    @if ($result['valid'])
                        <form
                            id="scan-checkin-form"
                            method="POST"
                            action="{{ route('petugas.validasi-tiket.check-in') }}"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="qr_token"
                                value="{{ $ticket->qr_token }}"
                            >

                            <button
                                type="button"
                                id="open-checkin-modal"
                                class="scan-btn primary"
                            >
                                <svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <path d="m5 12 4 4L19 6"/>
                                </svg>

                                Konfirmasi Check-In
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('petugas.validasi-tiket.index') }}" class="scan-btn secondary">Reset</a>
                </div>
            @elseif ($result !== null)
                <div class="scan-feedback" role="alert">{{ $result['message'] }}</div>
                <a class="scan-btn secondary" href="{{ route('petugas.validasi-tiket.index') }}">Reset Pencarian</a>
            @else
                <section class="scan-empty" aria-live="polite">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#98b1a1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5"/>
                        <path d="M9 12h6"/>
                    </svg>
                    <p class="scan-empty-title">Siap memindai tiket</p>
                    <p>Arahkan kamera ke QR Code E-Tiket pengunjung, atau masukkan kode transaksi secara manual.</p>
                </section>
            @endif
        </div>
    </div>

    @if (($result['valid'] ?? false) && $ticket !== null)

    <div
        id="scan-checkin-modal"
        class="scan-modal-backdrop"
        role="dialog"
        aria-modal="true"
        aria-labelledby="scan-modal-heading"
        hidden
    >
        <div class="scan-modal-box">

            <div class="scan-modal-icon">
                <svg width="32" height="32"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                    <circle cx="12" cy="12" r="10"/>
                </svg>
            </div>

            <h2 id="scan-modal-heading" class="scan-modal-title">
                Konfirmasi Check-In
            </h2>

            <p class="scan-modal-description">
                Apakah kamu yakin ingin melakukan check-in
                untuk pengunjung ini?
            </p>

            <p class="scan-modal-description"
            style="margin-top:12px; font-weight:600;">
                {{ $ticket->visitor?->name ?? 'Pengunjung' }}
            </p>

            <p class="scan-modal-description"
            style="margin-top:12px;">
                Setelah dikonfirmasi, E-Tiket akan ditandai
                sebagai sudah digunakan dan tidak dapat
                digunakan kembali.
            </p>

            <div class="scan-modal-actions">

                <button
                    type="button"
                    id="cancel-checkin-modal"
                    class="scan-btn secondary"
                >
                    Batal
                </button>

                <button
                    type="button"
                    id="confirm-checkin-modal"
                    class="scan-btn"
                >
                    Ya, Check-In
                </button>

            </div>
        </div>
    </div>
    @endif

    {{-- Form QR hasil kamera tetap menggunakan route verify lama (POST + CSRF). --}}
    <form id="scan-camera-verify" method="POST" action="{{ route('petugas.validasi-tiket.verify') }}" hidden>
        @csrf
        <input id="scan-camera-token" type="hidden" name="qr_token" value="">
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const start = document.getElementById('start-qr-scanner');
    const stop = document.getElementById('stop-qr-scanner');
    const wrapper = document.getElementById('scan-camera');
    const message = document.getElementById('qr-scanner-message');
    const verifyForm = document.getElementById('scan-camera-verify');
    const tokenField = document.getElementById('scan-camera-token');
    if (!start || !stop || !wrapper || !message || !verifyForm || !tokenField) return;

    let scanner = null;
    let scanning = false;
    let starting = false;
    let submitting = false;

    async function stopCamera() {
        if (scanner && scanning) {
            await scanner.stop();
        }
        scanning = false;
        wrapper.dataset.active = 'false';
        start.disabled = false;
        stop.disabled = true;
    }

    start.addEventListener('click', async function () {
        if (scanning || starting || submitting) return;
        if (typeof Html5Qrcode === 'undefined') {
            message.textContent = 'Library scanner tidak termuat. Periksa koneksi internet atau gunakan input manual.';
            return;
        }
        starting = true;
        start.disabled = true;
        message.textContent = 'Meminta izin kamera...';
        scanner = new Html5Qrcode('qr-reader');
        try {
            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 230, height: 230 } },
                async function (decodedText) {
                    if (submitting || !scanning) return;
                    const token = decodedText.trim();
                    if (!/^[A-Za-z0-9]{64}$/.test(token)) {
                        message.textContent = 'QR terbaca, tetapi bukan token E-Tiket final. Gunakan QR dari E-Tiket yang sudah Lunas.';
                        return;
                    }
                    submitting = true;
                    tokenField.value = token;
                    message.textContent = 'QR terbaca. Memvalidasi tiket...';
                    try { await stopCamera(); } catch (error) { /* Navigasi tetap aman. */ }
                    verifyForm.requestSubmit();
                },
                function () { /* Lanjut memindai. */ }
            );
            scanning = true;
            wrapper.dataset.active = 'true';
            stop.disabled = false;
            message.textContent = 'Arahkan kamera ke QR E-Tiket final.';
        } catch (error) {
            scanning = false;
            wrapper.dataset.active = 'false';
            start.disabled = false;
            stop.disabled = true;
            message.textContent = 'Kamera tidak dapat dibuka. Periksa izin kamera atau gunakan input manual.';
        } finally {
            starting = false;
        }
    });

    stop.addEventListener('click', async function () {
        try {
            await stopCamera();
            message.textContent = 'Kamera ditutup.';
        } catch (error) {
            message.textContent = 'Gagal menutup kamera.';
        }
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('scan-checkin-form');
    const openButton = document.getElementById('open-checkin-modal');
    const modal = document.getElementById('scan-checkin-modal');
    const cancelButton = document.getElementById('cancel-checkin-modal');
    const confirmButton = document.getElementById('confirm-checkin-modal');

    if (!form || !openButton || !modal ||
        !cancelButton || !confirmButton) {
        return;
    }

    function openModal() {
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        cancelButton.focus();
    }

    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = '';
        openButton.focus();
    }

    openButton.addEventListener('click', openModal);

    cancelButton.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });

    confirmButton.addEventListener('click', function () {
        confirmButton.disabled = true;

        // Mengirim qr_token asli ke backend check-in.
        form.requestSubmit();
    });

});
</script>

@endpush
