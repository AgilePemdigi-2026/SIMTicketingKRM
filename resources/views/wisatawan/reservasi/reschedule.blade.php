@extends('layouts.app')

@section('title', 'Reschedule Reservasi')

@push('styles')
<style>
    .rs-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    @media (min-width: 1024px) {
        .rs-layout {
            grid-template-columns:
                minmax(0, 2.2fr) minmax(0, 1fr);
        }
    }

    .rs-layout > * {
        min-width: 0;
    }

    .rs-calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 2px;
    }

    .rs-day {
        width: 100%;
        height: 42px;
        border: 1px solid #D6E0D9;
        background: white;
        color: #193B31;
        font-size: 13px;
        cursor: pointer;
    }

    .rs-day:hover:not(:disabled) {
        border-color: #07513D;
        background: #EDF5ED;
    }

    .rs-day:disabled {
        background: #F1F2F3;
        color: #B9C2BD;
        cursor: not-allowed;
    }

    .rs-day.is-selected,
    .rs-day.is-selected:hover {
        background: #07513D;
        border-color: #07513D;
        color: white;
        font-weight: 700;
    }

    .rs-session-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    @media (min-width: 640px) {
        .rs-session-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .rs-session {
        padding: 12px;
        border: 1px solid #D6E0D9;
        background: white;
        color: #557066;
        font-size: 13px;
        cursor: pointer;
    }

    .rs-session.is-selected {
        border: 2px solid #07513D;
        background: #EDF5ED;
        color: #193B31;
        font-weight: 600;
    }

    .rs-current-grid {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 18px;
    }

    @media (min-width: 640px) {
        .rs-current-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .rs-summary > div + div {
        margin-top: 17px;
    }

    .rs-label {
        margin-bottom: 4px;
        color: #718078;
        font-size: 12px;
    }

    .rs-value {
        font-size: 13px;
        font-weight: 600;
        color: #193B31;
    }

    .rs-reason {
        width: 100%;
        min-height: 100px;
        border: 1px solid #D6E0D9;
        background: white;
        padding: 12px;
        font-size: 13px;
        color: #193B31;
        resize: vertical;
    }

    .rs-reason:focus {
        outline: 2px solid #07513D;
        outline-offset: 1px;
    }

    @media (max-width: 640px) {
        .rs-day {
            height: 36px;
            font-size: 12px;
        }
    }
</style>
@endpush

@section('content')

@php
    $tanggalLamaFormatted = \Carbon\Carbon::parse($tanggalSaatIni)
        ->locale('id')
        ->translatedFormat('d F Y');

    $waktuLama = substr($sesiSaatIni->jam_mulai, 0, 5)
        . '–'
        . substr($sesiSaatIni->jam_selesai, 0, 5)
        . ' WIB';
@endphp

<div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

    {{-- JUDUL --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#193B31]">
            Reschedule Reservasi
        </h1>

        <div class="mt-2 h-1 w-24 bg-[#D9E8BE]"></div>
    </div>

    {{-- PEMBERITAHUAN --}}
    <div class="mb-5 border border-[#D6E0D9] bg-[#F0F7F0] p-4 text-sm text-[#193B31]">
        <strong>Pratinjau UI.</strong>
        Jadwal saat ini digunakan sebagai contoh pengujian tampilan.
        Belum ada reservasi nyata yang diubah.
    </div>

    {{-- =====================================
         RESERVASI SAAT INI
    ====================================== --}}
    <section class="mb-5 border border-gray-200 bg-white p-5 sm:p-6">

        <h2 class="mb-6 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
            Reservasi Saat Ini
        </h2>

        <div class="rs-current-grid">

            <div>
                <p class="rs-label">No. Invoice</p>
                <p class="rs-value">Belum tersedia</p>
            </div>

            <div>
                <p class="rs-label">Fasilitas</p>
                <p class="rs-value">
                    {{ $fasilitas->nama_fasilitas }}
                </p>
            </div>

            <div>
                <p class="rs-label">Tanggal Saat Ini</p>
                <p class="rs-value">
                    {{ $tanggalLamaFormatted }}
                </p>
            </div>

            <div>
                <p class="rs-label">Waktu Saat Ini</p>
                <p class="rs-value">
                    {{ $waktuLama }}
                </p>
            </div>

            <div>
                <p class="rs-label">Pemesan</p>
                <p class="rs-value">Belum ada data reservasi</p>
            </div>

            <div>
                <p class="rs-label">Status</p>
                <p class="rs-value">Mode Pratinjau</p>
            </div>

        </div>
    </section>

    {{-- =====================================
         KALENDER DAN PANEL KANAN
    ====================================== --}}
    <div class="rs-layout">

        {{-- KOLOM KIRI --}}
        <div>

            <h2 class="mb-3 text-sm font-bold text-[#193B31]">
                Pilih Tanggal Baru
            </h2>

            <section class="border border-gray-200 bg-white p-5">

                {{-- Navigasi Bulan --}}
                <div class="mb-5 flex items-center justify-between gap-3">

                    <button
                        type="button"
                        id="rsPrevMonth"
                        class="border border-gray-200 px-3 py-2 text-xs text-[#193B31] disabled:opacity-40"
                    >
                        ← Sebelumnya
                    </button>

                    <h3
                        id="rsCalendarTitle"
                        class="text-center text-sm font-bold text-[#193B31]"
                    ></h3>

                    <button
                        type="button"
                        id="rsNextMonth"
                        class="border border-gray-200 px-3 py-2 text-xs text-[#193B31]"
                    >
                        Berikutnya →
                    </button>

                </div>

                {{-- Nama Hari --}}
                <div class="rs-calendar-grid mb-2 text-center text-xs text-gray-500">
                    <span>Min</span>
                    <span>Sen</span>
                    <span>Sel</span>
                    <span>Rab</span>
                    <span>Kam</span>
                    <span>Jum</span>
                    <span>Sab</span>
                </div>

                {{-- Kalender --}}
                <div
                    id="rsCalendarDays"
                    class="rs-calendar-grid"
                    aria-label="Kalender tanggal baru"
                ></div>

                <div class="mt-5 flex flex-wrap gap-4 text-xs text-gray-500">
                    <span>□ Belum diperiksa</span>
                    <span class="text-[#07513D]">■ Dipilih</span>
                    <span class="text-gray-400">■ Tanggal lewat</span>
                </div>

                <p class="mt-3 text-xs text-gray-500">
                    Ketersediaan tanggal dan slot belum diperiksa
                    dengan data reservasi.
                </p>

            </section>

            {{-- PILIH WAKTU BARU --}}
            <div class="rs-session-grid mt-3">

                @foreach ($sesiAktif as $sesi)

                    <button
                        type="button"
                        class="rs-session"
                        data-id="{{ $sesi->id_sesi }}"
                        data-waktu="{{ substr($sesi->jam_mulai, 0, 5) }}–{{ substr($sesi->jam_selesai, 0, 5) }} WIB"
                        aria-pressed="false"
                    >
                        {{ substr($sesi->jam_mulai, 0, 5) }}
                        –
                        {{ substr($sesi->jam_selesai, 0, 5) }}
                    </button>

                @endforeach

            </div>

        </div>

        {{-- =====================================
             KOLOM KANAN
        ====================================== --}}
        <div>

            {{-- Konfirmasi Jadwal Baru --}}
            <section class="border border-gray-200 bg-white p-5">

                <h2 class="mb-5 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                    Konfirmasi Jadwal Baru
                </h2>

                <div class="rs-summary">

                    <div>
                        <p class="rs-label">Fasilitas</p>
                        <p class="rs-value">
                            {{ $fasilitas->nama_fasilitas }}
                        </p>
                    </div>

                    <div>
                        <p class="rs-label">Tanggal Lama</p>
                        <p class="rs-value">
                            {{ $tanggalLamaFormatted }}
                        </p>
                    </div>

                    <div>
                        <p class="rs-label">Waktu Lama</p>
                        <p class="rs-value">
                            {{ $waktuLama }}
                        </p>
                    </div>

                    <div>
                        <p class="rs-label">Tanggal Baru</p>
                        <p id="rsNewDate" class="rs-value">
                            Belum dipilih
                        </p>
                    </div>

                    <div>
                        <p class="rs-label">Waktu Baru</p>
                        <p id="rsNewTime" class="rs-value">
                            Belum dipilih
                        </p>
                    </div>

                </div>

            </section>

            {{-- Alasan Reschedule --}}
            <div class="mt-4">

                <label
                    for="rsReason"
                    class="mb-2 block text-xs font-semibold text-[#193B31]"
                >
                    Alasan Reschedule *
                </label>

                <textarea
                    id="rsReason"
                    class="rs-reason"
                    maxlength="1000"
                    placeholder="Jelaskan alasan perubahan jadwal..."
                    required
                ></textarea>

            </div>

            {{-- Riwayat Perubahan --}}
            <section class="mt-4 border border-gray-200 bg-white p-5">

                <h2 class="mb-4 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                    Riwayat Perubahan
                </h2>

                <p class="text-xs leading-relaxed text-gray-500">
                    Belum ada riwayat pengajuan yang dapat ditampilkan.
                    Riwayat asli akan tersedia setelah integrasi backend.
                </p>

            </section>

            {{-- Ketentuan --}}
            <div class="mt-4 border border-[#E8D9A7] bg-[#FFF8E6] p-4">

                <p class="text-xs leading-relaxed text-[#80631A]">
                    Reservasi tidak dapat dibatalkan dan tidak dapat
                    memperoleh pengembalian dana. Perubahan jadwal
                    memerlukan persetujuan pengelola.
                </p>

            </div>

        </div>

    </div>

    {{-- =====================================
         NAVIGASI
    ====================================== --}}
    <div class="mt-6 flex flex-wrap justify-end gap-3">

        <a
            href="{{ route('wisatawan.reservasi.invoice.preview', [
                'fasilitas' => $fasilitas->id_fasilitas,
                'tanggal' => $tanggalSaatIni,
                'sesi' => $sesiSaatIni->id_sesi,
            ]) }}"
            class="border border-[#07513D] px-6 py-3 text-sm font-semibold text-[#07513D]"
        >
            Kembali
        </a>

        <button
            type="button"
            id="rsSubmit"
            disabled
            class="bg-[#07513D] px-6 py-3 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40"
        >
            Ajukan Reschedule
        </button>

    </div>

    <p
        id="rsFeedback"
        class="mt-3 text-right text-xs text-[#557066]"
        aria-live="polite"
        role="status"
    ></p>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const calendarDays = document.getElementById('rsCalendarDays');
    const calendarTitle = document.getElementById('rsCalendarTitle');

    const prevMonth = document.getElementById('rsPrevMonth');
    const nextMonth = document.getElementById('rsNextMonth');

    const newDateLabel = document.getElementById('rsNewDate');
    const newTimeLabel = document.getElementById('rsNewTime');

    const reasonInput = document.getElementById('rsReason');
    const submitButton = document.getElementById('rsSubmit');
    const feedback = document.getElementById('rsFeedback');

    const sessions = document.querySelectorAll('.rs-session');

    // Jadwal acuan untuk pengujian frontend.
    const oldDate = @json($tanggalSaatIni);
    const oldSessionId = String(@json($sesiSaatIni->id_sesi));

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let visibleMonth = new Date(
        today.getFullYear(),
        today.getMonth(),
        1
    );

    let selectedDate = null;
    let selectedDateValue = null;

    let selectedSessionId = null;
    let selectedTime = null;

    const monthFormatter = new Intl.DateTimeFormat('id-ID', {
        month: 'long',
        year: 'numeric'
    });

    const dateFormatter = new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });

    function toDateValue(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    // Update perbandingan dan validasi dasar.
    function updateSummary() {

        newDateLabel.textContent = selectedDate
            ? dateFormatter.format(selectedDate)
            : 'Belum dipilih';

        newTimeLabel.textContent =
            selectedTime || 'Belum dipilih';

        const isSameSchedule =
            selectedDateValue === oldDate &&
            selectedSessionId === oldSessionId;

        const hasReason = reasonInput.value.trim().length > 0;

        submitButton.disabled = !(
            selectedDateValue &&
            selectedSessionId &&
            hasReason &&
            !isSameSchedule
        );
    }

    function renderCalendar() {

        calendarDays.innerHTML = '';

        const year = visibleMonth.getFullYear();
        const month = visibleMonth.getMonth();

        calendarTitle.textContent =
            monthFormatter.format(visibleMonth);

        const firstDay = new Date(year, month, 1).getDay();

        const daysInMonth = new Date(
            year,
            month + 1,
            0
        ).getDate();

        // Sel kosong sebelum tanggal pertama.
        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.setAttribute('aria-hidden', 'true');
            calendarDays.appendChild(emptyCell);
        }

        for (let day = 1; day <= daysInMonth; day++) {

            const date = new Date(year, month, day);
            const dateValue = toDateValue(date);

            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'rs-day';
            button.textContent = day;

            button.setAttribute(
                'aria-label',
                dateFormatter.format(date)
            );

            if (date < today) {

                button.disabled = true;

            } else {

                button.addEventListener('click', function () {

                    selectedDate = date;
                    selectedDateValue = dateValue;

                    feedback.textContent = '';

                    renderCalendar();
                    updateSummary();
                });
            }

            const isSelected = selectedDateValue === dateValue;

            button.setAttribute(
                'aria-pressed',
                isSelected ? 'true' : 'false'
            );

            if (isSelected) {
                button.classList.add('is-selected');
            }

            calendarDays.appendChild(button);
        }

        const currentMonth = new Date(
            today.getFullYear(),
            today.getMonth(),
            1
        );

        prevMonth.disabled =
            visibleMonth.getTime() <= currentMonth.getTime();
    }

    // Navigasi bulan.
    prevMonth.addEventListener('click', function () {

        visibleMonth = new Date(
            visibleMonth.getFullYear(),
            visibleMonth.getMonth() - 1,
            1
        );

        renderCalendar();
    });

    nextMonth.addEventListener('click', function () {

        visibleMonth = new Date(
            visibleMonth.getFullYear(),
            visibleMonth.getMonth() + 1,
            1
        );

        renderCalendar();
    });

    // Pemilihan sesi baru.
    sessions.forEach(function (button) {

        button.addEventListener('click', function () {

            sessions.forEach(function (item) {
                item.classList.remove('is-selected');
                item.setAttribute('aria-pressed', 'false');
            });

            button.classList.add('is-selected');
            button.setAttribute('aria-pressed', 'true');

            selectedSessionId = button.dataset.id;
            selectedTime = button.dataset.waktu;

            feedback.textContent = '';

            updateSummary();
        });

    });

    // Validasi alasan.
    reasonInput.addEventListener('input', function () {
        feedback.textContent = '';
        updateSummary();
    });

    // Pratinjau pengajuan.
    // Tidak mengirim request ke server.
    submitButton.addEventListener('click', function () {

        if (submitButton.disabled) {
            return;
        }

        feedback.textContent =
            'Pilihan jadwal dan alasan sudah terisi. ' +
            'Ini hanya pratinjau frontend; pengajuan belum dikirim. ' +
            'Ketersediaan slot dan persetujuan Admin ' +
            'akan diproses setelah integrasi backend.';
    });

    renderCalendar();
    updateSummary();

});
</script>
@endpush
