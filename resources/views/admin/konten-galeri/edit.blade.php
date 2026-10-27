@extends('layouts.admin')

@section('title', 'Edit Foto Galeri - Kebun Raya Mangrove')

@section('content')
<div class="mx-auto max-w-[1500px]">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-[#D4DED7] pb-6">

        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>

            <h1 class="text-2xl font-semibold text-[#173D30]">
                Edit Foto Galeri
            </h1>

            <p class="mt-2 text-sm text-[#74877B]">
                Perbarui foto dan informasi dokumentasi Kebun Raya Mangrove Surabaya.
            </p>
        </div>

        <a href="{{ route('admin.konten-galeri.index') }}"
           class="inline-flex items-center justify-center border border-[#CBD9CF] bg-white px-5 py-3 text-sm font-semibold text-[#355D47] transition hover:bg-[#EAF2EC]">
            ← Kembali
        </a>

    </div>

    {{-- PESAN ERROR --}}
    @if ($errors->any())
        <div class="mb-6 border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">

            <p class="mb-2 font-semibold">
                Periksa kembali data yang dimasukkan:
            </p>

            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif

    {{-- FORM EDIT GALERI --}}
    <form
        action="{{ route('admin.konten-galeri.update', $foto->id_konten) }}"
        method="POST"
        enctype="multipart/form-data"
        class="max-w-[1050px] space-y-6"
    >
        @csrf
        @method('PUT')

        {{-- INFORMASI FOTO --}}
        <div class="border border-[#DCE5DE] bg-white">

            <div class="border-b border-[#E2E9E4] px-6 py-5">
                <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                    Informasi Foto
                </h2>

                <p class="mt-1 text-xs text-[#819187]">
                    Perbarui judul, keterangan, atau foto galeri.
                </p>
            </div>

            <div class="space-y-6 p-6">

                {{-- JUDUL FOTO --}}
                <div>
                    <label
                        for="judul"
                        class="mb-2 block text-sm font-semibold text-[#355D47]"
                    >
                        Judul Foto <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="judul"
                        name="judul"
                        value="{{ old('judul', $foto->judul) }}"
                        maxlength="150"
                        required
                        class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#173D30] outline-none transition focus:border-[#064631] focus:ring-1 focus:ring-[#064631]"
                    >

                    @error('judul')
                        <p class="mt-2 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- KETERANGAN FOTO --}}
                <div>
                    <label
                        for="isi"
                        class="mb-2 block text-sm font-semibold text-[#355D47]"
                    >
                        Keterangan Foto
                    </label>

                    <textarea
                        id="isi"
                        name="isi"
                        rows="5"
                        placeholder="Masukkan keterangan foto (opsional)"
                        class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm leading-relaxed text-[#173D30] outline-none transition focus:border-[#064631] focus:ring-1 focus:ring-[#064631]"
                    >{{ old('isi', $foto->isi) }}</textarea>

                    <p class="mt-2 text-xs text-[#819187]">
                        Keterangan foto boleh dikosongkan.
                    </p>

                    @error('isi')
                        <p class="mt-2 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- FOTO SAAT INI --}}
                <div>
                    <label class="mb-2 block text-sm font-semibold text-[#355D47]">
                        Foto Saat Ini
                    </label>

                    @if ($foto->gambar && \Illuminate\Support\Facades\Storage::disk('public')->exists($foto->gambar))
                        <div class="mb-4">
                            <img
                                src="{{ asset('storage/' . $foto->gambar) }}"
                                alt="Foto galeri: {{ $foto->judul }}"
                                class="h-44 w-72 border border-[#DCE5DE] object-cover"
                            >
                        </div>
                    @else
                        <div class="mb-4 flex h-44 w-72 items-center justify-center bg-[#E9F0EB] text-sm text-[#819187]">
                            Foto tidak tersedia
                        </div>
                    @endif
                </div>

                {{-- GANTI FOTO --}}
                <div>
                    <label
                        for="gambar"
                        class="mb-2 block text-sm font-semibold text-[#355D47]"
                    >
                        Ganti Foto
                    </label>

                    <input
                        type="file"
                        id="gambar"
                        name="gambar"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="block w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#405A4D]
                               file:mr-4 file:border-0 file:bg-[#E9F0EB]
                               file:px-4 file:py-2 file:font-semibold file:text-[#064631]"
                    >

                    <p class="mt-2 text-xs text-[#819187]">
                        Kosongkan jika tidak ingin mengganti foto.
                        Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                    </p>

                    @error('gambar')
                        <p class="mt-2 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- PENGATURAN PUBLIKASI --}}
        <div class="border border-[#DCE5DE] bg-white">

            <div class="border-b border-[#E2E9E4] px-6 py-5">
                <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                    Pengaturan Publikasi
                </h2>

                <p class="mt-1 text-xs text-[#819187]">
                    Ubah status publikasi foto galeri.
                </p>
            </div>

            <div class="p-6">

                <label
                    for="status_publikasi"
                    class="mb-2 block text-sm font-semibold text-[#355D47]"
                >
                    Status Foto <span class="text-red-500">*</span>
                </label>

                <select
                    id="status_publikasi"
                    name="status_publikasi"
                    required
                    class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#173D30] outline-none transition focus:border-[#064631] focus:ring-1 focus:ring-[#064631]"
                >
                    <option
                        value="draft"
                        @selected(old('status_publikasi', $foto->status_publikasi) === 'draft')
                    >
                        Draft - Belum Dipublikasikan
                    </option>

                    <option
                        value="published"
                        @selected(old('status_publikasi', $foto->status_publikasi) === 'published')
                    >
                        Published - Publikasikan Foto
                    </option>
                </select>

                <p class="mt-2 text-xs leading-5 text-[#819187]">
                    Foto Draft tidak ditampilkan kepada pengunjung.
                    Foto Published nantinya ditampilkan pada Galeri wisatawan.
                </p>

                @error('status_publikasi')
                    <p class="mt-2 text-xs text-red-600">
                        {{ $message }}
                    </p>
                @enderror

            </div>
        </div>

        {{-- TOMBOL AKSI --}}
        <div class="flex flex-wrap justify-end gap-3 pb-6">

            <a
                href="{{ route('admin.konten-galeri.index') }}"
                class="inline-flex items-center justify-center border border-[#CBD9CF] bg-white px-6 py-3 text-sm font-semibold text-[#355D47] transition hover:bg-[#EAF2EC]"
            >
                Batal
            </a>

            <button
                type="submit"
                class="bg-[#064631] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#237052]"
            >
                Simpan Perubahan
            </button>

        </div>

    </form>

</div>
@endsection