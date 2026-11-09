@extends('layouts.admin')

@section('title', 'Manajemen Konten - Galeri')

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

        {{-- MENU KATEGORI --}}
        @include('admin.konten.partials.kategori', [
            'kategoriAktif' => 'galeri'
        ])

        {{-- KONTEN GALERI --}}
        <div class="min-w-0 flex-1 border border-[#DCE5DE] bg-white p-5">

            {{-- HEADER GALERI --}}
            <div class="mb-5 flex flex-wrap items-center justify-between gap-4 border-b border-[#E2E9E4] pb-5">

                <div>
                    <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                        Galeri Foto
                    </h2>

                    <p class="mt-2 text-xs text-[#819187]">
                        Total {{ $galeri->total() }} foto tersimpan.
                    </p>
                </div>

                @if (\Illuminate\Support\Facades\Route::has('admin.konten-galeri.create'))
                    <a
                        href="{{ route('admin.konten-galeri.create') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-[#064631] transition hover:bg-[#E9F0EB]"
                    >
                        <span class="text-lg">+</span>
                        Upload Foto
                    </a>
                @else
                    <span class="text-xs text-[#A0ACA4]">
                        Upload Foto (Belum Aktif)
                    </span>
                @endif

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

            {{-- GRID FOTO GALERI --}}
            @if ($galeri->count() > 0)

                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">

                    @foreach ($galeri as $foto)

                        <article class="min-w-0">

                            {{-- FOTO DAN TOMBOL HAPUS --}}
                            <div class="group relative overflow-hidden bg-[#E9F0EB]">

                                @if ($foto->gambar && \Illuminate\Support\Facades\Storage::disk('public')->exists($foto->gambar))

                                    <img
                                        src="{{ asset('storage/' . $foto->gambar) }}"
                                        alt="{{ $foto->judul }}"
                                        class="h-32 w-full object-cover transition duration-200 group-hover:scale-105 sm:h-36"
                                        loading="lazy"
                                    >

                                @else

                                    <div class="flex h-32 w-full items-center justify-center text-center text-xs text-[#819187] sm:h-36">
                                        Foto tidak tersedia
                                    </div>

                                @endif

                                {{-- HAPUS FOTO --}}
                                @if (\Illuminate\Support\Facades\Route::has('admin.konten-galeri.destroy'))

                                    <form
                                        action="{{ route('admin.konten-galeri.destroy', $foto->id_konten) }}"
                                        method="POST"
                                        class="absolute right-2 top-2"
                                        onsubmit="return confirm('Yakin ingin menghapus foto galeri ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            aria-label="Hapus foto {{ $foto->judul }}"
                                            title="Hapus Foto"
                                            class="flex h-7 w-7 items-center justify-center bg-white font-bold text-[#B65B36] shadow-sm transition hover:bg-red-50"
                                        >
                                            ×
                                        </button>
                                    </form>

                                @endif

                            </div>

                            {{-- JUDUL FOTO --}}
                            <div class="mt-2">

                                <p class="truncate text-xs font-medium text-[#405A4D]"
                                   title="{{ $foto->judul }}">
                                    {{ $foto->judul }}
                                </p>

                                {{-- STATUS PUBLIKASI --}}
                                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">

                                    @if ($foto->status_publikasi === 'published')
                                        <span class="bg-[#E0F1E5] px-2 py-1 text-[10px] font-semibold text-[#217548]">
                                            Published
                                        </span>
                                    @else
                                        <span class="bg-[#F0F2F0] px-2 py-1 text-[10px] font-semibold text-[#718076]">
                                            Draft
                                        </span>
                                    @endif

                                    {{-- EDIT FOTO --}}
                                    @if (\Illuminate\Support\Facades\Route::has('admin.konten-galeri.edit'))

                                        <a
                                            href="{{ route('admin.konten-galeri.edit', $foto->id_konten) }}"
                                            class="text-[11px] font-semibold text-[#064631] hover:underline"
                                        >
                                            Edit Foto
                                        </a>

                                    @endif

                                </div>

                            </div>

                        </article>

                    @endforeach

                </div>

                {{-- PAGINATION --}}
                @if ($galeri->hasPages())
                    <div class="mt-6 border-t border-[#E2E9E4] pt-5">
                        {{ $galeri->links() }}
                    </div>
                @endif

            @else

                {{-- KONDISI GALERI KOSONG --}}
                <div class="flex min-h-[220px] flex-col items-center justify-center px-4 py-10 text-center">

                    <div class="mb-4 text-4xl">
                        🖼️
                    </div>

                    <h3 class="text-sm font-semibold text-[#173D30]">
                        Belum Ada Foto Galeri
                    </h3>

                    <p class="mt-2 max-w-sm text-xs leading-6 text-[#819187]">
                        Belum ada foto yang tersimpan.
                        Gunakan tombol Upload Foto untuk menambahkan dokumentasi galeri.
                    </p>

                </div>

            @endif

        </div>

    </div>

</div>
@endsection