@extends('layouts.admin')

@section('title', 'Manajemen Wahana - Kebun Raya Mangrove')

@section('content')
<div class="mx-auto max-w-[1500px]">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-[#D4DED7] pb-6">
        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>

            <h1 class="text-2xl font-semibold text-[#173D30]">
                Manajemen Wahana
            </h1>

            <p class="mt-2 text-sm text-[#74877B]">
                Kelola wahana dan informasi operasional.
            </p>
        </div>

        <a href="{{ route('admin.wahana.create') }}"
           class="inline-flex items-center justify-center bg-[#064631] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#237052]">
            + Tambah Wahana
        </a>
    </div>

    {{-- PESAN SUKSES --}}
    @if (session('success'))
        <div class="mb-5 border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- PESAN ERROR --}}
    @if ($errors->any())
        <div class="mb-5 border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- DAFTAR WAHANA --}}
    <div class="overflow-hidden border border-[#DCE5DE] bg-white">

        <div class="border-b border-[#E2E9E4] px-5 py-5">
            <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                Daftar Wahana
            </h2>
            <p class="mt-1 text-xs text-[#819187]">
                Informasi wahana yang terdaftar dalam sistem.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] border-collapse text-left text-xs">

                <thead class="bg-[#E9F0EB] text-[#4A695A]">
                    <tr class="uppercase tracking-[0.05em]">
                        <th class="px-4 py-4 font-bold">Foto</th>
                        <th class="px-4 py-4 font-bold">Nama Wahana</th>
                        <th class="px-4 py-4 font-bold">Deskripsi</th>
                        <th class="px-4 py-4 font-bold">Durasi</th>
                        <th class="px-4 py-4 font-bold">Jam Operasional</th>
                        <th class="px-4 py-4 font-bold">Kapasitas</th>
                        <th class="px-4 py-4 font-bold">Status</th>
                        <th class="px-4 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">

                    @forelse ($wahana as $item)
                        <tr class="transition hover:bg-[#F8FAF8]">

                            {{-- FOTO --}}
                            <td class="px-4 py-4">
                                @if ($item->foto)
                                    <img
                                        src="{{ str_starts_with($item->foto, 'http') ? $item->foto : asset('storage/' . $item->foto) }}"
                                        alt="Foto {{ $item->nama_wahana }}"
                                        class="h-14 w-20 rounded-sm object-cover"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >

                                    <div class="hidden h-14 w-20 items-center justify-center bg-[#E9F0EB] text-center text-[10px] text-[#829287]">
                                        Foto tidak tersedia
                                    </div>
                                @else
                                    <div class="flex h-14 w-20 items-center justify-center bg-[#E9F0EB] text-center text-[10px] text-[#829287]">
                                        Belum ada foto
                                    </div>
                                @endif
                            </td>

                            {{-- NAMA --}}
                            <td class="px-4 py-4 font-semibold text-[#173D30]">
                                {{ $item->nama_wahana }}
                            </td>

                            {{-- DESKRIPSI --}}
                            <td class="max-w-[250px] px-4 py-4 leading-5">
                                {{ $item->deskripsi ?: 'Belum ada deskripsi.' }}
                            </td>

                            {{-- DURASI --}}
                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $item->durasi }} detik
                            </td>

                            {{-- JAM --}}
                            <td class="whitespace-nowrap px-4 py-4">
                                @if ($item->jam_buka && $item->jam_tutup)
                                    {{ \Carbon\Carbon::parse($item->jam_buka)->format('H:i') }}
                                    –
                                    {{ \Carbon\Carbon::parse($item->jam_tutup)->format('H:i') }}
                                @else
                                    Belum diatur
                                @endif
                            </td>

                            {{-- KAPASITAS --}}
                            <td class="whitespace-nowrap px-4 py-4">
                                {{ $item->kapasitas }}
                            </td>

                            {{-- STATUS --}}
                            <td class="px-4 py-4">
                                @if ($item->status_aktif == 1)
                                    <span class="inline-block bg-[#E0F1E5] px-3 py-1.5 font-semibold text-[#217548]">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-block bg-[#F0F2F0] px-3 py-1.5 font-semibold text-[#718076]">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-2">

                                    <a href="{{ route('admin.wahana.edit', $item->id_wahana) }}"
                                       class="border border-[#CBD9CF] px-3 py-2 font-medium text-[#355D47] transition hover:bg-[#EAF2EC]">
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.wahana.destroy', $item->id_wahana) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus wahana ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="border border-[#E9CCBC] px-3 py-2 font-medium text-[#B65B36] transition hover:bg-[#FFF2ED]">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-[#84958A]">
                                Belum ada data wahana.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection