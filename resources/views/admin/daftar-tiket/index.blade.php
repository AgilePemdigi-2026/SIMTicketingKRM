@extends('layouts.admin')

@section('title', 'Manajemen Tiket - Kebun Raya Mangrove')

@section('content')
<div class="mx-auto max-w-[1500px]">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-[#D4DED7] pb-6">
        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>

            <h1 class="text-2xl font-semibold text-[#173D30]">
                Manajemen Tiket
            </h1>

            <p class="mt-2 text-sm text-[#74877B]">
                Kelola produk dan harga tiket wisata.
            </p>
        </div>

        <a
            href="{{ route('admin.daftar-tiket.create') }}"
            class="inline-flex items-center justify-center bg-[#064631] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#237052]"
        >
            + Tambah Tiket
        </a>
    </div>

    {{-- PESAN SUKSES --}}
    @if (session('success'))
        <div class="mb-5 border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- PESAN VALIDASI --}}
    @if ($errors->any())
        <div class="mb-5 border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <p class="mb-2 font-bold">
                Terjadi kesalahan:
            </p>

            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- DAFTAR TIKET --}}
    <div class="overflow-hidden border border-[#DCE5DE] bg-white">

        <div class="border-b border-[#E2E9E4] px-5 py-5">
            <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                Daftar Tiket
            </h2>

            <p class="mt-1 text-xs text-[#819187]">
                Informasi tiket, kategori, harga, dan status yang terdaftar dalam sistem.
            </p>
        </div>

        {{-- SCROLL HORIZONTAL UNTUK TABEL LEBAR --}}
        <div class="overflow-x-auto">

            <table class="w-full min-w-[1250px] border-collapse text-left text-xs">

                {{-- HEADER TABEL --}}
                <thead class="bg-[#E9F0EB] text-[#4A695A]">
                    <tr class="uppercase tracking-[0.05em]">
                        <th class="px-4 py-4 font-bold">No.</th>
                        <th class="px-4 py-4 font-bold">Nama Tiket</th>
                        <th class="px-4 py-4 font-bold">Wahana</th>
                        <th class="px-4 py-4 font-bold">Kategori Tiket</th>
                        <th class="px-4 py-4 font-bold">Jenis Wisatawan</th>
                        <th class="px-4 py-4 font-bold">Kuota</th>
                        <th class="px-4 py-4 font-bold">Harga Dewasa</th>
                        <th class="px-4 py-4 font-bold">Harga Anak</th>
                        <th class="px-4 py-4 font-bold">Status</th>
                        <th class="px-4 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>

                {{-- ISI TABEL --}}
                <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">

                    @forelse ($daftarTiket as $item)
                        <tr class="transition hover:bg-[#F8FAF8]">

                            {{-- NOMOR --}}
                            <td class="px-4 py-4 text-[#819187]">
                                {{ $loop->iteration }}
                            </td>

                            {{-- NAMA TIKET --}}
                            <td class="px-4 py-4 font-semibold text-[#173D30]">
                                {{ $item->nama_tiket }}
                            </td>

                            {{-- WAHANA TERKAIT --}}
                            <td class="max-w-[170px] px-4 py-4 leading-5">
                                @forelse ($item->daftarWahana as $wahanaItem)
                                    {{ $wahanaItem->nama_wahana }}@if (!$loop->last), @endif
                                @empty
                                    <span class="text-[#96A59A]">-</span>
                                @endforelse
                            </td>

                            {{-- KATEGORI TIKET --}}
                            <td class="px-4 py-4">
                                {{ $item->kategori_tiket }}
                            </td>

                            {{-- JENIS WISATAWAN --}}
                            <td class="px-4 py-4">
                                {{ $item->kategori_wisatawan }}
                            </td>

                            {{-- KUOTA --}}
                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $item->kuota }}
                            </td>

                            {{-- HARGA DEWASA --}}
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-[#174E38]">
                                Rp {{ number_format($item->harga_dewasa, 0, ',', '.') }}
                            </td>

                            {{-- HARGA ANAK --}}
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-[#174E38]">
                                Rp {{ number_format($item->harga_anak, 0, ',', '.') }}
                            </td>

                            {{-- STATUS TIKET --}}
                            <td class="px-4 py-4">
                                @if ($item->status_aktif == 1)
                                    <span class="inline-block whitespace-nowrap bg-[#E0F1E5] px-3 py-1.5 font-semibold text-[#217548]">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block whitespace-nowrap bg-[#F0F2F0] px-3 py-1.5 font-semibold text-[#718076]">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">

                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('admin.daftar-tiket.edit', $item->id_tiket) }}"
                                        class="inline-flex min-h-9 items-center justify-center border border-[#CBD9CF] px-3 py-2 font-medium text-[#355D47] transition hover:bg-[#EAF2EC]"
                                    >
                                        Edit
                                    </a>

                                    {{-- HAPUS --}}
                                    <form
                                        action="{{ route('admin.daftar-tiket.destroy', $item->id_tiket) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus tiket ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="inline-flex min-h-9 items-center justify-center border border-[#E9CCBC] px-3 py-2 font-medium text-[#B65B36] transition hover:bg-[#FFF2ED]"
                                        >
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-sm text-[#84958A]">
                                Belum ada data tiket.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection