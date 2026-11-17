@extends('layouts.admin')

@section('title', 'Manajemen Reservasi - Kebun Raya Mangrove')

@section('content')
@php
    $reservasi = [
        [
            'invoice' => 'INV-2026-0047',
            'fasilitas' => 'Indoor / Auditorium',
            'pemesan' => 'PT. Cahaya Nusantara',
            'tanggal' => '05 Okt 2026',
            'tanggalFilter' => '2026-10-05',
            'jam' => '10.00–13.00',
            'total' => 1500000,
            'pembayaran' => 'Lunas',
            'status' => 'Terjadwal'
        ],
        [
            'invoice' => 'INV-2026-0031',
            'fasilitas' => 'Area Outdoor',
            'pemesan' => 'SMA Negeri 1 Surabaya',
            'tanggal' => '01 Sep 2026',
            'tanggalFilter' => '2026-09-01',
            'jam' => 'Seharian',
            'total' => 800000,
            'pembayaran' => 'Lunas',
            'status' => 'Terjadwal'
        ],
        [
            'invoice' => 'INV-2026-0036',
            'fasilitas' => 'Area Outdoor',
            'pemesan' => 'Komunitas Mangrove Jaya',
            'tanggal' => '06 Sep 2026',
            'tanggalFilter' => '2026-09-06',
            'jam' => 'Seharian',
            'total' => 800000,
            'pembayaran' => 'Lunas',
            'status' => 'Terjadwal'
        ],
        [
            'invoice' => 'INV-2026-0040',
            'fasilitas' => 'Area Pre-wedding',
            'pemesan' => 'Keluarga Hartono',
            'tanggal' => '10 Sep 2026',
            'tanggalFilter' => '2026-09-10',
            'jam' => '06.00–10.00',
            'total' => 2000000,
            'pembayaran' => 'Lunas',
            'status' => 'Terjadwal'
        ],
        [
            'invoice' => 'INV-2026-0019',
            'fasilitas' => 'Pujasera / Food Court',
            'pemesan' => 'CV. Rasa Nusantara',
            'tanggal' => '14 Sep 2026',
            'tanggalFilter' => '2026-09-14',
            'jam' => 'Seharian',
            'total' => 300000,
            'pembayaran' => 'Lunas',
            'status' => 'Terjadwal'
        ],
    ];

    $permintaan = [
        ['invoice' => 'INV-2026-0036', 'pemesan' => 'Komunitas Mangrove Jaya', 'fasilitas' => 'Area Outdoor', 'lama' => '06 Sep 2026', 'baru' => '12 Sep 2026', 'alasan' => 'Jadwal acara komunitas bergeser', 'status' => 'Menunggu Persetujuan'],
        ['invoice' => 'INV-2026-0040', 'pemesan' => 'Keluarga Hartono', 'fasilitas' => 'Area Pre-wedding', 'lama' => '10 Sep 2026', 'baru' => '17 Sep 2026', 'alasan' => 'Cuaca diperkirakan hujan', 'status' => 'Menunggu Persetujuan'],
        ['invoice' => 'INV-2026-0019', 'pemesan' => 'CV. Rasa Nusantara', 'fasilitas' => 'Pujasera / Food Court', 'lama' => '07 Sep 2026', 'baru' => '14 Sep 2026', 'alasan' => 'Permintaan klien berubah', 'status' => 'Disetujui'],
    ];
@endphp

<div class="mx-auto max-w-[1500px]">

    {{-- HEADER --}}
    <div class="mb-[18px] flex flex-wrap items-center justify-between gap-4 border-b border-[#C9D6CD] pb-1">
        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>
            <h1 class="text-2xl font-medium text-[#173D30]">
                Manajemen Reservasi
            </h1>
            <p class="mt-3 text-xs text-[#74877B]">
                Reservasi tidak dapat dibatalkan; wisatawan dapat mengajukan perubahan jadwal
            </p>
        </div>

        <button
            type="button"
            id="toggleReschedule"
            class="border border-[#527A65] px-4 py-3 text-xs font-semibold text-[#174E38] transition hover:bg-[#E9F0EB]"
        >
            Permintaan Reschedule (2)
        </button>
    </div>

    {{-- PANEL PERMINTAAN RESCHEDULE --}}
    <section id="panelReschedule" class="mb-4 hidden border border-[#D5B45F] border-l-[4px] bg-white p-4">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-[#E2E9E4] pb-3">
            <h2 class="text-xs font-bold uppercase tracking-wide text-[#294B3C]">
                Permintaan Reschedule dari Wisatawan
            </h2>

            <div class="flex items-center gap-4">
                <span class="text-base text-[#405A4D]">3 permintaan</span>
                <button
                    type="button"
                    id="closeReschedule"
                    class="border border-[#527A65] px-4 py-2 text-xs font-semibold text-[#174E38] hover:bg-[#E9F0EB]"
                >
                    Tutup Permintaan
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] border-collapse text-left text-[11px]">
                <thead class="bg-[#E9F0EB] text-[#4A695A]">
                    <tr class="uppercase tracking-wide">
                        <th class="px-3 py-3">No. Invoice</th>
                        <th class="px-3 py-3">Pemesan</th>
                        <th class="px-3 py-3">Fasilitas</th>
                        <th class="px-3 py-3">Tanggal Lama</th>
                        <th class="px-3 py-3">Tanggal Baru</th>
                        <th class="px-3 py-3">Alasan</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">
                    @foreach ($permintaan as $item)
                        <tr class="hover:bg-[#F8FAF8]">
                            <td class="whitespace-nowrap px-3 py-3">{{ $item['invoice'] }}</td>
                            <td class="px-3 py-3">{{ $item['pemesan'] }}</td>
                            <td class="px-3 py-3">{{ $item['fasilitas'] }}</td>
                            <td class="whitespace-nowrap px-3 py-3">{{ $item['lama'] }}</td>
                            <td class="whitespace-nowrap px-3 py-3">{{ $item['baru'] }}</td>
                            <td class="px-3 py-3">{{ $item['alasan'] }}</td>
                            <td class="px-3 py-3">
                                @if ($item['status'] === 'Menunggu Persetujuan')
                                    <span class="inline-block whitespace-nowrap border border-[#F0D99B] bg-[#FFF4D7] px-2 py-1 text-[10px] font-semibold text-[#946B13]">
                                        {{ $item['status'] }}
                                    </span>
                                @else
                                    <span class="inline-block border border-[#CDE2D2] bg-[#E0F1E5] px-2 py-1 text-[10px] font-semibold text-[#217548]">
                                        {{ $item['status'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-3">
                                @if ($item['status'] === 'Menunggu Persetujuan')
                                    <div class="flex gap-1">
                                        <button type="button" class="border border-[#176B45] bg-[#064631] px-2 py-1 text-[10px] font-semibold text-white hover:bg-[#237052]">
                                            Setujui
                                        </button>
                                        <button type="button" class="border border-[#E7B8B2] px-2 py-1 text-[10px] font-semibold text-[#B54D43] hover:bg-red-50">
                                            Tolak
                                        </button>
                                    </div>
                                @else
                                    <span class="text-[#819187]">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr id="emptyFilter" class="hidden">
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">
                            Tidak ada reservasi yang sesuai dengan filter.
                        </td>
                    </tr>    
                </tbody>
            </table>
        </div>
    </section>

    {{-- FILTER RESERVASI --}}
    <section class="mb-3 border border-[#DCE5DE] bg-white px-4 py-4">
        <div class="mb-4 flex items-center justify-between border-b border-[#E2E9E4] pb-3">
            <h2 class="text-xs font-bold uppercase tracking-wide text-[#294B3C]">
                Filter Reservasi
            </h2>
            <button type="button" id="resetFilter" class="text-[11px] text-[#35664C] hover:underline">
                Reset Filter
            </button>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <label class="block text-[10px] font-semibold text-[#405A4D]">
                Tanggal
                <input id="filterTanggal" type="date" class="mt-2 block h-9 w-full border border-[#D5E0D8] bg-white px-3 text-xs font-normal text-[#405A4D] focus:border-[#527A65] focus:outline-none">
            </label>

            <label class="block text-[10px] font-semibold text-[#405A4D]">
                Jenis Fasilitas
                <select id="filterFasilitas" class="mt-2 block h-9 w-full border border-[#D5E0D8] bg-white px-3 text-xs font-normal text-[#405A4D] focus:border-[#527A65] focus:outline-none">
                    <option value="">Semua</option>
                    <option>Indoor / Auditorium</option>
                    <option>Area Outdoor</option>
                    <option>Area Pre-wedding</option>
                    <option>Pujasera / Food Court</option>
                </select>
            </label>

            <label class="block text-[10px] font-semibold text-[#405A4D]">
                Status Pembayaran
                <select id="filterPembayaran" class="mt-2 block h-9 w-full border border-[#D5E0D8] bg-white px-3 text-xs font-normal text-[#405A4D] focus:border-[#527A65] focus:outline-none">
                    <option value="">Semua</option>
                    <option>Lunas</option>
                    <option>Belum Lunas</option>
                </select>
            </label>

            <label class="block text-[10px] font-semibold text-[#405A4D]">
                Status Reservasi
                <select id="filterStatus" class="mt-2 block h-9 w-full border border-[#D5E0D8] bg-white px-3 text-xs font-normal text-[#405A4D] focus:border-[#527A65] focus:outline-none">
                    <option value="">Semua</option>
                    <option>Terjadwal</option>
                    <option>Selesai</option>
                    <option>Ditolak</option>
                </select>
            </label>
        </div>
    </section>

    {{-- TABEL RESERVASI --}}
    <div class="overflow-hidden border border-[#DCE5DE] bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] border-collapse text-left text-[11px]">
                <thead class="bg-[#E9F0EB] text-[#4A695A]">
                    <tr class="uppercase tracking-wide">
                        <th class="px-3 py-3">No. Invoice</th>
                        <th class="px-3 py-3">Fasilitas</th>
                        <th class="px-3 py-3">Nama Pemesan</th>
                        <th class="px-3 py-3">Tanggal Pakai</th>
                        <th class="px-3 py-3">Jam Pakai</th>
                        <th class="px-3 py-3">Total</th>
                        <th class="px-3 py-3">Status Pembayaran</th>
                        <th class="px-3 py-3">Status Reservasi</th>
                        <th class="px-3 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody id="reservasiTable" class="divide-y divide-[#E3EAE5] text-[#405A4D]">
                    @foreach ($reservasi as $item)
                        <tr class="reservation-row transition hover:bg-[#F8FAF8]"
                            data-fasilitas="{{ $item['fasilitas'] }}"
                            data-tanggal="{{ $item['tanggalFilter'] }}"
                            data-pembayaran="{{ $item['pembayaran'] }}"
                            data-status="{{ $item['status'] }}">
                            <td class="whitespace-nowrap px-3 py-3">
                                <a href="{{ route('admin.reservasi.preview.detail') }}" class="font-bold text-[#176B45] underline decoration-[#A5C4AD] underline-offset-2">
                                    {{ $item['invoice'] }}
                                </a>
                            </td>
                            <td class="px-3 py-3">{{ $item['fasilitas'] }}</td>
                            <td class="px-3 py-3">{{ $item['pemesan'] }}</td>
                            <td class="whitespace-nowrap px-3 py-3">{{ $item['tanggal'] }}</td>
                            <td class="whitespace-nowrap px-3 py-3">{{ $item['jam'] }}</td>
                            <td class="whitespace-nowrap px-3 py-3 font-bold text-[#294B3C]">
                                Rp {{ number_format($item['total'], 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-block border border-[#CDE2D2] bg-[#E0F1E5] px-2 py-1 text-[10px] font-semibold text-[#217548]">
                                    {{ $item['pembayaran'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span class="inline-block border border-[#CDE2D2] bg-[#E0F1E5] px-2 py-1 text-[10px] font-semibold text-[#217548]">
                                    {{ $item['status'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <a href="{{ route('admin.reservasi.preview.detail') }}" class="inline-flex border border-[#CBD9CF] px-3 py-1.5 text-[10px] text-[#355D47] transition hover:bg-[#EAF2EC]">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
        <p class="text-[10px] text-[#819187]">
            Menampilkan 1–5 dari 47 reservasi
        </p>
        <div class="flex items-center border border-[#DCE5DE] bg-white text-xs">
            <button type="button" class="px-3 py-2 text-[#819187]">?</button>
            <button type="button" class="bg-[#064631] px-3 py-2 font-semibold text-white">1</button>
            <button type="button" class="border-l border-[#DCE5DE] px-3 py-2 text-[#587363]">2</button>
            <button type="button" class="border-l border-[#DCE5DE] px-3 py-2 text-[#587363]">3</button>
            <button type="button" class="border-l border-[#DCE5DE] px-3 py-2 text-[#587363]">?</button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>

document.addEventListener('DOMContentLoaded', function () {
    // PANEL RESCHEDULE
    const panel = document.getElementById('panelReschedule');
    const toggle = document.getElementById('toggleReschedule');
    const close = document.getElementById('closeReschedule');

    if (panel && toggle && close) {
        toggle.addEventListener('click', function () {
            panel.classList.toggle('hidden');
        });

        close.addEventListener('click', function () {
            panel.classList.add('hidden');
        });
    }

    // FILTER RESERVASI
    const filterTanggal = document.getElementById('filterTanggal');
    const filterFasilitas = document.getElementById('filterFasilitas');
    const filterPembayaran = document.getElementById('filterPembayaran');
    const filterStatus = document.getElementById('filterStatus');
    const resetFilter = document.getElementById('resetFilter');

    const rows = document.querySelectorAll('.reservation-row');

    function applyFilters() {
        let visibleCount = 0;

        rows.forEach(function (row) {
            const matchTanggal =
                !filterTanggal.value ||
                row.dataset.tanggal === filterTanggal.value;

            const matchFasilitas =
                !filterFasilitas.value ||
                row.dataset.fasilitas === filterFasilitas.value;

            const matchPembayaran =
                !filterPembayaran.value ||
                row.dataset.pembayaran === filterPembayaran.value;

            const matchStatus =
                !filterStatus.value ||
                row.dataset.status === filterStatus.value;

            const isMatch =
                matchTanggal &&
                matchFasilitas &&
                matchPembayaran &&
                matchStatus;

            row.classList.toggle('hidden', !isMatch);

            if (isMatch) {
                visibleCount++;
            }
        });

        const emptyMessage = document.getElementById('emptyFilter');

        if (emptyMessage) {
            emptyMessage.classList.toggle('hidden', visibleCount > 0);
        }
    }

    filterTanggal.addEventListener('change', applyFilters);
    filterFasilitas.addEventListener('change', applyFilters);
    filterPembayaran.addEventListener('change', applyFilters);
    filterStatus.addEventListener('change', applyFilters);

    // RESET FILTER
    resetFilter.addEventListener('click', function () {
        filterTanggal.value = '';
        filterFasilitas.value = '';
        filterPembayaran.value = '';
        filterStatus.value = '';

        applyFilters();
    });
});
</script>
@endpush



