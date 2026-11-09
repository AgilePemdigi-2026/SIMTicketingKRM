@extends('layouts.admin')

@section('title', 'Manajemen Konten - Info Destinasi')

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
            'kategoriAktif' => 'destinasi'
        ])

        {{-- PANEL INFO DESTINASI --}}
        <div class="min-w-0 flex-1 border border-[#DCE5DE] bg-white p-5 sm:p-6">

            <div class="mb-6 border-b border-[#E2E9E4] pb-5">

                <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                    Info Destinasi
                </h2>

                <p class="mt-2 text-xs leading-5 text-[#819187]">
                    Kelola profil, deskripsi, foto utama, dan informasi
                    operasional Kebun Raya Mangrove Surabaya.
                </p>

            </div>

            {{-- PESAN SUKSES --}}
            @if (session('success'))
                <div class="mb-5 border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            {{-- PESAN VALIDASI --}}
            @if ($errors->any())
                <div class="mb-5 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">

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

            <form
                action="{{ route('admin.konten-destinasi.update') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                {{-- NAMA DESTINASI --}}
                <div>
                    <label for="nama_destinasi"
                        class="mb-2 block text-xs font-bold text-[#355D47]">
                        Nama Destinasi <span class="text-red-600">*</span>
                    </label>

                    <input
                        type="text"
                        id="nama_destinasi"
                        name="nama_destinasi"
                        value="{{ old('nama_destinasi', $destinasi?->nama_destinasi) }}"
                        maxlength="150"
                        required
                        placeholder="Masukkan nama destinasi"
                        class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                    >
                </div>

                {{-- FOTO UTAMA --}}
                <div>
                    <label for="foto_utama"
                        class="mb-2 block text-xs font-bold text-[#355D47]">
                        Foto Utama Destinasi
                    </label>

                    @if ($destinasi?->foto_utama)

                        <div class="mb-4 max-w-lg overflow-hidden border border-[#DCE5DE] bg-[#E9F0EB]">

                            <img
                                src="{{ asset('storage/' . $destinasi->foto_utama) }}"
                                alt="Foto utama destinasi"
                                class="h-52 w-full object-cover"
                            >

                        </div>

                        <p class="mb-3 text-xs text-[#718079]">
                            Foto saat ini. Unggah foto baru jika ingin menggantinya.
                        </p>

                    @endif

                    <input
                        type="file"
                        id="foto_utama"
                        name="foto_utama"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="block w-full border border-[#CBD9CF] bg-white p-3 text-xs text-[#355D47] file:mr-4 file:border-0 file:bg-[#E9F0EB] file:px-3 file:py-2 file:font-semibold file:text-[#064631]"
                    >

                    <p class="mt-2 text-xs text-[#819187]">
                        Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                    </p>
                </div>

                {{-- DESKRIPSI SINGKAT --}}
                <div>
                    <label for="deskripsi_singkat"
                        class="mb-2 block text-xs font-bold text-[#355D47]">
                        Deskripsi Singkat
                    </label>

                    <textarea
                        id="deskripsi_singkat"
                        name="deskripsi_singkat"
                        rows="3"
                        placeholder="Masukkan gambaran singkat destinasi"
                        class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm leading-6 text-[#193B31] outline-none focus:border-[#064631]"
                    >{{ old('deskripsi_singkat', $destinasi?->deskripsi_singkat) }}</textarea>
                </div>

                {{-- DESKRIPSI LENGKAP --}}
                <div>
                    <label for="deskripsi_lengkap"
                        class="mb-2 block text-xs font-bold text-[#355D47]">
                        Deskripsi Lengkap
                    </label>

                    <textarea
                        id="deskripsi_lengkap"
                        name="deskripsi_lengkap"
                        rows="7"
                        placeholder="Masukkan informasi lengkap mengenai destinasi wisata"
                        class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm leading-6 text-[#193B31] outline-none focus:border-[#064631]"
                    >{{ old('deskripsi_lengkap', $destinasi?->deskripsi_lengkap) }}</textarea>
                </div>

                {{-- JAM OPERASIONAL --}}
                <div class="grid gap-5 sm:grid-cols-2">

                    <div>
                        <label for="jam_buka"
                            class="mb-2 block text-xs font-bold text-[#355D47]">
                            Jam Buka
                        </label>

                        <input
                            type="time"
                            id="jam_buka"
                            name="jam_buka"
                            value="{{ old('jam_buka', $destinasi?->jam_buka ? substr((string) $destinasi->jam_buka, 0, 5) : '') }}"
                            class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                        >
                    </div>

                    <div>
                        <label for="jam_tutup"
                            class="mb-2 block text-xs font-bold text-[#355D47]">
                            Jam Tutup
                        </label>

                        <input
                            type="time"
                            id="jam_tutup"
                            name="jam_tutup"
                            value="{{ old('jam_tutup', $destinasi?->jam_tutup ? substr((string) $destinasi->jam_tutup, 0, 5) : '') }}"
                            class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                        >
                    </div>

                </div>

                {{-- ALAMAT --}}
                <div>
                    <label for="alamat"
                        class="mb-2 block text-xs font-bold text-[#355D47]">
                        Alamat Destinasi
                    </label>

                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="3"
                        placeholder="Masukkan alamat resmi destinasi"
                        class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm leading-6 text-[#193B31] outline-none focus:border-[#064631]"
                    >{{ old('alamat', $destinasi?->alamat) }}</textarea>
                </div>

                {{-- TOMBOL SIMPAN --}}
                <div class="flex justify-end border-t border-[#E2E9E4] pt-6">

                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center justify-center bg-[#064631] px-7 py-3 text-xs font-bold text-white transition hover:bg-[#0A674A]"
                    >
                        Simpan Informasi Destinasi
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
@endsection