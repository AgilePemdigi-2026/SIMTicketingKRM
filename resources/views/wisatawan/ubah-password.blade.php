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
                    <div class="relative">
                        <input id="current_password" name="current_password" type="password"
                           required autocomplete="current-password"
                           aria-invalid="{{ $errors->has('current_password') ? 'true' : 'false' }}"
                           @error('current_password') aria-describedby="current-password-error" @enderror
                           class="w-full rounded-none border border-[#BDCFC2] bg-white py-3 pl-4 pr-12 text-sm text-[#193B31] outline-none transition focus:border-[#2F795B] focus:ring-2 focus:ring-[#C7E1CC]">
                        <button type="button"
                                data-password-toggle="current_password"
                                aria-controls="current_password"
                                aria-label="Tampilkan password"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-[#718079] transition hover:text-[#064631] focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2F795B]">
                            <svg data-eye-open class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg data-eye-closed class="hidden h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3 21 21"/>
                                <path d="M10.6 5.1A11 11 0 0 1 12 5c6.5 0 10 7 10 7a14 14 0 0 1-3.2 4.1M6.1 6.1C3.4 8 2 12 2 12s3.5 7 10 7a10.5 10.5 0 0 0 4.3-.9"/>
                                <path d="M10.1 10.1a3 3 0 0 0 3.8 3.8"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p id="current-password-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-[#193B31]">
                        Password Baru <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <input id="password" name="password" type="password"
                           required minlength="8" autocomplete="new-password"
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           @error('password') aria-describedby="password-error" @enderror
                           class="w-full rounded-none border border-[#BDCFC2] bg-white py-3 pl-4 pr-12 text-sm text-[#193B31] outline-none transition focus:border-[#2F795B] focus:ring-2 focus:ring-[#C7E1CC]">
                        <button type="button"
                                data-password-toggle="password"
                                aria-controls="password"
                                aria-label="Tampilkan password"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-[#718079] transition hover:text-[#064631] focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2F795B]">
                            <svg data-eye-open class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg data-eye-closed class="hidden h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3 21 21"/>
                                <path d="M10.6 5.1A11 11 0 0 1 12 5c6.5 0 10 7 10 7a14 14 0 0 1-3.2 4.1M6.1 6.1C3.4 8 2 12 2 12s3.5 7 10 7a10.5 10.5 0 0 0 4.3-.9"/>
                                <path d="M10.1 10.1a3 3 0 0 0 3.8 3.8"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p id="password-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-[#193B31]">
                        Konfirmasi Password Baru <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" type="password"
                           required minlength="8" autocomplete="new-password"
                           class="w-full rounded-none border border-[#BDCFC2] bg-white py-3 pl-4 pr-12 text-sm text-[#193B31] outline-none transition focus:border-[#2F795B] focus:ring-2 focus:ring-[#C7E1CC]">
                        <button type="button"
                                data-password-toggle="password_confirmation"
                                aria-controls="password_confirmation"
                                aria-label="Tampilkan password"
                                aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-[#718079] transition hover:text-[#064631] focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-[#2F795B]">
                            <svg data-eye-open class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg data-eye-closed class="hidden h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3 21 21"/>
                                <path d="M10.6 5.1A11 11 0 0 1 12 5c6.5 0 10 7 10 7a14 14 0 0 1-3.2 4.1M6.1 6.1C3.4 8 2 12 2 12s3.5 7 10 7a10.5 10.5 0 0 0 4.3-.9"/>
                                <path d="M10.1 10.1a3 3 0 0 0 3.8 3.8"/>
                            </svg>
                        </button>
                    </div>
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


@push('scripts')
<script>
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            const isVisible = input.type === 'password';
            input.type = isVisible ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(isVisible));
            button.setAttribute('aria-label', isVisible ? 'Sembunyikan password' : 'Tampilkan password');
            button.querySelector('[data-eye-open]').classList.toggle('hidden', isVisible);
            button.querySelector('[data-eye-closed]').classList.toggle('hidden', !isVisible);
        });
    });
</script>
@endpush