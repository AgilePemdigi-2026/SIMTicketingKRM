@extends('layouts.app')

@section('title', 'Edit Profil | Kebun Raya Mangrove Surabaya')

@section('content')
    <div class="mx-auto w-full max-w-[1180px] px-4 pb-16 pt-12 sm:px-6 sm:pb-20 sm:pt-16 lg:px-8 lg:pt-20">
        <div class="mb-8">
            <p class="text-xs font-bold tracking-[0.18em] text-[#2F795B]">AKUN WISATAWAN</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-[#193B31] sm:text-[36px]">Edit Profil</h1>
            <div class="mt-2 h-[3px] w-36 bg-[#A1C977]" aria-hidden="true"></div>
            <p class="mt-5 max-w-2xl text-sm leading-6 text-[#718079]">
                Perbarui nama, nomor telepon, atau email. Nomor identitas dan jenis wisatawan ditampilkan sebagai informasi akun.
            </p>
        </div>

        <div class="max-w-[780px] border border-[#DAE3DD] bg-white p-5 sm:p-8">
            <h2 class="text-sm font-bold tracking-[0.1em] text-[#193B31]">INFORMASI PRIBADI</h2>
            <div class="mb-6 mt-5 h-px bg-[#DAE3DD]"></div>

            <form method="POST" action="{{ route('wisatawan.profil.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-[#193B31]">Nama Lengkap <span class="text-red-600">*</span></label>
                    <input id="name" name="name" type="text" required maxlength="255"
                           autocomplete="name" value="{{ old('name', $user->name) }}"
                           aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                           @error('name') aria-describedby="name-error" @enderror
                           class="w-full rounded-none border border-[#BDCFC2] bg-white px-4 py-3 text-sm text-[#193B31] outline-none transition focus:border-[#2F795B] focus:ring-2 focus:ring-[#C7E1CC]">
                    @error('name')
                        <p id="name-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="phone" class="mb-2 block text-sm font-semibold text-[#193B31]">Nomor Telepon <span class="text-red-600">*</span></label>
                        <input id="phone" name="phone" type="tel" required maxlength="25"
                               autocomplete="tel" value="{{ old('phone', $user->phone) }}"
                               aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                               @error('phone') aria-describedby="phone-error" @enderror
                               class="w-full rounded-none border border-[#BDCFC2] bg-white px-4 py-3 text-sm text-[#193B31] outline-none transition focus:border-[#2F795B] focus:ring-2 focus:ring-[#C7E1CC]">
                        @error('phone')
                            <p id="phone-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-[#193B31]">Email <span class="text-red-600">*</span></label>
                        <input id="email" name="email" type="email" required maxlength="255"
                               autocomplete="email" value="{{ old('email', $user->email) }}"
                               aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                               @error('email') aria-describedby="email-error" @enderror
                               class="w-full rounded-none border border-[#BDCFC2] bg-white px-4 py-3 text-sm text-[#193B31] outline-none transition focus:border-[#2F795B] focus:ring-2 focus:ring-[#C7E1CC]">
                        @error('email')
                            <p id="email-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-6 border-t border-[#E6ECE7] pt-6 text-sm sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-[#718079]">Nomor Identitas</p>
                        <p class="break-all font-semibold text-[#193B31]">{{ $user->identity_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="mb-1 text-[#718079]">Jenis Wisatawan</p>
                        <p class="font-semibold text-[#193B31]">{{ $user->visitor_type ?: '-' }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 border-t border-[#E6ECE7] pt-6">
                    <button type="submit"
                            class="inline-flex min-h-12 items-center justify-center bg-[#064631] px-7 py-3 text-sm font-semibold text-white transition hover:bg-[#2F795B] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#064631]">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('wisatawan.profil') }}"
                       class="inline-flex min-h-12 items-center justify-center border border-[#064631] px-7 py-3 text-sm font-semibold text-[#064631] transition hover:bg-[#EEF4EE] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#064631]">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection