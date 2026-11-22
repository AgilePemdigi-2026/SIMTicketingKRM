@extends('layouts.app')

@section('title', 'Reservasi - Pilih Tanggal')

@push('styles')
<style>
    /* Layout kalender dan panel kanan */
    #jadwalLayout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
        align-items: stretch;
    }

    @media (min-width: 1024px) {
        #jadwalLayout {
            grid-template-columns:
                minmax(0, 2.2fr) minmax(0, 1fr);
        }
    }

    #jadwalLayout > section,
    #jadwalLayout > aside {
        min-width: 0;
        width: 100%;
    }

    /* Kalender tujuh kolom */
    .reservasi-kalender-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 2px;
    }

    .reservasi-kalender-grid > * {
        min-width: 0;
    }

    .reservasi-kalender-grid .tanggal-button {
        width: 100%;
        height: 42px;
        border: 1px solid #D6E0D9;
        background: white;
        color: #193B31;
        font-size: 13px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .reservasi-kalender-grid .tanggal-button:hover:not(:disabled):not(.is-selected) {
        background: #EDF5ED;
        border-color: #07513D;
    }

    .reservasi-kalender-grid .tanggal-button:disabled {
        background: #F1F2F3;
        color: #C5CBD0;
        cursor: not-allowed;
    }

    .reservasi-kalender-grid .tanggal-button.is-selected {
        background: #07513D;
        color: white;
        border-color: #07513D;
        font-weight: 700;
    }

    /* Pilihan waktu */
    .reservasi-sesi-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 12px;
    }

    @media (min-width: 640px) {
        .reservasi-sesi-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }
    }

    .reservasi-sesi-grid .sesi-option {
        padding: 12px;
        border: 1px solid #D6E0D9;
        background: white;
        color: #557066;
        font-size: 13px;
        text-align: center;
        cursor: pointer;
        transition: background 0.2s;
    }

    .reservasi-sesi-grid .sesi-option:hover {
        border-color: #07513D;
    }

    .reservasi-sesi-grid .sesi-option.is-selected {
        border: 2px solid #07513D;
        background: #EDF5ED;
        color: #193B31;
        font-weight: 600;
    }

    /* Panel ringkasan */
    .reservasi-summary-details > div + div {
        margin-top: 20px;
    }

    /* Responsif */
    @media (max-width: 640px) {
        .reservasi-kalender-grid .tanggal-button {
            height: 38px;
            font-size: 12px;
        }

        .reservasi-calendar-nav {
            padding: 8px;
            font-size: 11px;
        }
    }
</style>
@endpush

@section('content')

<div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

    @php
        $langkah = [
            'Pilih Fasilitas',
            'Pilih Tanggal',
            'Data Reservasi',
            'Pembayaran',
            'Invoice',
        ];
    @endphp

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
                        {{ $index <= 1
                            ? 'border-[#07513D] bg-[#07513D] text-white'
                            : 'border-gray-200 bg-[#FBFCF8] text-gray-400' }}"
                        @if ($index === 1)
                            aria-current="step"
                        @endif
                    >
                        {{ $index === 0 ? '✓' : $index + 1 }}
                    </div>

                    <span
                        class="text-[10px] sm:text-xs
                        {{ $index <= 1
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
         JUDUL HALAMAN
    ====================================== --}}
    <div class="mb-8">
        <h1 class="krm-page-title">
            Pilih Tanggal &amp; Waktu
        </h1>
    </div>

    {{-- =====================================
         KALENDER DAN PANEL RINGKASAN
    ====================================== --}}
    <div id="jadwalLayout">

        {{-- KOLOM KIRI --}}
        <section class="border border-gray-200 bg-white">

            {{-- Kalender --}}
            <div class="p-5">

                <div class="mb-6 flex items-center justify-between gap-3">

                    <button
                        type="button"
                        id="prevMonth"
                        class="reservasi-calendar-nav border border-gray-200 px-3 py-2 text-xs text-[#193B31] hover:bg-gray-50 disabled:opacity-40"
                    >
                        ← Sebelumnya
                    </button>

                    <h2
                        id="calendarTitle"
                        class="text-center text-sm font-bold text-[#193B31]"
                    ></h2>

                    <button
                        type="button"
                        id="nextMonth"
                        class="reservasi-calendar-nav border border-gray-200 px-3 py-2 text-xs text-[#193B31] hover:bg-gray-50"
                    >
                        Berikutnya →
                    </button>

                </div>

                {{-- Nama Hari --}}
                <div class="reservasi-kalender-grid mb-2 text-center text-xs text-gray-500">

                    <span>Min</span>
                    <span>Sen</span>
                    <span>Sel</span>
                    <span>Rab</span>
                    <span>Kam</span>
                    <span>Jum</span>
                    <span>Sab</span>

                </div>

                {{-- Angka Tanggal --}}
                <div
                    id="calendarDays"
                    class="reservasi-kalender-grid"
                    aria-label="Kalender reservasi"
                ></div>

                {{-- Keterangan --}}
                <div class="mt-5 flex flex-wrap gap-4 text-xs text-gray-500">

                    <span>□ Belum diperiksa</span>

                    <span class="text-[#07513D]">
                        ■ Dipilih
                    </span>

                    <span class="text-gray-400">
                        ■ Tanggal lewat
                    </span>

                </div>

                <p class="mt-3 text-xs leading-relaxed text-gray-500">
                    Ketersediaan tanggal belum diverifikasi
                    dengan data reservasi.
                </p>

            </div>

            {{-- =====================================
                 PILIH WAKTU
            ====================================== --}}
            <div class="border-t border-gray-200 p-5">

                <h3 class="mb-4 border-b border-gray-200 pb-3 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                    Pilih Waktu
                </h3>

                <div class="reservasi-sesi-grid">

                    @foreach ($sesiAktif as $sesi)

                        <button
                            type="button"
                            class="sesi-option"
                            data-id="{{ $sesi->id_sesi }}"
                            data-waktu="{{ substr($sesi->jam_mulai, 0, 5) }}–{{ substr($sesi->jam_selesai, 0, 5) }} WIB"
                            data-harga="{{ number_format((float) $sesi->harga_sesi, 0, ',', '.') }}"
                            aria-pressed="false"
                        >
                            {{ substr($sesi->jam_mulai, 0, 5) }}
                            –
                            {{ substr($sesi->jam_selesai, 0, 5) }}
                        </button>

                    @endforeach

                </div>

            </div>

        </section>

        {{-- =====================================
             KOLOM KANAN - PILIHAN SAAT INI
        ====================================== --}}
        <aside class="border border-gray-200 bg-white p-5">

            <h2 class="border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                Pilihan Saat Ini
            </h2>

            <div class="reservasi-summary-details mt-6">

                <div>
                    <p class="mb-1 text-xs text-gray-500">
                        Fasilitas
                    </p>

                    <p class="text-sm font-bold text-[#193B31]">
                        {{ $fasilitas->nama_fasilitas }}
                    </p>
                </div>

                <div>
                    <p class="mb-1 text-xs text-gray-500">
                        Tanggal Dipilih
                    </p>

                    <p
                        id="summaryDate"
                        class="text-sm font-semibold text-[#193B31]"
                    >
                        Belum dipilih
                    </p>
                </div>

                <div>
                    <p class="mb-1 text-xs text-gray-500">
                        Waktu
                    </p>

                    <p
                        id="summaryTime"
                        class="text-sm font-semibold text-[#193B31]"
                    >
                        Belum dipilih
                    </p>
                </div>

                <div>
                    <p class="mb-1 text-xs text-gray-500">
                        Harga Sewa
                    </p>

                    <p
                        id="summaryPrice"
                        class="text-sm font-bold text-[#193B31]"
                    >
                        Belum dipilih
                    </p>
                </div>

            </div>

        </aside>

    </div>

    {{-- =====================================
         TOMBOL NAVIGASI
    ====================================== --}}
    <div class="mt-6 flex items-center justify-end gap-3">

        <a
            href="{{ route('wisatawan.reservasi.index') }}"
            class="border border-[#07513D] px-6 py-3 text-sm font-semibold text-[#07513D] hover:bg-[#F0F7F0]"
        >
            ← Kembali
        </a>

        <button
            type="button"
            id="lanjutButton"
            disabled
            class="bg-[#07513D] px-7 py-3 text-sm font-semibold text-white transition hover:bg-[#063A2B] disabled:cursor-not-allowed disabled:opacity-40"
        >
            Lanjut →
        </button>

    </div>

    <p
        id="reservationFeedback"
        class="mt-3 text-right text-xs text-gray-500"
        aria-live="polite"
    ></p>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ==========================================
    // ELEMEN HALAMAN
    // ==========================================

    const calendarDays = document.getElementById('calendarDays');
    const calendarTitle = document.getElementById('calendarTitle');
    const prevMonth = document.getElementById('prevMonth');
    const nextMonth = document.getElementById('nextMonth');
    const lanjutButton = document.getElementById('lanjutButton');

    const summaryDate = document.getElementById('summaryDate');
    const summaryTime = document.getElementById('summaryTime');
    const summaryPrice = document.getElementById('summaryPrice');

    const sessionButtons = document.querySelectorAll('.sesi-option');

    const feedback = document.getElementById('reservationFeedback');

    // ==========================================
    // DATA TANGGAL DAN PILIHAN
    // ==========================================

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
    let selectedPrice = null;

    const monthFormatter = new Intl.DateTimeFormat('id-ID', {
        month: 'long',
        year: 'numeric'
    });

    const dateFormatter = new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });

    // ==========================================
    // FORMAT TANGGAL YYYY-MM-DD
    // ==========================================

    function toDateValue(date) {
        const year = date.getFullYear();

        const month = String(
            date.getMonth() + 1
        ).padStart(2, '0');

        const day = String(
            date.getDate()
        ).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }

    // ==========================================
    // PERBARUI PANEL RINGKASAN
    // ==========================================

    function updateSummary() {

        summaryDate.textContent = selectedDate
            ? dateFormatter.format(selectedDate)
            : 'Belum dipilih';

        summaryTime.textContent =
            selectedTime || 'Belum dipilih';

        summaryPrice.textContent = selectedPrice !== null
            ? `Rp ${selectedPrice}`
            : 'Belum dipilih';

        lanjutButton.disabled = !(
            selectedDateValue && selectedSessionId
        );
    }

    // ==========================================
    // TAMPILKAN KALENDER
    // ==========================================

    function renderCalendar() {

        calendarDays.innerHTML = '';

        const year = visibleMonth.getFullYear();
        const month = visibleMonth.getMonth();

        calendarTitle.textContent =
            monthFormatter.format(visibleMonth);

        const firstDay = new Date(
            year,
            month,
            1
        ).getDay();

        const daysInMonth = new Date(
            year,
            month + 1,
            0
        ).getDate();

        // Ruang kosong sebelum tanggal 1
        for (let i = 0; i < firstDay; i++) {
            const kosong = document.createElement('div');
            kosong.setAttribute('aria-hidden', 'true');
            calendarDays.appendChild(kosong);
        }

        // Buat tombol tanggal
        for (let day = 1; day <= daysInMonth; day++) {

            const date = new Date(year, month, day);
            const dateValue = toDateValue(date);

            const button = document.createElement('button');

            button.type = 'button';
            button.textContent = day;
            button.className = 'tanggal-button';

            button.setAttribute(
                'aria-label',
                dateFormatter.format(date)
            );

            button.setAttribute(
                'aria-pressed',
                selectedDateValue === dateValue
                    ? 'true'
                    : 'false'
            );

            // Tanggal yang sudah lewat
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

            // Warna hijau untuk tanggal terpilih
            if (selectedDateValue === dateValue) {
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

    // ==========================================
    // NAVIGASI BULAN
    // ==========================================

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

    // ==========================================
    // PILIH SESI WAKTU
    // ==========================================

    function selectSession(button) {

        sessionButtons.forEach(function (item) {

            item.classList.remove('is-selected');

            item.setAttribute(
                'aria-pressed',
                'false'
            );
        });

        button.classList.add('is-selected');

        button.setAttribute(
            'aria-pressed',
            'true'
        );

        selectedSessionId = button.dataset.id;
        selectedTime = button.dataset.waktu;
        selectedPrice = button.dataset.harga;

        feedback.textContent = '';

        updateSummary();
    }

    sessionButtons.forEach(function (button) {

        button.addEventListener('click', function () {
            selectSession(button);
        });

    });

    // ==========================================
    // PULIHKAN PILIHAN SAAT KEMBALI
    // ==========================================

    function restoreSelection() {

        const params = new URLSearchParams(
            window.location.search
        );

        const tanggalDariURL = params.get('tanggal');
        const sesiDariURL = params.get('sesi');

        // Kembalikan tanggal sebelumnya
        if (
            tanggalDariURL &&
            /^\d{4}-\d{2}-\d{2}$/.test(tanggalDariURL)
        ) {

            const [tahun, bulan, hari] = tanggalDariURL
                .split('-')
                .map(Number);

            const tanggalLama = new Date(
                tahun,
                bulan - 1,
                hari
            );

            tanggalLama.setHours(0, 0, 0, 0);

            // Pastikan tanggal valid dan belum lewat
            if (
                toDateValue(tanggalLama) === tanggalDariURL &&
                tanggalLama.getTime() >= today.getTime()
            ) {

                selectedDate = tanggalLama;
                selectedDateValue = tanggalDariURL;

                visibleMonth = new Date(
                    tahun,
                    bulan - 1,
                    1
                );

            }
        }

        // Kembalikan sesi hanya jika tanggal valid
        if (selectedDateValue && sesiDariURL) {

            const tombolSesiLama = Array.from(
                sessionButtons
            ).find(function (button) {

                return button.dataset.id === sesiDariURL;

            });

            if (tombolSesiLama) {
                selectSession(tombolSesiLama);
            }
        }
    }

    // ==========================================
    // NAVIGASI KE DATA RESERVASI
    // ==========================================

    lanjutButton.addEventListener('click', function () {

        if (!selectedDateValue || !selectedSessionId) {
            return;
        }

        const tujuan = new URL(
            @json(route('wisatawan.reservasi.data', $fasilitas->id_fasilitas)),
            window.location.origin
        );

        tujuan.searchParams.set(
            'tanggal',
            selectedDateValue
        );

        tujuan.searchParams.set(
            'sesi',
            selectedSessionId
        );

        window.location.assign(
            tujuan.toString()
        );
    });

    // ==========================================
    // INISIALISASI HALAMAN
    // ==========================================

    // Pulihkan pilihan dulu saat halaman dibuka
    restoreSelection();

    // Baru tampilkan kalender dan ringkasan
    renderCalendar();
    updateSummary();

});
</script>
@endpush
