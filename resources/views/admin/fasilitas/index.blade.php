@extends('layouts.admin')

@section('title', 'Manajemen Fasilitas - Kebun Raya Mangrove')

@section('content')
<div class="mx-auto max-w-[1500px]">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-[#D4DED7] pb-6">
        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>

            <h1 class="text-2xl font-semibold text-[#173D30]">
                Manajemen Fasilitas
            </h1>

            <p class="mt-2 text-sm text-[#74877B]">
                Kelola fasilitas, jadwal sesi, dan harga sewa.
            </p>
        </div>

        <a
            href="{{ route('admin.fasilitas.create') }}"
            class="inline-flex items-center justify-center bg-[#064631] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#237052]"
        >
            + Tambah Fasilitas
        </a>
    </div>

    {{-- PESAN SUKSES --}}
    @if (session('success'))
        <div class="mb-5 border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- PESAN ERROR --}}
    @if (session('error'))
        <div class="mb-5 border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- PESAN VALIDASI --}}
    @if ($errors->any())
        <div class="mb-5 border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <p class="mb-2 font-bold">
                Data belum dapat diproses:
            </p>

            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- STATISTIK FASILITAS --}}
    <div class="mb-6 grid gap-4 md:grid-cols-3">

        {{-- TOTAL FASILITAS --}}
        <div class="flex items-center gap-4 rounded-lg border border-[#DCE5DE] bg-white p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#E5F4EA] text-2xl text-[#176B45]">
                ▦
            </div>

            <div>
                <p class="text-xs text-[#74877B]">
                    Total Fasilitas
                </p>

                <p class="mt-1 text-2xl font-bold text-[#173D30]">
                    {{ $fasilitas->count() }}
                </p>
            </div>
        </div>

        {{-- FASILITAS AKTIF --}}
        <div class="flex items-center gap-4 rounded-lg border border-[#DCE5DE] bg-white p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#E5F4EA] text-2xl text-[#176B45]">
                ✓
            </div>

            <div>
                <p class="text-xs text-[#74877B]">
                    Fasilitas Aktif
                </p>

                <p class="mt-1 text-2xl font-bold text-[#173D30]">
                    {{ $fasilitas->where('status_fasilitas', 1)->count() }}
                </p>
            </div>
        </div>

        {{-- TOTAL SESI --}}
        <div class="flex items-center gap-4 rounded-lg border border-[#DCE5DE] bg-white p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#E5F4EA] text-2xl text-[#176B45]">
                ◷
            </div>

            <div>
                <p class="text-xs text-[#74877B]">
                    Total Sesi
                </p>

                <p class="mt-1 text-2xl font-bold text-[#173D30]">
                    {{ $fasilitas->sum(fn ($item) => $item->sesiFasilitas->count()) }}
                </p>
            </div>
        </div>

    </div>

    {{-- PANEL DAFTAR FASILITAS --}}
    <div class="overflow-hidden border border-[#DCE5DE] bg-white">

        <div class="border-b border-[#E2E9E4] px-5 py-5">
            <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                Daftar Fasilitas
            </h2>

            <p class="mt-1 text-xs text-[#819187]">
                Informasi fasilitas beserta rincian sesi dan harga sewa.
            </p>
        </div>

        {{-- TABEL RESPONSIF --}}
        <div class="overflow-x-auto">

            <table class="w-full min-w-[1150px] border-collapse text-left text-xs">

                {{-- HEADER TABEL --}}
                <thead class="bg-[#E9F0EB] text-[#4A695A]">
                    <tr class="uppercase tracking-[0.05em]">
                        <th class="px-4 py-4 font-bold">No.</th>
                        <th class="px-4 py-4 font-bold">Fasilitas</th>
                        <th class="px-4 py-4 font-bold">Kapasitas</th>
                        <th class="px-4 py-4 font-bold">Sesi & Harga</th>
                        <th class="px-4 py-4 font-bold">Status</th>
                        <th class="px-4 py-4 font-bold">Deskripsi</th>
                        <th class="px-4 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>

                {{-- ISI TABEL --}}
                <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">

                    @forelse ($fasilitas as $item)
                        <tr class="transition hover:bg-[#F8FAF8]">

                            {{-- NOMOR --}}
                            <td class="px-4 py-5 align-top font-semibold text-[#819187]">
                                {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </td>

                            {{-- FOTO DAN NAMA FASILITAS --}}
                            <td class="px-4 py-5 align-top">
                                <div class="flex min-w-[190px] items-start gap-3">

                                    @if ($item->foto_fasilitas)
                                        <div class="shrink-0">
                                            <img
                                                src="{{ str_starts_with($item->foto_fasilitas, 'http') ? $item->foto_fasilitas : asset('storage/' . $item->foto_fasilitas) }}"
                                                alt="Foto {{ $item->nama_fasilitas }}"
                                                class="h-16 w-16 rounded-md object-cover"
                                                loading="lazy"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                            >

                                            <div class="hidden h-16 w-16 items-center justify-center rounded-md bg-[#E9F0EB] px-1 text-center text-[10px] text-[#829287]">
                                                Foto tidak tersedia
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-md bg-[#E9F0EB] text-center text-[10px] text-[#829287]">
                                            Belum ada foto
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-[#173D30]">
                                            {{ $item->nama_fasilitas }}
                                        </p>

                                        <p class="mt-2 text-xs leading-5 text-[#819187]">
                                            {{ $item->ketentuan_fasilitas ?: 'Fasilitas wisata' }}
                                        </p>
                                    </div>

                                </div>
                            </td>

                            {{-- KAPASITAS --}}
                            <td class="px-4 py-5 align-top">
                                <span class="inline-block whitespace-nowrap bg-[#EFF3EF] px-3 py-2 font-semibold text-[#355D47]">
                                    {{ $item->kapasitas_fasilitas }} orang
                                </span>
                            </td>

                            {{-- SESI DAN HARGA --}}
                            <td class="px-4 py-5 align-top">
                                <div class="flex min-w-[200px] flex-col gap-2">

                                    @forelse ($item->sesiFasilitas as $sesi)
                                        <div class="border border-[#DCE5DE] bg-[#FAFCFA] p-3">

                                            <div class="mb-2 flex items-center justify-between gap-3">
                                                <span class="font-bold text-[#173D30]">
                                                    {{ $sesi->nama_sesi }}
                                                </span>

                                                <span class="whitespace-nowrap bg-[#E9F0EB] px-2 py-1 text-[10px] text-[#718779]">
                                                    {{ $sesi->durasi_menit }} mnt
                                                </span>
                                            </div>

                                            <p class="mb-2 text-[#819187]">
                                                {{ substr((string) $sesi->jam_mulai, 0, 5) }}
                                                –
                                                {{ substr((string) $sesi->jam_selesai, 0, 5) }}
                                            </p>

                                            <p class="text-sm font-bold text-[#176B45]">
                                                Rp {{ number_format($sesi->harga_sesi, 0, ',', '.') }}
                                            </p>

                                        </div>
                                    @empty
                                        <span class="text-xs text-[#819187]">
                                            Belum ada sesi.
                                        </span>
                                    @endforelse

                                </div>
                            </td>

                            {{-- STATUS --}}
                            <td class="px-4 py-5 align-top">
                                @if ((int) $item->status_fasilitas === 1)
                                    <span class="inline-flex items-center gap-2 whitespace-nowrap bg-[#E0F1E5] px-3 py-2 font-semibold text-[#217548]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-2 whitespace-nowrap bg-[#F0F2F0] px-3 py-2 font-semibold text-[#718076]">
                                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- DESKRIPSI --}}
                            <td class="max-w-[200px] px-4 py-5 align-top leading-5 text-[#64786D]">
                                {{ $item->deskripsi_fasilitas ?: 'Belum ada deskripsi.' }}
                            </td>

                            {{-- AKSI --}}
                            <td class="px-4 py-5 align-top">
                                <div class="flex min-w-[110px] flex-col gap-2">

                                    {{-- TOMBOL EDIT --}}
                                    <a
                                        href="{{ route('admin.fasilitas.edit', $item->id_fasilitas) }}"
                                        class="inline-flex min-h-9 items-center justify-center border border-[#CBD9CF] px-3 py-2 font-semibold text-[#355D47] transition hover:bg-[#EAF2EC]"
                                    >
                                        Edit
                                    </a>

                                    {{-- TOMBOL AKTIFKAN / NONAKTIFKAN --}}
                                    <form
                                        action="{{ route('admin.fasilitas.toggle-status', $item->id_fasilitas) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin mengubah status fasilitas ini?')"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        @if ((int) $item->status_fasilitas === 1)
                                            <button
                                                type="submit"
                                                class="inline-flex min-h-9 w-full items-center justify-center border border-[#E9CCBC] px-3 py-2 font-semibold text-[#B65B36] transition hover:bg-[#FFF2ED]"
                                            >
                                                Nonaktifkan
                                            </button>
                                        @else
                                            <button
                                                type="submit"
                                                class="inline-flex min-h-9 w-full items-center justify-center border border-[#B7D7C0] px-3 py-2 font-semibold text-[#176B45] transition hover:bg-[#EFF6F0]"
                                            >
                                                Aktifkan
                                            </button>
                                        @endif
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">

                                <p class="text-sm font-bold text-[#173D30]">
                                    Belum ada fasilitas.
                                </p>

                                <p class="mt-2 text-xs text-[#819187]">
                                    Tambahkan fasilitas untuk mulai mengelola sesi dan harga.
                                </p>

                                <a
                                    href="{{ route('admin.fasilitas.create') }}"
                                    class="mt-5 inline-flex bg-[#064631] px-5 py-3 text-xs font-semibold text-white transition hover:bg-[#237052]"
                                >
                                    + Tambah Fasilitas
                                </a>

                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection