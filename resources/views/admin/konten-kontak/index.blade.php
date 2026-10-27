@extends('layouts.admin')

@section('title', 'Manajemen Konten - Info Kontak')

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
            'kategoriAktif' => 'kontak'
        ])

        {{-- PANEL INFO KONTAK --}}
        <div class="min-w-0 flex-1 border border-[#DCE5DE] bg-white p-5 sm:p-6">

            <div class="mb-6 border-b border-[#E2E9E4] pb-5">
                <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                    Info Kontak
                </h2>

                <p class="mt-2 text-xs leading-5 text-[#819187]">
                    Kelola informasi kontak, lokasi, jam operasional,
                    dan media sosial Kebun Raya Mangrove Surabaya.
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

            {{-- FORM INFORMASI KONTAK --}}
            <form
                action="{{ route('admin.konten-kontak.update') }}"
                method="POST"
                class="space-y-6"
            >
                @csrf
                @method('PUT')

                {{-- ALAMAT --}}
                <div>
                    <label for="alamat"
                        class="mb-2 block text-xs font-bold text-[#355D47]">
                        Alamat
                    </label>

                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="3"
                        placeholder="Masukkan alamat destinasi"
                        class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                    >{{ old('alamat', $kontak?->alamat) }}</textarea>
                </div>

                {{-- NOMOR TELEPON DAN EMAIL --}}
                <div class="grid gap-5 sm:grid-cols-2">

                    <div>
                        <label for="nomor_telepon"
                            class="mb-2 block text-xs font-bold text-[#355D47]">
                            Nomor Telepon
                        </label>

                        <input
                            type="text"
                            id="nomor_telepon"
                            name="nomor_telepon"
                            maxlength="30"
                            value="{{ old('nomor_telepon', $kontak?->nomor_telepon) }}"
                            placeholder="Masukkan nomor telepon"
                            class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                        >
                    </div>

                    <div>
                        <label for="email"
                            class="mb-2 block text-xs font-bold text-[#355D47]">
                            Email
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            maxlength="255"
                            value="{{ old('email', $kontak?->email) }}"
                            placeholder="Masukkan alamat email"
                            class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                        >
                    </div>

                </div>

                {{-- JAM OPERASIONAL --}}
                <div>
                    <label for="jam_operasional"
                        class="mb-2 block text-xs font-bold text-[#355D47]">
                        Jam Operasional
                    </label>

                    <input
                        type="text"
                        id="jam_operasional"
                        name="jam_operasional"
                        maxlength="255"
                        value="{{ old('jam_operasional', $kontak?->jam_operasional) }}"
                        placeholder="Contoh: Senin - Minggu, 07.00 - 17.00 WIB"
                        class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                    >
                </div>

                {{-- GOOGLE MAPS --}}
                <div>
                    <label for="google_maps_url"
                        class="mb-2 block text-xs font-bold text-[#355D47]">
                        URL Google Maps
                    </label>

                    <input
                        type="url"
                        id="google_maps_url"
                        name="google_maps_url"
                        maxlength="255"
                        value="{{ old('google_maps_url', $kontak?->google_maps_url) }}"
                        placeholder="https://www.google.com/maps/..."
                        class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                    >

                    <p class="mt-2 text-xs text-[#819187]">
                        Gunakan tautan lokasi resmi dari Google Maps.
                    </p>
                </div>

                {{-- MEDIA SOSIAL --}}
                <div class="border-t border-[#E2E9E4] pt-6">

                    <h3 class="mb-2 text-xs font-bold uppercase tracking-[0.08em] text-[#173D30]">
                        Media Sosial
                    </h3>

                    <p class="mb-5 text-xs text-[#819187]">
                        Isi akun atau tautan media sosial resmi yang tersedia.
                    </p>

                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- INSTAGRAM --}}
                        <div>
                            <label for="instagram"
                                class="mb-2 block text-xs font-bold text-[#355D47]">
                                Instagram
                            </label>

                            <input
                                type="text"
                                id="instagram"
                                name="instagram"
                                maxlength="255"
                                value="{{ old('instagram', $kontak?->instagram) }}"
                                placeholder="@username atau URL Instagram"
                                class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                            >
                        </div>

                        {{-- FACEBOOK --}}
                        <div>
                            <label for="facebook"
                                class="mb-2 block text-xs font-bold text-[#355D47]">
                                Facebook
                            </label>

                            <input
                                type="text"
                                id="facebook"
                                name="facebook"
                                maxlength="255"
                                value="{{ old('facebook', $kontak?->facebook) }}"
                                placeholder="Nama akun atau URL Facebook"
                                class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                            >
                        </div>

                        {{-- YOUTUBE --}}
                        <div>
                            <label for="youtube"
                                class="mb-2 block text-xs font-bold text-[#355D47]">
                                YouTube
                            </label>

                            <input
                                type="text"
                                id="youtube"
                                name="youtube"
                                maxlength="255"
                                value="{{ old('youtube', $kontak?->youtube) }}"
                                placeholder="Nama channel atau URL YouTube"
                                class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                            >
                        </div>

                        {{-- TWITTER / X --}}
                        <div>
                            <label for="twitter"
                                class="mb-2 block text-xs font-bold text-[#355D47]">
                                Twitter / X
                            </label>

                            <input
                                type="text"
                                id="twitter"
                                name="twitter"
                                maxlength="255"
                                value="{{ old('twitter', $kontak?->twitter) }}"
                                placeholder="@username atau URL X"
                                class="w-full border border-[#CBD9CF] bg-white px-4 py-3 text-sm text-[#193B31] outline-none focus:border-[#064631]"
                            >
                        </div>

                    </div>
                </div>

                {{-- TOMBOL SIMPAN --}}
                <div class="flex justify-end border-t border-[#E2E9E4] pt-6">
                    <button
                        type="submit"
                        class="inline-flex min-h-11 items-center justify-center bg-[#064631] px-7 py-3 text-xs font-bold text-white transition hover:bg-[#0A674A]"
                    >
                        Simpan Informasi Kontak
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endsection
