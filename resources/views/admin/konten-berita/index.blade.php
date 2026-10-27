@extends('layouts.admin')

@section('title', 'Manajemen Konten - Berita & Artikel')

@section('content')
<div class="mx-auto max-w-[1500px]">

    {{-- HEADER UTAMA --}}
    <div class="mb-6 border-b border-[#D4DED7] pb-6">

        <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
            Administrasi Kebun Raya Mangrove
        </p>

        <h1 class="text-2xl font-semibold text-[#173D30]">
            Manajemen Konten
        </h1>

        <p class="mt-2 text-sm text-[#74877B]">
            Kelola informasi pada portal wisatawan.
        </p>

    </div>

    {{-- LAYOUT KATEGORI DAN KONTEN --}}
    <div class="flex flex-col items-start gap-4 lg:flex-row">

        {{-- MENU KATEGORI KIRI --}}
        @include('admin.konten.partials.kategori', [
            'kategoriAktif' => 'berita'
        ])

        {{-- PANEL BERITA & ARTIKEL --}}
        <div class="min-w-0 flex-1 border border-[#DCE5DE] bg-white p-5">

            {{-- HEADER PANEL --}}
            <div class="mb-5 flex flex-wrap items-center justify-between gap-4 border-b border-[#E2E9E4] pb-5">

                <div>
                    <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                        Berita & Artikel
                    </h2>

                    <p class="mt-2 text-xs text-[#819187]">
                        Kelola berita, kegiatan, dan artikel edukasi.
                    </p>
                </div>

                <a
                    href="{{ route('admin.konten-berita.create') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-[#064631] transition hover:bg-[#E9F0EB]"
                >
                    <span class="text-lg">+</span>
                    Tambah Artikel
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

            {{-- INFORMASI TOTAL --}}
            <div class="mb-4 flex items-center justify-between gap-3">

                <h3 class="text-xs font-bold uppercase tracking-[0.06em] text-[#4A695A]">
                    Daftar Berita
                </h3>

                <span class="text-xs text-[#668977]">
                    Total: {{ $berita->total() }} artikel
                </span>

            </div>

            {{-- TABEL BERITA --}}
            <div class="overflow-x-auto border border-[#DCE5DE]">

                <table class="w-full min-w-[920px] border-collapse text-left text-xs">

                    <thead class="bg-[#E9F0EB] text-[#4A695A]">
                        <tr class="uppercase tracking-[0.05em]">

                            <th class="px-3 py-4 font-bold">
                                No.
                            </th>

                            <th class="px-3 py-4 font-bold">
                                Judul Artikel
                            </th>

                            <th class="px-3 py-4 font-bold">
                                Kategori
                            </th>

                            <th class="px-3 py-4 font-bold">
                                Penulis
                            </th>

                            <th class="px-3 py-4 font-bold">
                                Tanggal Publikasi
                            </th>

                            <th class="px-3 py-4 font-bold">
                                Status
                            </th>

                            <th class="px-3 py-4 font-bold">
                                Aksi
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[#E3EAE5] text-[#405A4D]">

                        @forelse ($berita as $artikel)

                            <tr class="transition hover:bg-[#F8FAF8]">

                                {{-- NOMOR --}}
                                <td class="px-3 py-4">
                                    {{ $berita->firstItem() + $loop->index }}
                                </td>

                                {{-- JUDUL DAN RINGKASAN --}}
                                <td class="max-w-[250px] px-3 py-4">

                                    <p class="font-semibold leading-5 text-[#173D30]">
                                        {{ $artikel->judul }}
                                    </p>

                                    <p class="mt-2 leading-5 text-[#819187]">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($artikel->isi ?? ''), 75) }}
                                    </p>

                                </td>

                                {{-- KATEGORI --}}
                                <td class="px-3 py-4">
                                    {{ $artikel->kategori ?? '-' }}
                                </td>

                                {{-- PENULIS --}}
                                <td class="px-3 py-4">
                                    {{ $artikel->penulis ?? '-' }}
                                </td>

                                {{-- TANGGAL PUBLIKASI --}}
                                <td class="whitespace-nowrap px-3 py-4">

                                    @if ($artikel->waktu_publikasi)
                                        {{ $artikel->waktu_publikasi->format('d/m/Y H:i') }}
                                    @else
                                        Belum dipublikasikan
                                    @endif

                                </td>

                                {{-- STATUS --}}
                                <td class="px-3 py-4">

                                    @if ($artikel->status_publikasi === 'published')

                                        <span class="inline-block bg-[#E0F1E5] px-2 py-1.5 font-semibold text-[#217548]">
                                            Published
                                        </span>

                                    @else

                                        <span class="inline-block bg-[#F0F2F0] px-2 py-1.5 font-semibold text-[#718076]">
                                            Draft
                                        </span>

                                    @endif

                                </td>

                                {{-- AKSI --}}
                                <td class="px-3 py-4">

                                    <div class="flex items-center gap-2">

                                        {{-- EDIT --}}
                                        <a
                                            href="{{ route('admin.konten-berita.edit', $artikel->id_konten) }}"
                                            class="border border-[#CBD9CF] px-3 py-2 font-medium text-[#355D47] transition hover:bg-[#EAF2EC]"
                                        >
                                            Edit
                                        </a>

                                        {{-- HAPUS --}}
                                        <form
                                            action="{{ route('admin.konten-berita.destroy', $artikel->id_konten) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus berita ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="border border-[#E9CCBC] px-3 py-2 font-medium text-[#B65B36] transition hover:bg-[#FFF2ED]"
                                            >
                                                Hapus
                                            </button>
                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="px-4 py-14 text-center text-sm text-[#84958A]">
                                    Belum ada data berita atau artikel.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- PAGINATION --}}
            @if ($berita->hasPages())
                <div class="mt-5 border-t border-[#E2E9E4] pt-5">
                    {{ $berita->links() }}
                </div>
            @endif

        </div>

    </div>

</div>
@endsection