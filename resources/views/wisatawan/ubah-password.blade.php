@extends('layouts.app')

@section('title', 'Ubah Password | Kebun Raya Mangrove Surabaya')

@section('content')
    <div class="mx-auto w-full max-w-[1180px] px-4 pb-16 pt-12 sm:px-6 sm:pb-20 sm:pt-16 lg:px-8 lg:pt-20">
        <div class="mb-8">
            <p class="text-xs font-bold tracking-[0.18em] text-[#2F795B]">KEAMANAN AKUN</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-[#193B31] sm:text-[36px]">Ubah Password</h1>
            <div class="mt-2 h-[3px] w-36 bg-[#A1C977]" aria-hidden="true"></div>
            <p class="mt-5 max-w-2xl text-sm leading-6 text-[#718079]">
                Masukkan password saat ini, lalu tentukan password baru minimal 8 karakter.
            </p>
        </div>

        <div class="max-w-[650px] border border-[#DAE3DD] bg-white p-5 sm:p-8">
            <h2 class="text-sm font-bold tracking-[0.1em] text-[#193B31]">PENGATURAN PASSWORD</h2>
            <div class="mb-6 mt-5 h-px bg-[#DAE3DD]"></div>

            <form method="POST" action="{{ route('wisatawan.password.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="mb-2 block text-sm font-semibold text-[#193B31]">
                        Password Lama <span class="text-red-600">*</span>
                    </label>
                    <input id="current_password" name="current_password" type="password"
                           required autocomplete="current-password"
                           aria-invalid="{{ $errors->has('current_password') ? 'true' : 'false' }}"
                           @error('current_password') aria-describedby="current-password-error" @enderror
                           class="w-full rounded-none border border-[#BDCFC2] bg-white px-4 py-3 text-sm text-[#193B31] outline-none transition focus:border-[#2F795B] focus:ring-2 focus:ring-[#C7E1CC]">
                    @error('current_password')
                        <p id="current-password-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-[#193B31]">
                        Password Baru <span class="text-red-600">*</span>
                    </label>
                    <input id="password" name="password" type="password"
                           required minlength="8" autocomplete="new-password"
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           @error('password') aria-describedby="password-error" @enderror
                           class="w-full rounded-none border border-[#BDCFC2] bg-white px-4 py-3 text-sm text-[#193B31] outline-none transition focus:border-[#2F795B] focus:ring-2 focus:ring-[#C7E1CC]">
                    @error('password')
                        <p id="password-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-[#193B31]">
                        Konfirmasi Password Baru <span class="text-red-600">*</span>
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           required minlength="8" autocomplete="new-password"
                           class="w-full rounded-none border border-[#BDCFC2] bg-white px-4 py-3 text-sm text-[#193B31] outline-none transition focus:border-[#2F795B] focus:ring-2 focus:ring-[#C7E1CC]">
                </div>

                <div class="flex flex-wrap gap-3 border-t border-[#E6ECE7] pt-6">
                    <button type="submit"
                            class="inline-flex min-h-12 items-center justify-center bg-[#064631] px-7 py-3 text-sm font-semibold text-white transition hover:bg-[#2F795B] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#064631]">
                        Simpan Password Baru
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