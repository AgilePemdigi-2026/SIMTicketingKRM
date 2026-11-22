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

    .rs-notice {
        border: 1px solid #D6E0D9;
        background: #F0F7F0;
        padding: 14px 16px;
        font-size: 13px;
        color: #193B31;
        line-height: 1.6;
    }

    .rs-notice.is-warning {
        background: #FFF8E6;
        border-color: #E8D9A7;
        color: #80631A;
    }

    .rs-notice.is-error {
        background: #FFF0EE;
        border-color: #EFBBB5;
        color: #9B2922;
    }

    .rs-field-error { color: #9B2922; font-size: 12px; margin-top: 7px; }

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
    $tanggalLama = $reservation->visit_date->locale('id')->translatedFormat('d F Y');
    $waktuLama = substr((string) $reservation->start_time, 0, 5)
        . '–' . substr((string) $reservation->end_time, 0, 5) . ' WIB';
    $statusReservasi = match ($reservation->administrative_status) {
        'approved', 'confirmed' => 'Disetujui',
        'pending' => 'Menunggu Persetujuan',
        'cancelled' => 'Dibatalkan',
        'rejected' => 'Ditolak',
        default => ucfirst(str_replace('_', ' ', $reservation->administrative_status)),
    };
    $sesiPending = $pendingRequest
        ? $sessions->firstWhere('id_sesi', $pendingRequest->requested_id_sesi)
        : null;
@endphp

<div class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="mb-8">
        <h1 class="krm-page-title">
            Reschedule Reservasi
        </h1>
    </div>

    @if (session('success'))
        <div class="rs-notice mb-5" role="status">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="rs-notice is-error mb-5" role="alert">
            <strong>Pengajuan belum berhasil.</strong>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mb-5 border border-gray-200 bg-white p-5 sm:p-6">
        <h2 class="mb-6 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
            Reservasi Saat Ini
        </h2>

        <div class="rs-current-grid">
            <div>
                <p class="rs-label">No. Invoice</p>
                <p class="rs-value">{{ $reservation->invoice_number }}</p>
            </div>
            <div>
                <p class="rs-label">Fasilitas</p>
                <p class="rs-value">{{ $reservation->facility_name }}</p>
            </div>
            <div>
                <p class="rs-label">Tanggal Saat Ini</p>
                <p class="rs-value">{{ $tanggalLama }}</p>
            </div>
            <div>
                <p class="rs-label">Waktu Saat Ini</p>
                <p class="rs-value">{{ $waktuLama }}</p>
            </div>
            <div>
                <p class="rs-label">Pemesan</p>
                <p class="rs-value">{{ $reservation->customer_name }}</p>
            </div>
            <div>
                <p class="rs-label">Status Reservasi</p>
                <p class="rs-value">{{ $statusReservasi }}</p>
            </div>
        </div>
    </section>

    @if ($pendingRequest)
        <div class="rs-notice is-warning mb-5" role="status">
            <strong>Menunggu Persetujuan Admin.</strong>
            Pengajuan Reschedule sudah tersimpan. Jadwal asli tetap berlaku sampai permintaan disetujui.
            Pengajuan baru tidak dapat dikirim sebelum permintaan ini diproses.
        </div>
        <div class="rs-layout">
            <section class="border border-gray-200 bg-white p-5">
                <h2 class="mb-5 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                    Konfirmasi Jadwal yang Diajukan
                </h2>
                <div class="rs-summary">
                    <div><p class="rs-label">Fasilitas</p><p class="rs-value">{{ $reservation->facility_name }}</p></div>
                    <div><p class="rs-label">Tanggal Lama</p><p class="rs-value">{{ $tanggalLama }}</p></div>
                    <div><p class="rs-label">Waktu Lama</p><p class="rs-value">{{ $waktuLama }}</p></div>
                    <div>
                        <p class="rs-label">Tanggal Baru yang Diajukan</p>
                        <p class="rs-value">{{ $pendingRequest->requested_visit_date->locale('id')->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="rs-label">Sesi Baru yang Diajukan</p>
                        <p class="rs-value">
                            @if ($sesiPending)
                                {{ $sesiPending->nama_sesi }}
                                ({{ substr((string) $sesiPending->jam_mulai, 0, 5) }}–{{ substr((string) $sesiPending->jam_selesai, 0, 5) }} WIB)
                            @else
                                Sesi #{{ $pendingRequest->requested_id_sesi }}
                            @endif
                        </p>
                    </div>
                    <div><p class="rs-label">Alasan</p><p class="rs-value">{{ $pendingRequest->reason }}</p></div>
                    <div><p class="rs-label">Status Pengajuan</p><p class="rs-value">Menunggu Persetujuan</p></div>
                </div>
            </section>
            <section class="border border-gray-200 bg-white p-5">
                <h2 class="mb-5 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                    Riwayat Perubahan
                </h2>
                <p class="rs-label">Pengajuan terbaru</p>
                <p class="rs-value">{{ $pendingRequest->created_at->locale('id')->translatedFormat('d F Y H:i') }} WIB</p>
                <p class="rs-label mt-3">Status</p>
                <p class="rs-value">Menunggu Persetujuan Admin</p>
            </section>
        </div>
        <div class="mt-6 flex justify-end">
            <a href="{{ route('ticket-purchase.orders') }}"
               class="border border-[#07513D] px-6 py-3 text-sm font-semibold text-[#07513D]">
                Kembali ke Riwayat Pesanan
            </a>
        </div>
    @else
        <form id="rsForm" method="POST" action="{{ route('wisatawan.reservasi.reschedule.store', ['code' => $reservation->code]) }}">
            @csrf
            <input type="hidden" id="rsRequestedDate" name="requested_visit_date" value="{{ old('requested_visit_date') }}">
            <input type="hidden" id="rsRequestedSession" name="requested_id_sesi" value="{{ old('requested_id_sesi') }}">

            <div class="rs-layout">
                <div>
                    <h2 class="mb-3 text-sm font-bold text-[#193B31]">Pilih Tanggal Baru</h2>
                    <section class="border border-gray-200 bg-white p-5">
                        <div class="mb-5 flex items-center justify-between gap-3">
                            <button type="button" id="rsPrevMonth"
                                class="border border-gray-200 px-3 py-2 text-xs text-[#193B31] disabled:opacity-40">
                                ← Sebelumnya
                            </button>
                            <h3 id="rsCalendarTitle" class="text-center text-sm font-bold text-[#193B31]"></h3>
                            <button type="button" id="rsNextMonth"
                                class="border border-gray-200 px-3 py-2 text-xs text-[#193B31]">
                                Berikutnya →
                            </button>
                        </div>

                        <div class="rs-calendar-grid mb-2 text-center text-xs text-gray-500">
                            <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span>
                            <span>Kam</span><span>Jum</span><span>Sab</span>
                        </div>
                        <div id="rsCalendarDays" class="rs-calendar-grid" aria-label="Kalender tanggal baru"></div>
                        <div class="mt-5 flex flex-wrap gap-4 text-xs text-gray-500">
                            <span>□ Belum diperiksa</span>
                            <span class="text-[#07513D]">■ Dipilih</span>
                            <span class="text-gray-400">■ Tanggal tidak memenuhi syarat</span>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">
                            Pilih tanggal mulai besok. Ketersediaan kapasitas diperiksa saat pengajuan dikirim.
                        </p>
                        @error('requested_visit_date')
                            <p class="rs-field-error">{{ $message }}</p>
                        @enderror
                    </section>

                    <h2 class="mb-3 mt-5 text-sm font-bold text-[#193B31]">Pilih Sesi Baru</h2>
                    <div class="rs-session-grid mt-3" id="rsSessionList">
                        @forelse ($sessions as $session)
                            <button type="button" class="rs-session"
                                data-id="{{ $session->id_sesi }}"
                                data-waktu="{{ substr((string) $session->jam_mulai, 0, 5) }}–{{ substr((string) $session->jam_selesai, 0, 5) }} WIB"
                                aria-pressed="false">
                                {{ $session->nama_sesi }}
                                ({{ substr((string) $session->jam_mulai, 0, 5) }}–{{ substr((string) $session->jam_selesai, 0, 5) }})
                            </button>
                        @empty
                            <p class="text-xs text-gray-500">Belum ada sesi aktif untuk fasilitas ini.</p>
                        @endforelse
                    </div>
                    @error('requested_id_sesi')
                        <p class="rs-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <section class="border border-gray-200 bg-white p-5">
                        <h2 class="mb-5 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                            Konfirmasi Jadwal Baru
                        </h2>
                        <div class="rs-summary">
                            <div><p class="rs-label">Fasilitas</p><p class="rs-value">{{ $reservation->facility_name }}</p></div>
                            <div><p class="rs-label">Tanggal Lama</p><p class="rs-value">{{ $tanggalLama }}</p></div>
                            <div><p class="rs-label">Waktu Lama</p><p class="rs-value">{{ $waktuLama }}</p></div>
                            <div><p class="rs-label">Tanggal Baru</p><p id="rsNewDate" class="rs-value">Belum dipilih</p></div>
                            <div><p class="rs-label">Waktu Baru</p><p id="rsNewTime" class="rs-value">Belum dipilih</p></div>
                        </div>
                    </section>

                    <div class="mt-4">
                        <label for="rsReason" class="mb-2 block text-xs font-semibold text-[#193B31]">Alasan Reschedule *</label>
                        <textarea id="rsReason" name="reason" class="rs-reason" minlength="10" maxlength="1000"
                            placeholder="Jelaskan alasan perubahan jadwal (minimal 10 karakter)..." required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="rs-field-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <section class="mt-4 border border-gray-200 bg-white p-5">
                        <h2 class="mb-4 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
                            Riwayat Perubahan
                        </h2>
                        <p class="text-xs leading-relaxed text-gray-500">
                            Tidak ada pengajuan yang sedang menunggu persetujuan.
                            Riwayat lengkap akan ditampilkan setelah data pengajuan terdahulu diintegrasikan.
                        </p>
                    </section>

                    <div class="rs-notice is-warning mt-4">
                        Reservasi tidak dapat dibatalkan dan tidak dapat memperoleh pengembalian dana.
                        Perubahan jadwal memerlukan persetujuan pengelola.
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <a href="{{ route('ticket-purchase.orders') }}"
                   class="border border-[#07513D] px-6 py-3 text-sm font-semibold text-[#07513D]">
                    Kembali
                </a>
                <button type="submit" id="rsSubmit" disabled
                    class="bg-[#07513D] px-6 py-3 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40">
                    Ajukan Reschedule
                </button>
            </div>
            <p id="rsFeedback" class="mt-3 text-right text-xs text-[#557066]" aria-live="polite" role="status"></p>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('rsForm');
    if (!form) return; // Sudah ada pengajuan pending: tampilkan ringkasan saja.

    const calendarDays = document.getElementById('rsCalendarDays');
    const calendarTitle = document.getElementById('rsCalendarTitle');
    const prevMonth = document.getElementById('rsPrevMonth');
    const nextMonth = document.getElementById('rsNextMonth');
    const newDateLabel = document.getElementById('rsNewDate');
    const newTimeLabel = document.getElementById('rsNewTime');
    const reasonInput = document.getElementById('rsReason');
    const submitButton = document.getElementById('rsSubmit');
    const feedback = document.getElementById('rsFeedback');
    const requestedDateInput = document.getElementById('rsRequestedDate');
    const requestedSessionInput = document.getElementById('rsRequestedSession');
    const sessions = Array.from(document.querySelectorAll('.rs-session'));

    // Tanggal valid mengikuti WIB dari server, bukan zona waktu komputer pengunjung.
    const earliestDateValue = @json(now('Asia/Jakarta')->addDay()->toDateString());
    const oldDate = @json($reservation->visit_date->toDateString());
    const oldSessionId = String(@json($reservation->id_sesi));

    const monthFormatter = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
    const dateFormatter = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });

    function parseDate(value) {
        if (!value || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
        const [year, month, day] = value.split('-').map(Number);
        const parsed = new Date(year, month - 1, day);
        if (parsed.getFullYear() !== year || parsed.getMonth() !== month - 1 || parsed.getDate() !== day) return null;
        return parsed;
    }

    function toDateValue(date) {
        return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'),
            String(date.getDate()).padStart(2, '0')].join('-');
    }

    const earliestDate = parseDate(earliestDateValue);
    let selectedDateValue = requestedDateInput.value && requestedDateInput.value >= earliestDateValue
        ? requestedDateInput.value : null;
    let selectedSessionId = requestedSessionInput.value || null;
    let selectedDate = parseDate(selectedDateValue);
    let visibleMonth = new Date((selectedDate || earliestDate).getFullYear(), (selectedDate || earliestDate).getMonth(), 1);

    function updateSummary() {
        newDateLabel.textContent = selectedDate ? dateFormatter.format(selectedDate) : 'Belum dipilih';
        const chosenSession = sessions.find(button => button.dataset.id === selectedSessionId);
        newTimeLabel.textContent = chosenSession ? chosenSession.dataset.waktu : 'Belum dipilih';
        requestedDateInput.value = selectedDateValue || '';
        requestedSessionInput.value = chosenSession ? selectedSessionId : '';

        sessions.forEach(button => {
            const selected = button.dataset.id === selectedSessionId;
            button.classList.toggle('is-selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });

        const validDate = selectedDateValue && selectedDateValue >= earliestDateValue;
        const validSession = !!chosenSession;
        const reasonIsValid = reasonInput.value.trim().length >= 10;
        const sameSchedule = selectedDateValue === oldDate && selectedSessionId === oldSessionId;
        submitButton.disabled = !(validDate && validSession && reasonIsValid && !sameSchedule);
        feedback.textContent = sameSchedule ? 'Pilih tanggal atau sesi yang berbeda dari jadwal saat ini.' : '';
    }

    function renderCalendar() {
        calendarDays.innerHTML = '';
        calendarTitle.textContent = monthFormatter.format(visibleMonth);
        const year = visibleMonth.getFullYear();
        const month = visibleMonth.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        for (let i = 0; i < firstDay; i++) {
            const cell = document.createElement('div');
            cell.setAttribute('aria-hidden', 'true');
            calendarDays.appendChild(cell);
        }
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const value = toDateValue(date);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'rs-day';
            button.textContent = String(day);
            button.setAttribute('aria-label', dateFormatter.format(date));
            button.disabled = value < earliestDateValue;
            button.setAttribute('aria-pressed', String(selectedDateValue === value));
            if (selectedDateValue === value) button.classList.add('is-selected');
            if (!button.disabled) {
                button.addEventListener('click', function () {
                    selectedDate = date;
                    selectedDateValue = value;
                    renderCalendar();
                    updateSummary();
                });
            }
            calendarDays.appendChild(button);
        }
        const firstAllowedMonth = new Date(earliestDate.getFullYear(), earliestDate.getMonth(), 1);
        prevMonth.disabled = visibleMonth.getTime() <= firstAllowedMonth.getTime();
    }

    prevMonth.addEventListener('click', function () {
        visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1);
        renderCalendar();
    });
    nextMonth.addEventListener('click', function () {
        visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
        renderCalendar();
    });
    sessions.forEach(function (button) {
        button.addEventListener('click', function () {
            selectedSessionId = button.dataset.id;
            updateSummary();
        });
    });
    reasonInput.addEventListener('input', updateSummary);
    form.addEventListener('submit', function (event) {
        updateSummary();
        if (submitButton.disabled) event.preventDefault();
    });

    renderCalendar();
    updateSummary();
});
</script>
@endpush
