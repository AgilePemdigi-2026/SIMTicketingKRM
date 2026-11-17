@extends('layouts.admin')

@section('title', 'Detail Reservasi - Kebun Raya Mangrove')

@section('content')
<div class="mx-auto max-w-[1200px]">

    {{-- HEADER --}}
    <div class="mb-6 border-b border-[#C9D6CD] pb-4">
        <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
            Administrasi Kebun Raya Mangrove
        </p>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-[#173D30]">
                    Detail Reservasi
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Invoice: INV-2026-0047
                </p>
            </div>

            <span class="rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-800">
                Terjadwal
            </span>
        </div>
    </div>

    {{-- INFORMASI PEMESAN --}}
    <div class="mb-6 rounded-xl border border-[#DCE5DE] bg-white p-6 shadow-sm">
        <h2 class="mb-5 border-b border-[#E5EBE6] pb-3 text-lg font-semibold text-[#173D30]">
            Informasi Pemesan
        </h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <p class="text-xs text-gray-500">Nama Pemesan</p>
                <p class="mt-1 font-semibold text-gray-800">PT. Cahaya Nusantara</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Nomor Identitas</p>
                <p class="mt-1 font-semibold text-gray-800">3578XXXXXXXXXXXX</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Nomor Telepon</p>
                <p class="mt-1 font-semibold text-gray-800">0812-3456-7890</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Email</p>
                <p class="mt-1 font-semibold text-gray-800">cahaya.nusantara@email.com</p>
            </div>
        </div>
    </div>

    {{-- DETAIL RESERVASI --}}
    <div class="mb-6 rounded-xl border border-[#DCE5DE] bg-white p-6 shadow-sm">
        <h2 class="mb-5 border-b border-[#E5EBE6] pb-3 text-lg font-semibold text-[#173D30]">
            Detail Reservasi
        </h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <p class="text-xs text-gray-500">Fasilitas</p>
                <p class="mt-1 font-semibold text-gray-800">Indoor / Auditorium</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Tanggal Penggunaan</p>
                <p class="mt-1 font-semibold text-gray-800">05 Oktober 2026</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Jam Penggunaan</p>
                <p class="mt-1 font-semibold text-gray-800">10.00 - 13.00</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Jumlah Peserta</p>
                <p class="mt-1 font-semibold text-gray-800">100 Orang</p>
            </div>

            <div class="sm:col-span-2">
                <p class="text-xs text-gray-500">Keperluan</p>
                <p class="mt-1 font-semibold text-gray-800">
                    Kegiatan seminar dan pertemuan perusahaan
                </p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 border-t border-[#E5EBE6] pt-5 sm:grid-cols-3">
            <div>
                <p class="text-xs text-gray-500">Total Pembayaran</p>
                <p class="mt-1 text-xl font-bold text-[#173D30]">Rp 1.500.000</p>
            </div>

            <div>
                <p class="text-xs text-gray-500">Status Pembayaran</p>
                <span class="mt-2 inline-block rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                    Lunas
                </span>
            </div>

            <div>
                <p class="text-xs text-gray-500">Status Reservasi</p>
                <span class="mt-2 inline-block rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                    Terjadwal
                </span>
            </div>
        </div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ url('/admin/reservasi-preview') }}"
           class="rounded-lg border border-[#C9D6CD] px-5 py-2.5 text-sm font-semibold text-[#315B46] transition hover:bg-[#F0F5F1]">
            Kembali
        </a>

        <div class="flex flex-wrap gap-3">
            <button type="button"
                class="rounded-lg bg-[#315B46] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#244735]">
                Update Status
            </button>

            <button type="button" onclick="window.print()"
                class="rounded-lg border border-[#315B46] px-5 py-2.5 text-sm font-semibold text-[#315B46] transition hover:bg-[#F0F5F1]">
                Cetak Invoice
            </button>
        </div>
    </div>

</div>
@endsection
