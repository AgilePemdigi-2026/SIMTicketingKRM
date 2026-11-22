@extends('layouts.app')

@section('title', 'Hubungi Kami - Kebun Raya Mangrove Surabaya')

@section('content')
@php
    // Seluruh informasi di bawah berasal dari model InfoKontak (Admin).
    $sosial = [
        ['label' => 'Instagram', 'value' => $kontak?->instagram, 'icon' => '◎'],
        ['label' => 'Facebook', 'value' => $kontak?->facebook, 'icon' => 'f'],
        ['label' => 'YouTube', 'value' => $kontak?->youtube, 'icon' => '▶'],
        ['label' => 'Twitter / X', 'value' => $kontak?->twitter, 'icon' => '𝕏'],
    ];
    $hasSosial = collect($sosial)->contains(fn ($item) => filled($item['value']));
    $mapsUrl = $kontak?->google_maps_url;
    $hasMapsUrl = filled($mapsUrl) && filter_var($mapsUrl, FILTER_VALIDATE_URL);
@endphp

<div class="mx-auto w-full max-w-[1180px] px-4 pb-20 pt-10 sm:px-6 sm:pb-24 sm:pt-14 lg:px-8 lg:pt-16">
    {{-- Breadcrumb menggunakan komponen bersama --}}
    <x-breadcrumb :items="[
        ['label' => 'Kontak']
    ]" />

    <h1 class="krm-page-title">
        Hubungi Kami
    </h1>

    <div class="mt-7 grid items-start gap-6 lg:grid-cols-2">
        {{-- Kolom kiri: informasi kontak dan media sosial --}}
        <div class="space-y-5">
            <section class="border border-[#D6E1DA] bg-white px-5 py-6 sm:px-7 sm:py-7" aria-labelledby="informasi-kontak-title">
                <h2 id="informasi-kontak-title" class="border-b border-[#D6E1DA] pb-4 text-[13px] font-bold uppercase tracking-[0.06em] text-[#193B31]">
                    Informasi Kontak
                </h2>

                <div class="mt-6 space-y-6">
                    <div class="flex items-start gap-4">
                        <svg aria-hidden="true" class="mt-1 h-5 w-5 shrink-0 text-[#2F795B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3z"/><path d="M9 3v15M15 6v15"/></svg>
                        <div>
                            <p class="text-[12px] text-[#718079]">Alamat</p>
                            <p class="mt-1 whitespace-pre-line text-[13px] font-semibold leading-6 text-[#193B31]">{{ $kontak?->alamat ?: 'Alamat belum tersedia.' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <svg aria-hidden="true" class="mt-1 h-5 w-5 shrink-0 text-[#2F795B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3.1 5.2 2 2 0 0 1 5.1 3h3a2 2 0 0 1 2 1.7l.5 2.8a2 2 0 0 1-.6 1.8L8.2 11a16 16 0 0 0 4.8 4.8l1.7-1.8a2 2 0 0 1 1.8-.6l2.8.5a2 2 0 0 1 1.7 2z"/></svg>
                        <div>
                            <p class="text-[12px] text-[#718079]">Telepon</p>
                            @if ($kontak?->nomor_telepon)
                                <a href="tel:{{ preg_replace('/[^+0-9]/', '', $kontak->nomor_telepon) }}" class="mt-1 block text-[13px] font-semibold text-[#193B31] hover:underline">{{ $kontak->nomor_telepon }}</a>
                            @else
                                <p class="mt-1 text-[13px] text-[#64786D]">Belum tersedia.</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <svg aria-hidden="true" class="mt-1 h-5 w-5 shrink-0 text-[#2F795B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        <div>
                            <p class="text-[12px] text-[#718079]">Email</p>
                            @if ($kontak?->email)
                                <a href="mailto:{{ $kontak->email }}" class="mt-1 block break-all text-[13px] font-semibold text-[#193B31] hover:underline">{{ $kontak->email }}</a>
                            @else
                                <p class="mt-1 text-[13px] text-[#64786D]">Belum tersedia.</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-start gap-4">
                        <svg aria-hidden="true" class="mt-1 h-5 w-5 shrink-0 text-[#2F795B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <div>
                            <p class="text-[12px] text-[#718079]">Jam Operasional</p>
                            <p class="mt-1 text-[13px] font-semibold leading-6 text-[#193B31]">{{ $kontak?->jam_operasional ?: 'Belum tersedia.' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border border-[#D6E1DA] bg-white px-5 py-6 sm:px-7" aria-labelledby="sosial-title">
                <h2 id="sosial-title" class="border-b border-[#D6E1DA] pb-4 text-[13px] font-bold uppercase tracking-[0.06em] text-[#193B31]">
                    Media Sosial
                </h2>

                @if ($hasSosial)
                    <div class="mt-4 divide-y divide-[#E8EEE9]">
                        @foreach ($sosial as $item)
                            @if (filled($item['value']))
                                <div class="flex items-center gap-4 py-3">
                                    <span aria-hidden="true" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#064631] text-[14px] font-bold text-white">{{ $item['icon'] }}</span>
                                    <div class="min-w-0">
                                        <p class="text-[11px] text-[#718079]">{{ $item['label'] }}</p>
                                        @if (filter_var($item['value'], FILTER_VALIDATE_URL))
                                            <a href="{{ $item['value'] }}" target="_blank" rel="noopener noreferrer" class="block break-words text-[13px] font-semibold text-[#193B31] hover:underline">{{ $item['value'] }}</a>
                                        @else
                                            <p class="break-words text-[13px] font-semibold text-[#193B31]">{{ $item['value'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="mt-5 text-[13px] text-[#718079]">Informasi media sosial belum tersedia.</p>
                @endif
            </section>
        </div>

        {{-- Kolom kanan: lokasi dan formulir pesan --}}
        <div class="space-y-5">
            <section class="flex min-h-[300px] flex-col items-center justify-center border border-[#D6E1DA] bg-[#E6EFEB] px-5 py-8 text-center sm:min-h-[330px]" aria-labelledby="lokasi-title">
                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white text-[#064631]">
                    <svg aria-hidden="true" class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3z"/><path d="M9 3v15M15 6v15"/></svg>
                </span>
                <h2 id="lokasi-title" class="mt-5 text-[16px] font-bold text-[#064631]">Kebun Raya Mangrove Surabaya</h2>
                <p class="mt-2 max-w-sm text-[12px] leading-5 text-[#718079]">{{ $kontak?->alamat ?: 'Informasi lokasi belum tersedia.' }}</p>

                @if ($hasMapsUrl)
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex min-h-10 items-center justify-center border border-[#064631] bg-white px-5 py-2 text-[12px] font-bold text-[#064631] transition hover:bg-[#F3F8F4]">
                        Buka Google Maps
                    </a>
                @else
                    <p class="mt-5 text-[12px] text-[#718079]">Tautan Google Maps belum tersedia.</p>
                @endif
            </section>

            <section class="border border-[#D6E1DA] bg-white px-5 py-6 sm:px-7" aria-labelledby="kirim-pesan-title">
                <h2 id="kirim-pesan-title" class="border-b border-[#D6E1DA] pb-4 text-[13px] font-bold uppercase tracking-[0.06em] text-[#193B31]">Kirim Pesan</h2>

                {{-- Notifikasi pengiriman berhasil --}}
                @if (session('contact_success'))
                    <div role="status"
                        class="mt-5 border border-[#A1C977] bg-[#F1F6EF] px-4 py-3 text-[13px] text-[#064631]">
                        {{ session('contact_success') }}
                    </div>
                @endif

                {{-- Notifikasi gagal mengirim email --}}
                @if (session('contact_error'))
                    <div role="alert"
                        class="mt-5 border border-red-300 bg-red-50 px-4 py-3 text-[13px] text-red-700">
                        {{ session('contact_error') }}
                    </div>
                @endif

                {{-- Kesalahan validasi --}}
                @if ($errors->any())
                    <div role="alert"
                        class="mt-5 border border-red-300 bg-red-50 px-4 py-3 text-[13px] text-red-700">
                        <p class="font-bold">Periksa kembali data pesan:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ route('wisatawan.kontak.kirim') }}"
                    method="POST"
                    class="mt-5 space-y-4"
                >
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">

                        {{-- Nama --}}
                        <div>
                            <label for="pesan_nama"
                                class="mb-2 block text-[12px] text-[#64786D]">
                                Nama
                            </label>

                            <input
                                id="pesan_nama"
                                name="nama"
                                type="text"
                                value="{{ old('nama') }}"
                                placeholder="Nama lengkap"
                                autocomplete="name"
                                required
                                minlength="2"
                                maxlength="100"
                                class="w-full border border-[#D6E1DA] bg-[#FAFCFA] px-4 py-3 text-[13px] text-[#193B31] outline-none placeholder:text-[#718079] focus:border-[#064631]"
                            >
                        </div>

                        {{-- Email --}}
                        <div>
                            <label for="pesan_email"
                                class="mb-2 block text-[12px] text-[#64786D]">
                                Email
                            </label>

                            <input
                                id="pesan_email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                placeholder="email@contoh.com"
                                autocomplete="email"
                                required
                                maxlength="254"
                                class="w-full border border-[#D6E1DA] bg-[#FAFCFA] px-4 py-3 text-[13px] text-[#193B31] outline-none placeholder:text-[#718079] focus:border-[#064631]"
                            >
                        </div>
                    </div>

                    {{-- Subjek --}}
                    <div>
                        <label for="pesan_subjek"
                            class="mb-2 block text-[12px] text-[#64786D]">
                            Subjek
                        </label>

                        <input
                            id="pesan_subjek"
                            name="subjek"
                            type="text"
                            value="{{ old('subjek') }}"
                            placeholder="Subjek pesan"
                            required
                            minlength="3"
                            maxlength="150"
                            class="w-full border border-[#D6E1DA] bg-[#FAFCFA] px-4 py-3 text-[13px] text-[#193B31] outline-none placeholder:text-[#718079] focus:border-[#064631]"
                        >
                    </div>

                    {{-- Isi Pesan --}}
                    <div>
                        <label for="pesan_isi"
                            class="mb-2 block text-[12px] text-[#64786D]">
                            Pesan
                        </label>

                        <textarea
                            id="pesan_isi"
                            name="pesan"
                            rows="5"
                            placeholder="Tulis pesan Anda di sini..."
                            required
                            minlength="10"
                            maxlength="5000"
                            class="w-full resize-y border border-[#D6E1DA] bg-[#FAFCFA] px-4 py-3 text-[13px] text-[#193B31] outline-none placeholder:text-[#718079] focus:border-[#064631]"
                        >{{ old('pesan') }}</textarea>
                    </div>

                    {{-- Tombol Kirim --}}
                    <button
                        type="submit"
                        class="flex min-h-11 w-full items-center justify-center bg-[#064631] px-4 py-3 text-[13px] font-bold text-white transition hover:bg-[#075C43] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#2F795B]"
                    >
                        Kirim Pesan
                    </button>

                    <p class="text-[12px] leading-5 text-[#718079]">
                        Pesan Anda akan diteruskan melalui email kepada pengelola.
                    </p>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
