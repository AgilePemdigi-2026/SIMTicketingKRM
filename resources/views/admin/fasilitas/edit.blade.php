@extends('layouts.admin')

@section('title', 'Edit Fasilitas - Kebun Raya Mangrove')

@section('content')
@php
    $inputClass = 'w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]';
    $labelClass = 'mb-2 block text-xs font-semibold text-[#415C4D]';

    $sesiLama = old(
        'sesi',
        $fasilitas->sesiFasilitas
            ->map(fn ($item) => [
                'id_sesi' => $item->id_sesi,
                'nama_sesi' => $item->nama_sesi,
                'jam_mulai' => substr((string) $item->jam_mulai, 0, 5),
                'jam_selesai' => substr((string) $item->jam_selesai, 0, 5),
                'durasi_menit' => $item->durasi_menit,
                'harga_sesi' => $item->harga_sesi,
            ])
            ->values()
            ->all()
    );

    if (count($sesiLama) === 0) {
        $sesiLama = [[
            'id_sesi' => '',
            'nama_sesi' => '',
            'jam_mulai' => '',
            'jam_selesai' => '',
            'durasi_menit' => '',
            'harga_sesi' => '',
        ]];
    }
@endphp

<div class="max-w-[1400px]">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4 border-b border-[#D4DED7] pb-6">
        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>

            <h1 class="text-2xl font-semibold text-[#173D30]">
                Edit Fasilitas — {{ $fasilitas->nama_fasilitas }}
            </h1>

            <p class="mt-2 text-sm text-[#74877B]">
                Perbarui informasi fasilitas, foto, jadwal sesi, dan harga sewa.
            </p>
        </div>

        <a
            href="{{ route('admin.fasilitas.index') }}"
            class="inline-flex items-center border border-[#8CAA98] bg-white px-4 py-2.5 text-xs font-semibold text-[#17523B] transition hover:bg-[#EDF4EE]"
        >
            ← Kembali
        </a>
    </div>

    {{-- VALIDASI ERROR --}}
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

    {{-- FORM EDIT FASILITAS --}}
    <div class="max-w-[1120px] border border-[#DCE5DE] bg-white p-5 sm:p-7">

        <div class="mb-6 border-b border-[#E0E8E2] pb-4">
            <h2 class="text-xs font-bold uppercase tracking-[0.09em] text-[#294A3C]">
                Form Edit Fasilitas
            </h2>

            <p class="mt-2 text-xs text-[#89988E]">
                Perbarui data fasilitas yang sudah terdaftar.
            </p>
        </div>

        <form
            action="{{ route('admin.fasilitas.update', $fasilitas->id_fasilitas) }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            @csrf
            @method('PUT')

            {{-- NAMA DAN KAPASITAS --}}
            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label for="nama_fasilitas" class="{{ $labelClass }}">
                        Nama Fasilitas <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="nama_fasilitas"
                        name="nama_fasilitas"
                        value="{{ old('nama_fasilitas', $fasilitas->nama_fasilitas) }}"
                        maxlength="100"
                        required
                        class="{{ $inputClass }}"
                    >

                    @error('nama_fasilitas')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kapasitas_fasilitas" class="{{ $labelClass }}">
                        Kapasitas Fasilitas <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        id="kapasitas_fasilitas"
                        name="kapasitas_fasilitas"
                        value="{{ old('kapasitas_fasilitas', $fasilitas->kapasitas_fasilitas) }}"
                        min="1"
                        required
                        class="{{ $inputClass }}"
                    >

                    @error('kapasitas_fasilitas')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- DESKRIPSI --}}
            <div>
                <label for="deskripsi_fasilitas" class="{{ $labelClass }}">
                    Deskripsi Fasilitas
                </label>

                <textarea
                    id="deskripsi_fasilitas"
                    name="deskripsi_fasilitas"
                    rows="4"
                    placeholder="Tuliskan informasi mengenai fasilitas..."
                    class="{{ $inputClass }} resize-y leading-6"
                >{{ old('deskripsi_fasilitas', $fasilitas->deskripsi_fasilitas) }}</textarea>

                @error('deskripsi_fasilitas')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- FOTO FASILITAS --}}
            <div>
                <label for="foto_fasilitas" class="{{ $labelClass }}">
                    Foto Fasilitas
                </label>

                {{-- FOTO YANG SUDAH TERSIMPAN --}}
                @if ($fasilitas->foto_fasilitas)
                    <div class="mb-4 border border-[#E0E8E2] bg-[#FAFCFA] p-4">

                        <p class="mb-3 text-xs font-bold text-[#385948]">
                            Foto Saat Ini
                        </p>

                        <img
                            src="{{ str_starts_with($fasilitas->foto_fasilitas, 'http') ? $fasilitas->foto_fasilitas : asset('storage/' . $fasilitas->foto_fasilitas) }}"
                            alt="Foto {{ $fasilitas->nama_fasilitas }}"
                            class="max-h-[240px] max-w-full object-contain"
                        >

                        <p class="mt-3 text-[11px] text-[#84978A]">
                            Foto ini tetap digunakan jika tidak memilih foto baru.
                        </p>

                    </div>
                @else
                    <p class="mb-4 text-xs text-[#84978A]">
                        Belum ada foto yang tersimpan.
                    </p>
                @endif

                {{-- AREA UPLOAD FOTO BARU --}}
                <label
                    for="foto_fasilitas"
                    class="flex min-h-[155px] cursor-pointer flex-col items-center justify-center border border-dashed border-[#B7CDBD] bg-[#F5F9F6] px-5 py-7 text-center transition hover:border-[#27704E] hover:bg-[#EFF6F0]"
                >
                    <span class="text-3xl font-light text-[#236247]">
                        +
                    </span>

                    <span class="mt-2 text-sm font-bold text-[#17523B]">
                        Pilih Foto Baru
                    </span>

                    <span class="mt-2 text-[11px] text-[#87978C]">
                        JPG, JPEG, PNG, atau WebP. Maksimal 2 MB.
                    </span>

                    <span
                        id="nama-file"
                        class="mt-3 text-xs font-medium text-[#397657]"
                    ></span>
                </label>

                <input
                    type="file"
                    id="foto_fasilitas"
                    name="foto_fasilitas"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="sr-only"
                >

                @error('foto_fasilitas')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror

                {{-- PREVIEW FOTO BARU --}}
                <div
                    id="preview-container"
                    class="mt-4 hidden border border-[#E1E8E3] bg-[#FAFCFA] p-4"
                >
                    <p class="mb-3 text-xs font-semibold text-[#385948]">
                        Preview Foto Baru
                    </p>

                    <img
                        id="preview"
                        alt="Preview foto fasilitas yang dipilih"
                        class="max-h-[280px] max-w-full object-contain"
                    >
                </div>
            </div>

            {{-- KETENTUAN --}}
            <div>
                <label for="ketentuan_fasilitas" class="{{ $labelClass }}">
                    Ketentuan Penggunaan
                </label>

                <textarea
                    id="ketentuan_fasilitas"
                    name="ketentuan_fasilitas"
                    rows="3"
                    placeholder="Tuliskan ketentuan penggunaan fasilitas..."
                    class="{{ $inputClass }} resize-y leading-6"
                >{{ old('ketentuan_fasilitas', $fasilitas->ketentuan_fasilitas) }}</textarea>

                @error('ketentuan_fasilitas')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- STATUS --}}
            <div class="max-w-[350px]">
                <label for="status_fasilitas" class="{{ $labelClass }}">
                    Status Fasilitas <span class="text-red-500">*</span>
                </label>

                <select
                    id="status_fasilitas"
                    name="status_fasilitas"
                    required
                    class="{{ $inputClass }}"
                >
                    <option
                        value="1"
                        {{ (string) old('status_fasilitas', $fasilitas->status_fasilitas) === '1' ? 'selected' : '' }}
                    >
                        Tersedia
                    </option>

                    <option
                        value="0"
                        {{ (string) old('status_fasilitas', $fasilitas->status_fasilitas) === '0' ? 'selected' : '' }}
                    >
                        Tidak Tersedia
                    </option>
                </select>

                @error('status_fasilitas')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- PILIHAN SESI --}}
            <div class="border-t border-[#E0E8E2] pt-6">

                <div class="mb-5">
                    <h2 class="text-sm font-bold uppercase tracking-[0.08em] text-[#173D30]">
                        Pilihan Sesi
                    </h2>

                    <p class="mt-2 text-xs leading-6 text-[#819187]">
                        Jam selesai harus setelah jam mulai pada hari yang sama.
                        Durasi harus sesuai dengan selisih waktu.
                        Minimal satu sesi wajib tersedia.
                    </p>
                </div>

                <div id="daftar-sesi" class="space-y-4">

                    @foreach ($sesiLama as $i => $sesi)
                        <section class="sesi border border-[#DCE5DE] bg-[#FAFCFA] p-4 sm:p-5">

                            <div class="mb-4 flex items-center justify-between gap-3 border-b border-[#E0E8E2] pb-3">

                                <h3 class="text-sm font-bold text-[#173D30]">
                                    Sesi <span class="nomor-sesi">{{ $loop->iteration }}</span>
                                </h3>

                                <button
                                    type="button"
                                    class="hapus-sesi border border-[#E9CCBC] bg-white px-3 py-2 text-xs font-semibold text-[#B65B36] hover:bg-[#FFF2ED]"
                                >
                                    Hapus Sesi
                                </button>
                            </div>

                            {{-- ID SESI LAMA: JANGAN DIHAPUS --}}
                            <input
                                type="hidden"
                                name="sesi[{{ $i }}][id_sesi]"
                                value="{{ $sesi['id_sesi'] ?? '' }}"
                            >

                            <div class="mb-4">
                                <label class="{{ $labelClass }}">
                                    Nama Sesi <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="sesi[{{ $i }}][nama_sesi]"
                                    value="{{ $sesi['nama_sesi'] ?? '' }}"
                                    maxlength="100"
                                    required
                                    class="{{ $inputClass }}"
                                >
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">

                                <div>
                                    <label class="{{ $labelClass }}">
                                        Jam Mulai *
                                    </label>

                                    <input
                                        type="time"
                                        name="sesi[{{ $i }}][jam_mulai]"
                                        value="{{ $sesi['jam_mulai'] ?? '' }}"
                                        required
                                        class="{{ $inputClass }}"
                                    >
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">
                                        Jam Selesai *
                                    </label>

                                    <input
                                        type="time"
                                        name="sesi[{{ $i }}][jam_selesai]"
                                        value="{{ $sesi['jam_selesai'] ?? '' }}"
                                        required
                                        class="{{ $inputClass }}"
                                    >
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">
                                        Durasi (menit) *
                                    </label>

                                    <input
                                        type="number"
                                        name="sesi[{{ $i }}][durasi_menit]"
                                        value="{{ $sesi['durasi_menit'] ?? '' }}"
                                        min="1"
                                        required
                                        class="{{ $inputClass }}"
                                    >
                                </div>

                                <div>
                                    <label class="{{ $labelClass }}">
                                        Harga Sesi (Rp) *
                                    </label>

                                    <input
                                        type="number"
                                        name="sesi[{{ $i }}][harga_sesi]"
                                        value="{{ $sesi['harga_sesi'] ?? '' }}"
                                        min="0"
                                        step="0.01"
                                        required
                                        class="{{ $inputClass }}"
                                    >
                                </div>

                            </div>
                        </section>
                    @endforeach

                </div>

                <button
                    type="button"
                    id="tambah-sesi"
                    class="mt-4 border border-[#91AD9B] bg-[#EFF6F0] px-4 py-3 text-xs font-bold text-[#17523B] hover:bg-[#E1EFE4]"
                >
                    + Tambah Sesi
                </button>
            </div>

            {{-- TOMBOL AKSI --}}
            <div class="flex flex-wrap gap-3 border-t border-[#E0E8E2] pt-5">

                <button
                    type="submit"
                    class="inline-flex min-h-11 items-center justify-center bg-[#064631] px-6 py-3 text-xs font-bold text-white hover:bg-[#237052]"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="{{ route('admin.fasilitas.index') }}"
                    class="inline-flex min-h-11 items-center justify-center border border-[#91AD9B] bg-white px-6 py-3 text-xs font-bold text-[#17523B] hover:bg-[#EFF5F0]"
                >
                    Batal
                </a>

            </div>

        </form>
    </div>
</div>

{{-- TEMPLATE UNTUK SESI BARU --}}
<template id="template-sesi">
    <section class="sesi border border-[#DCE5DE] bg-[#FAFCFA] p-4 sm:p-5">

        <div class="mb-4 flex items-center justify-between gap-3 border-b border-[#E0E8E2] pb-3">
            <h3 class="text-sm font-bold text-[#173D30]">
                Sesi <span class="nomor-sesi"></span>
            </h3>

            <button
                type="button"
                class="hapus-sesi border border-[#E9CCBC] bg-white px-3 py-2 text-xs font-semibold text-[#B65B36]"
            >
                Hapus Sesi
            </button>
        </div>

        <input
            type="hidden"
            name="sesi[__INDEX__][id_sesi]"
            value=""
        >

        <div class="mb-4">
            <label class="{{ $labelClass }}">Nama Sesi *</label>
            <input
                type="text"
                name="sesi[__INDEX__][nama_sesi]"
                maxlength="100"
                required
                class="{{ $inputClass }}"
            >
        </div>

        <div class="grid gap-4 sm:grid-cols-2">

            <div>
                <label class="{{ $labelClass }}">Jam Mulai *</label>
                <input
                    type="time"
                    name="sesi[__INDEX__][jam_mulai]"
                    required
                    class="{{ $inputClass }}"
                >
            </div>

            <div>
                <label class="{{ $labelClass }}">Jam Selesai *</label>
                <input
                    type="time"
                    name="sesi[__INDEX__][jam_selesai]"
                    required
                    class="{{ $inputClass }}"
                >
            </div>

            <div>
                <label class="{{ $labelClass }}">Durasi (menit) *</label>
                <input
                    type="number"
                    name="sesi[__INDEX__][durasi_menit]"
                    min="1"
                    required
                    class="{{ $inputClass }}"
                >
            </div>

            <div>
                <label class="{{ $labelClass }}">Harga Sesi (Rp) *</label>
                <input
                    type="number"
                    name="sesi[__INDEX__][harga_sesi]"
                    min="0"
                    step="0.01"
                    required
                    class="{{ $inputClass }}"
                >
            </div>

        </div>
    </section>
</template>
@endsection

@push('scripts')
<script>
(function () {
    const daftarSesi = document.getElementById('daftar-sesi');
    const tombolTambah = document.getElementById('tambah-sesi');
    const templateSesi = document.getElementById('template-sesi');

    let counterSesi = 0;

    function perbaruiNomorSesi() {
        daftarSesi.querySelectorAll('.sesi').forEach((item, index) => {
            item.querySelector('.nomor-sesi').textContent = index + 1;
        });
    }

    tombolTambah.addEventListener('click', function () {
        const index = 'baru_' + Date.now() + '_' + counterSesi++;

        const html = templateSesi.innerHTML.replaceAll('__INDEX__', index);

        daftarSesi.insertAdjacentHTML('beforeend', html);

        perbaruiNomorSesi();
    });

    daftarSesi.addEventListener('click', function (event) {
        const tombolHapus = event.target.closest('.hapus-sesi');

        if (!tombolHapus) {
            return;
        }

        if (daftarSesi.querySelectorAll('.sesi').length <= 1) {
            alert('Minimal harus ada satu sesi fasilitas.');
            return;
        }

        tombolHapus.closest('.sesi').remove();
        perbaruiNomorSesi();
    });

    const inputFoto = document.getElementById('foto_fasilitas');
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('preview-container');
    const namaFile = document.getElementById('nama-file');

    let previewUrl = null;

    inputFoto.addEventListener('change', function () {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        const file = this.files[0];

        if (!file) {
            preview.removeAttribute('src');
            previewContainer.classList.add('hidden');
            namaFile.textContent = '';
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!allowedTypes.includes(file.type) || file.size > 2 * 1024 * 1024) {
            alert('Pilih gambar JPG, PNG, atau WebP maksimal 2 MB.');

            this.value = '';
            preview.removeAttribute('src');
            previewContainer.classList.add('hidden');
            namaFile.textContent = '';
            return;
        }

        namaFile.textContent = file.name;
        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        previewContainer.classList.remove('hidden');
    });

    perbaruiNomorSesi();
})();
</script>
@endpush