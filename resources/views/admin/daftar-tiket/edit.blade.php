@extends('layouts.admin')

@section('title', 'Edit Tiket - Kebun Raya Mangrove')

@section('content')
@php
    $kategoriTiketSaatIni = old('kategori_tiket', $daftarTiket->kategori_tiket);
    $kategoriWisatawanSaatIni = old('kategori_wisatawan', $daftarTiket->kategori_wisatawan);

    $wahanaTerpilih = old(
        'wahana_ids',
        $daftarTiket->daftarWahana->pluck('id_wahana')->all()
    );
@endphp

<div class="max-w-[1400px]">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4 border-b border-[#D4DED7] pb-6">
        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>

            <h1 class="text-2xl font-semibold text-[#173D30]">
                Edit Tiket — {{ $daftarTiket->nama_tiket }}
            </h1>

            <p class="mt-2 text-sm text-[#74877B]">
                Perbarui informasi produk tiket, harga, dan ketentuannya.
            </p>
        </div>

        <a
            href="{{ route('admin.daftar-tiket.index') }}"
            class="inline-flex items-center border border-[#8CAA98] bg-white px-4 py-2.5 text-xs font-semibold text-[#17523B] transition hover:bg-[#EDF4EE]"
        >
            ← Kembali
        </a>
    </div>

    {{-- ERROR VALIDASI --}}
    @if ($errors->any())
        <div class="mb-6 border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <p class="mb-2 font-bold">
                Periksa kembali data yang dimasukkan:
            </p>

            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM EDIT TIKET --}}
    <div class="max-w-[1120px] border border-[#DCE5DE] bg-white p-5 sm:p-7">

        <div class="mb-6 border-b border-[#E0E8E2] pb-4">
            <h2 class="text-xs font-bold uppercase tracking-[0.09em] text-[#294A3C]">
                Form Edit Tiket
            </h2>

            <p class="mt-2 text-xs text-[#89988E]">
                Perbarui data tiket yang sudah terdaftar.
            </p>
        </div>

        <form
            action="{{ route('admin.daftar-tiket.update', $daftarTiket->id_tiket) }}"
            method="POST"
            class="space-y-6"
        >
            @csrf
            @method('PUT')

            {{-- NAMA, KATEGORI, WISATAWAN --}}
            <div class="grid gap-5 md:grid-cols-3">

                {{-- NAMA TIKET --}}
                <div>
                    <label for="nama_tiket"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Nama Tiket <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="nama_tiket"
                        name="nama_tiket"
                        value="{{ old('nama_tiket', $daftarTiket->nama_tiket) }}"
                        maxlength="100"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >

                    @error('nama_tiket')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KATEGORI TIKET --}}
                <div>
                    <label for="kategori_tiket"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Kategori Tiket <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="kategori_tiket"
                        name="kategori_tiket"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >
                        <option value="">-- Pilih Kategori --</option>

                        {{-- Pertahankan kategori lama bila berbeda dari opsi form --}}
                        @if (
                            $kategoriTiketSaatIni &&
                            !in_array($kategoriTiketSaatIni, ['Tiket Masuk', 'Tiket Wahana'])
                        )
                            <option value="{{ $kategoriTiketSaatIni }}" selected>
                                {{ $kategoriTiketSaatIni }}
                            </option>
                        @endif

                        <option
                            value="Tiket Masuk"
                            {{ $kategoriTiketSaatIni == 'Tiket Masuk' ? 'selected' : '' }}
                        >
                            Tiket Masuk
                        </option>

                        <option
                            value="Tiket Wahana"
                            {{ $kategoriTiketSaatIni == 'Tiket Wahana' ? 'selected' : '' }}
                        >
                            Tiket Wahana
                        </option>
                    </select>

                    @error('kategori_tiket')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- JENIS WISATAWAN --}}
                <div>
                    <label for="kategori_wisatawan"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Jenis Wisatawan <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="kategori_wisatawan"
                        name="kategori_wisatawan"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >
                        <option value="">-- Pilih Jenis Wisatawan --</option>

                        {{-- Pertahankan kategori wisatawan dari data lama --}}
                        @if (
                            $kategoriWisatawanSaatIni &&
                            !in_array(
                                $kategoriWisatawanSaatIni,
                                ['Domestik (WNI)', 'Mancanegara (WNA)']
                            )
                        )
                            <option value="{{ $kategoriWisatawanSaatIni }}" selected>
                                {{ $kategoriWisatawanSaatIni }}
                            </option>
                        @endif

                        <option
                            value="Domestik (WNI)"
                            {{ $kategoriWisatawanSaatIni == 'Domestik (WNI)' ? 'selected' : '' }}
                        >
                            Domestik (WNI)
                        </option>

                        <option
                            value="Mancanegara (WNA)"
                            {{ $kategoriWisatawanSaatIni == 'Mancanegara (WNA)' ? 'selected' : '' }}
                        >
                            Mancanegara (WNA)
                        </option>
                    </select>

                    @error('kategori_wisatawan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- HARGA DAN KUOTA --}}
            <div class="grid gap-5 md:grid-cols-3">

                {{-- HARGA DEWASA --}}
                <div>
                    <label for="harga_dewasa"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Harga Dewasa (Rp) <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        id="harga_dewasa"
                        name="harga_dewasa"
                        value="{{ old('harga_dewasa', $daftarTiket->harga_dewasa) }}"
                        min="0"
                        step="0.01"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >

                    @error('harga_dewasa')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- HARGA ANAK --}}
                <div>
                    <label for="harga_anak"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Harga Anak (Rp) <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        id="harga_anak"
                        name="harga_anak"
                        value="{{ old('harga_anak', $daftarTiket->harga_anak) }}"
                        min="0"
                        step="0.01"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >

                    @error('harga_anak')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- KUOTA --}}
                <div>
                    <label for="kuota"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Kuota <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        id="kuota"
                        name="kuota"
                        value="{{ old('kuota', $daftarTiket->kuota) }}"
                        min="0"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >

                    @error('kuota')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- KETENTUAN --}}
            <div>
                <label for="ketentuan"
                       class="mb-2 block text-xs font-semibold text-[#415C4D]">
                    Aturan Penggunaan / Ketentuan
                </label>

                <textarea
                    id="ketentuan"
                    name="ketentuan"
                    rows="4"
                    placeholder="Tuliskan aturan dan ketentuan penggunaan tiket..."
                    class="w-full resize-y border border-[#D3DED5] bg-white px-3 py-3 text-sm leading-6 text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                >{{ old('ketentuan', $daftarTiket->ketentuan) }}</textarea>

                @error('ketentuan')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- PILIH WAHANA --}}
            <fieldset class="border border-[#DBE7DE] bg-[#FAFCFA] p-4 sm:p-5">

                <legend class="px-2 text-xs font-bold uppercase tracking-[0.07em] text-[#294A3C]">
                    Wahana yang Termasuk dalam Tiket
                </legend>

                <p class="mb-4 text-xs leading-6 text-[#829589]">
                    Centang wahana yang termasuk dalam tiket.
                    Wahana yang sudah terhubung akan tercentang otomatis.
                    Tiket masuk saja boleh tanpa wahana.
                </p>

                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">

                    <span
                        id="wahana-count"
                        class="text-xs font-bold text-[#236247]"
                        aria-live="polite"
                    >
                        0 wahana dipilih
                    </span>

                    <button
                        type="button"
                        id="clear-wahana"
                        class="border border-[#E9CCBC] bg-white px-3 py-2 text-xs font-semibold text-[#B65B36] transition hover:bg-[#FFF2ED]"
                    >
                        Batalkan Semua Pilihan
                    </button>

                </div>

                <div class="max-h-[220px] space-y-1 overflow-y-auto border border-[#D3DED5] bg-white p-3">

                    @forelse ($wahana as $item)
                        <label class="flex cursor-pointer items-center gap-3 border-b border-[#EDF1ED] px-2 py-3 last:border-0 hover:bg-[#F5F9F6]">

                            <input
                                type="checkbox"
                                name="wahana_ids[]"
                                value="{{ $item->id_wahana }}"
                                {{ in_array($item->id_wahana, $wahanaTerpilih) ? 'checked' : '' }}
                                class="h-4 w-4 accent-[#176B45]"
                            >

                            <span class="text-sm text-[#355746]">
                                {{ $item->nama_wahana }}
                            </span>

                        </label>
                    @empty
                        <p class="py-5 text-center text-sm text-[#829589]">
                            Belum ada data wahana.
                        </p>
                    @endforelse

                </div>

                @error('wahana_ids')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror

                @error('wahana_ids.*')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror

            </fieldset>

            {{-- STATUS --}}
            <div class="max-w-[350px]">
                <label for="status_aktif"
                       class="mb-2 block text-xs font-semibold text-[#415C4D]">
                    Status <span class="text-red-500">*</span>
                </label>

                <select
                    id="status_aktif"
                    name="status_aktif"
                    required
                    class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                >
                    <option
                        value="1"
                        {{ old('status_aktif', $daftarTiket->status_aktif) == '1' ? 'selected' : '' }}
                    >
                        Aktif
                    </option>

                    <option
                        value="0"
                        {{ old('status_aktif', $daftarTiket->status_aktif) == '0' ? 'selected' : '' }}
                    >
                        Tidak Aktif
                    </option>
                </select>

                @error('status_aktif')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- TOMBOL SIMPAN DAN BATAL --}}
            <div class="flex flex-wrap items-center gap-3 border-t border-[#E0E8E2] pt-5">

                <button
                    type="submit"
                    class="inline-flex min-h-11 items-center justify-center bg-[#064631] px-6 py-3 text-xs font-bold text-white transition hover:bg-[#237052]"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="{{ route('admin.daftar-tiket.index') }}"
                    class="inline-flex min-h-11 items-center justify-center border border-[#91AD9B] bg-white px-6 py-3 text-xs font-bold text-[#17523B] transition hover:bg-[#EFF5F0]"
                >
                    Batal
                </a>

            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const wahanaCheckboxes = document.querySelectorAll(
            'input[name="wahana_ids[]"]'
        );

        const wahanaCount = document.getElementById('wahana-count');
        const clearWahanaButton = document.getElementById('clear-wahana');

        function updateWahanaCount() {
            const total = document.querySelectorAll(
                'input[name="wahana_ids[]"]:checked'
            ).length;

            wahanaCount.textContent = total + ' wahana dipilih';
        }

        wahanaCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', updateWahanaCount);
        });

        clearWahanaButton.addEventListener('click', function () {
            wahanaCheckboxes.forEach(function (checkbox) {
                checkbox.checked = false;
            });

            updateWahanaCount();
        });

        updateWahanaCount();
    })();
</script>
@endpush