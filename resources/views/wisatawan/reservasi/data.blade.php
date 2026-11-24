@extends('layouts.app')

@section('title', 'Reservasi - Data Reservasi')

@push('styles')
<style>
    .reservasi-data-container {
        max-width: 900px;
        margin: 0 auto;
    }

    .reservasi-data-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .reservasi-input {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #D6E0D9;
        background: white;
        color: #193B31;
        font-size: 13px;
        border-radius: 0;
    }

    .reservasi-input:focus {
        outline: 2px solid #07513D;
        outline-offset: 1px;
    }

    .reservasi-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 500;
        color: #193B31;
    }

    .reservasi-invoice-row {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 7px 0;
        font-size: 13px;
    }

    .reservasi-invoice-row span:last-child {
        text-align: right;
        font-weight: 600;
    }

    @media (max-width: 640px) {
        .reservasi-data-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="reservasi-data-container px-4 py-10 sm:px-6">

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
            ->translatedFormat('d F Y');

        $waktuMulai = substr($sesi->jam_mulai, 0, 5);
        $waktuSelesai = substr($sesi->jam_selesai, 0, 5);

        $totalBiaya = (float) $sesi->harga_sesi;
    @endphp

    {{-- Progress Reservasi --}}
    <div class="mx-auto mb-14 max-w-3xl">
        <div class="relative flex items-start justify-between">

            <div class="absolute left-[10%] right-[10%] top-4 h-px bg-gray-200"></div>

            @foreach ($langkah as $index => $nama)
                <div class="relative z-10 flex flex-1 flex-col items-center gap-2 text-center">

                    <div class="flex h-8 w-8 items-center justify-center border text-sm font-semibold
                        {{ $index <= 2
                            ? 'border-[#07513D] bg-[#07513D] text-white'
                            : 'border-gray-200 bg-[#FBFCF8] text-gray-400' }}"
                        @if ($index === 2) aria-current="step" @endif
                    >
                        {{ $index < 2 ? '✓' : $index + 1 }}
                    </div>

                    <span class="text-[10px] sm:text-xs
                        {{ $index <= 2
                            ? 'font-semibold text-[#07513D]'
                            : 'text-gray-400' }}">
                        {{ $nama }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Judul --}}
    <div class="mb-8">
        <h1 class="krm-page-title">
            Data Reservasi
        </h1>
    </div>

    {{-- Informasi Pemesan --}}
    <section class="mb-5 border border-gray-200 bg-white p-5 sm:p-6">

        <h2 class="mb-6 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
            Informasi Pemesan
        </h2>

        {{-- Form reservasi terintegrasi dengan backend --}}
        <form
            id="formReservasi"
            method="POST"
            action="{{ route('wisatawan.reservasi.checkout') }}"
        >
            @csrf

            <input
                type="hidden"
                name="id_fasilitas"
                value="{{ $fasilitas->id_fasilitas }}"
            >

            <input
                type="hidden"
                name="id_sesi"
                value="{{ $sesi->id_sesi }}"
            >

            <input
                type="hidden"
                name="tanggal"
                value="{{ $tanggal }}"
            >

            <div class="reservasi-data-grid">

                <div class="reservasi-field">
                    <label for="nama_pemesan">
                        Nama Pemesan / Penanggung Jawab *
                    </label>
                    <input
                        id="nama_pemesan"
                        name="nama_pemesan"
                        type="text"
                        class="reservasi-input"
                        placeholder="Masukkan nama lengkap"
                        maxlength="100"
                        required
                    >
                </div>

                <div class="reservasi-field">
                    <label for="nomor_identitas">
                        No. Identitas KTP / NPWP *
                    </label>
                    <input
                        id="nomor_identitas"
                        name="nomor_identitas"
                        type="text"
                        class="reservasi-input"
                        placeholder="Masukkan nomor identitas"
                        maxlength="32"
                        required
                    >
                </div>

                <div class="reservasi-field">
                    <label for="nomor_telepon">
                        No. Telepon *
                    </label>
                    <input
                        id="nomor_telepon"
                        name="nomor_telepon"
                        type="tel"
                        class="reservasi-input"
                        placeholder="08xxxxxxxxxx"
                        maxlength="20"
                        required
                    >
                </div>

                <div class="reservasi-field">
                    <label for="email">
                        Email *
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="reservasi-input"
                        placeholder="nama@email.com"
                        maxlength="150"
                        required
                    >
                </div>

                <div class="reservasi-field" style="grid-column: 1 / -1">
                    <label for="keperluan">
                        Keperluan Kegiatan *
                    </label>
                    <input
                        id="keperluan"
                        name="keperluan"
                        type="text"
                        class="reservasi-input"
                        placeholder="Contoh: Seminar, pernikahan, kegiatan komunitas"
                        maxlength="200"
                        required
                    >
                </div>

                <div class="reservasi-field">
                    <label for="jumlah_peserta">
                        Jumlah Peserta / Pengunjung *
                    </label>
                    <input
                        id="jumlah_peserta"
                        name="jumlah_peserta"
                        type="number"
                        min="1"
                        max="{{ $fasilitas->kapasitas_fasilitas }}"
                        class="reservasi-input"
                        placeholder="Jumlah peserta"
                        required
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Maksimal {{ $fasilitas->kapasitas_fasilitas }} peserta
                    </p>
                </div>

                <div class="reservasi-field">
                    <label for="durasi">
                        Durasi Penggunaan
                    </label>
                    <input
                        id="durasi"
                        type="text"
                        class="reservasi-input bg-gray-50"
                        value="{{ $sesi->durasi_menit }} menit ({{ $sesi->nama_sesi }})"
                        readonly
                    >
                </div>

                <div class="reservasi-field" style="grid-column: 1 / -1">
                    <label for="informasi_tambahan">
                        Informasi Tambahan
                    </label>
                    <textarea
                        id="informasi_tambahan"
                        name="informasi_tambahan"
                        class="reservasi-input"
                        rows="3"
                        maxlength="1000"
                        placeholder="Kebutuhan teknis, dekorasi, dan lain-lain"
                    ></textarea>
                </div>

            </div>
        </form>
    </section>

    {{-- Pratinjau Invoice --}}
    <section class="border border-gray-200 bg-white p-5 sm:p-6">

        <h2 class="mb-5 border-b border-gray-200 pb-4 text-xs font-bold uppercase tracking-wide text-[#193B31]">
            Pratinjau Invoice
        </h2>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">No. Invoice</span>
            <span>Belum diterbitkan</span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Fasilitas</span>
            <span>{{ $fasilitas->nama_fasilitas }}</span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Tanggal</span>
            <span>{{ $tanggalFormatted }}</span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Waktu</span>
            <span>
                {{ $waktuMulai }}–{{ $waktuSelesai }} WIB
            </span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Jumlah Peserta</span>
            <span id="invoicePeserta">Belum diisi</span>
        </div>

        <div class="reservasi-invoice-row">
            <span class="text-gray-500">Keperluan</span>
            <span id="invoiceKeperluan">Belum diisi</span>
        </div>

        <div class="reservasi-invoice-row border-t border-gray-200 pt-4">
            <span class="font-bold text-[#193B31]">
                Total Pembayaran
            </span>
            <span
                id="invoiceTotal"
                class="font-bold text-[#07513D]"
            >
                Rp 0
            </span>
        </div>

        <p class="mt-4 text-xs text-gray-500">
            Total pembayaran merupakan estimasi sebelum pengajuan
            disimpan. Nomor invoice diterbitkan setelah reservasi
            berhasil dicatat. Pengajuan reservasi bukan bukti
            pembayaran lunas.
        </p>
    </section>

    {{-- Navigasi --}}
    <div class="mt-6 flex justify-end gap-3">

        <a
            href="{{ route('wisatawan.reservasi.tanggal', [
                'fasilitas' => $fasilitas->id_fasilitas,
                'tanggal' => $tanggal,
                'sesi' => $sesi->id_sesi,
            ]) }}"
            class="border border-[#07513D] px-6 py-3 text-sm font-semibold text-[#07513D]"
        >
            ← Kembali
        </a>

        <button
            type="submit"
            form="formReservasi"
            id="lanjutPembayaran"
            class="bg-[#07513D] px-6 py-3 text-sm font-semibold text-white hover:bg-[#063A2B] disabled:cursor-not-allowed disabled:opacity-50"
        >
            Simpan Pengajuan Reservasi →
        </button>

    </div>

    <p
        id="pesanForm"
        role="status"
        aria-live="polite"
        class="mt-3 text-right text-xs text-[#557066]"
    ></p>

    {{-- Hasil penyimpanan reservasi asli --}}
    <div
        id="hasilReservasi"
        class="mt-5 border border-[#D6E0D9] bg-[#F0F7F0] p-5"
        hidden
    >
        <h3 class="text-base font-bold text-[#07513D]">
            Pengajuan Reservasi Berhasil Dicatat
        </h3>

        <p class="mt-2 text-sm text-[#193B31]">
            Nomor Invoice:
            <strong id="hasilNomorInvoice"></strong>
        </p>

        <p class="mt-2 text-sm text-[#193B31]">
            Total:
            <strong id="hasilTotal"></strong>
        </p>

        <p class="mt-2 text-sm text-[#193B31]">
            Status Administrasi:
            <strong id="hasilStatus"></strong>
        </p>

        <p class="mt-3 text-xs leading-relaxed text-[#557066]">
            Reservasi sudah tercatat dalam sistem.
            Pengajuan masih menunggu proses administrasi
            dan belum dinyatakan lunas.
        </p>
    </div>

</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('formReservasi');
    const peserta = document.getElementById('jumlah_peserta');
    const keperluan = document.getElementById('keperluan');
    const pesanForm = document.getElementById('pesanForm');
    const tombolSubmit = document.getElementById('lanjutPembayaran');

    const invoicePeserta = document.getElementById('invoicePeserta');
    const invoiceKeperluan = document.getElementById('invoiceKeperluan');
    const invoiceTotal = document.getElementById('invoiceTotal');

    const hasilReservasi = document.getElementById('hasilReservasi');
    const hasilNomorInvoice = document.getElementById('hasilNomorInvoice');
    const hasilTotal = document.getElementById('hasilTotal');
    const hasilStatus = document.getElementById('hasilStatus');

    const hargaSesi = @json((int) $sesi->harga_sesi);

    let sedangMengirim = false;
    let sudahTersimpan = false;

    function formatRupiah(nominal) {
        return 'Rp ' + Number(nominal).toLocaleString('id-ID');
    }

    // Perbarui pratinjau ketika jumlah peserta berubah.
    function updateInvoice() {

        const jumlah = Number(peserta.value);

        invoicePeserta.textContent =
            Number.isInteger(jumlah) && jumlah > 0
                ? jumlah + ' peserta'
                : 'Belum diisi';

        invoiceKeperluan.textContent =
            keperluan.value.trim() || 'Belum diisi';

        const total =
            Number.isInteger(jumlah) && jumlah > 0
                ? hargaSesi * jumlah
                : 0;

        invoiceTotal.textContent = formatRupiah(total);
    }

    peserta.addEventListener('input', updateInvoice);
    keperluan.addEventListener('input', updateInvoice);

    updateInvoice();

    // Kirim form ke backend Naufal.
    form.addEventListener('submit', async function (event) {

        event.preventDefault();

        // Hindari request ganda.
        if (sedangMengirim || sudahTersimpan) {
            return;
        }

        if (!form.reportValidity()) {
            return;
        }

        sedangMengirim = true;
        tombolSubmit.disabled = true;
        tombolSubmit.textContent = 'Menyimpan Reservasi...';

        pesanForm.textContent = '';
        hasilReservasi.hidden = true;

        try {

            const formData = new FormData(form);

            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': formData.get('_token')
                },
                body: formData
            });

            const result = await response.json()
                .catch(() => ({}));

            if (!response.ok) {

                const validationErrors = Object.values(
                    result.errors || {}
                ).flat().join(' ');

                const errorMessage =
                    validationErrors ||
                    (response.status >= 500
                        ? 'Server mengalami kendala. Periksa data reservasi sebelum mencoba lagi.'
                        : result.message || 'Gagal menyimpan reservasi.');

                throw new Error(errorMessage);
            }

            const reservasi = result.reservation;

            if (!reservasi || !reservasi.code) {
                throw new Error(
                    'Respons server tidak memuat kode reservasi. Periksa data sebelum mengirim ulang.'
                );
            }

            sudahTersimpan = true;

            // Ambil URL pembayaran dari route Laravel.
            // Kode reservasi berasal dari respons backend.
            const templateUrl = @json(route(
                'facility-reservation.payment.show',
                ['code' => '__KODE_RESERVASI__']
            ));

            const halamanPembayaran = templateUrl.replace(
                '__KODE_RESERVASI__',
                encodeURIComponent(reservasi.code)
            );

            // Berikan informasi sebelum berpindah halaman.
            pesanForm.textContent =
                'Reservasi berhasil disimpan. Mengalihkan ke pembayaran QRIS...';

            tombolSubmit.textContent = 'Menuju Pembayaran...';

            // Buka halaman pembayaran reservasi yang sebenarnya.
            window.location.assign(halamanPembayaran);

        } catch (error) {

            pesanForm.textContent = error.message;

            tombolSubmit.textContent =
                'Simpan Pengajuan Reservasi →';

            // Jika koneksi terputus setelah server menyimpan,
            // status penyimpanan mungkin belum diketahui.
            // Jangan klik ulang sebelum mengecek database.
            if (error instanceof TypeError) {
                pesanForm.textContent =
                    'Koneksi bermasalah. Periksa apakah reservasi sudah tercatat sebelum mencoba lagi.';
            }

        } finally {

            sedangMengirim = false;

            if (!sudahTersimpan) {
                tombolSubmit.disabled = false;
            }
        }

    });

});
</script>
@endpush