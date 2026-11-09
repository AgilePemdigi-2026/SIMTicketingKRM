@extends('layouts.admin')

@section('title', 'Tambah Wahana - Kebun Raya Mangrove')

@section('content')
<div class="max-w-[1400px]">

    {{-- HEADER HALAMAN --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4 border-b border-[#D4DED7] pb-6">
        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>

            <h1 class="text-2xl font-semibold text-[#173D30]">
                Tambah Wahana
            </h1>

            <p class="mt-2 text-sm text-[#74877B]">
                Tambahkan wahana baru beserta informasi operasionalnya.
            </p>
        </div>

        <a href="{{ route('admin.wahana.index') }}"
           class="inline-flex items-center gap-2 border border-[#8CAA98] bg-white px-4 py-2.5 text-xs font-semibold text-[#17523B] transition hover:bg-[#EDF4EE]">
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

    {{-- FORM TAMBAH WAHANA --}}
    <div class="max-w-[1050px] border border-[#DCE5DE] bg-white p-5 sm:p-7">

        <div class="mb-6 border-b border-[#E0E8E2] pb-4">
            <h2 class="text-xs font-bold uppercase tracking-[0.09em] text-[#294A3C]">
                Form Tambah Wahana
            </h2>

            <p class="mt-2 text-xs text-[#89988E]">
                Data operasional wahana wisata.
            </p>
        </div>

        <form
            action="{{ route('admin.wahana.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            @csrf

            {{-- NAMA, DURASI, KAPASITAS --}}
            <div class="grid gap-5 md:grid-cols-3">

                <div>
                    <label for="nama_wahana"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Nama Wahana <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="text"
                        id="nama_wahana"
                        name="nama_wahana"
                        value="{{ old('nama_wahana') }}"
                        maxlength="100"
                        placeholder="Contoh: Sepeda Air"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >

                    @error('nama_wahana')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="durasi"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Durasi (detik) <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        id="durasi"
                        name="durasi"
                        value="{{ old('durasi') }}"
                        min="1"
                        placeholder="Contoh: 1800"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >

                    <p class="mt-1 text-[11px] text-[#91A095]">
                        1800 detik = 30 menit
                    </p>

                    @error('durasi')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kapasitas"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Kapasitas <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="number"
                        id="kapasitas"
                        name="kapasitas"
                        value="{{ old('kapasitas') }}"
                        min="1"
                        placeholder="Contoh: 20"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >

                    @error('kapasitas')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- DESKRIPSI --}}
            <div>
                <label for="deskripsi"
                       class="mb-2 block text-xs font-semibold text-[#415C4D]">
                    Deskripsi Wahana
                </label>

                <textarea
                    id="deskripsi"
                    name="deskripsi"
                    rows="4"
                    maxlength="500"
                    placeholder="Tuliskan informasi dan deskripsi wahana..."
                    class="w-full resize-y border border-[#D3DED5] bg-white px-3 py-3 text-sm leading-6 text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                >{{ old('deskripsi') }}</textarea>

                @error('deskripsi')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- UPLOAD FOTO --}}
            <div>
                <label for="foto"
                       class="mb-2 block text-xs font-semibold text-[#415C4D]">
                    Foto Wahana
                </label>

                <label
                    for="foto"
                    class="flex min-h-[155px] cursor-pointer flex-col items-center justify-center border border-dashed border-[#B7CDBD] bg-[#F5F9F6] px-5 py-7 text-center transition hover:border-[#27704E] hover:bg-[#EFF6F0]"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="30"
                        height="30"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="mb-3 text-[#236247]"
                    >
                        <path d="M12 5v14"/>
                        <path d="M5 12h14"/>
                    </svg>

                    <span class="text-sm font-bold text-[#17523B]">
                        Unggah Foto
                    </span>

                    <span class="mt-2 text-[11px] text-[#87978C]">
                        JPG, JPEG, PNG, atau WebP. Maksimal 2 MB.
                    </span>

                    <span id="nama-file"
                          class="mt-3 text-xs font-medium text-[#397657]">
                    </span>
                </label>

                <input
                    type="file"
                    id="foto"
                    name="foto"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="sr-only"
                >

                @error('foto')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror

                {{-- PREVIEW FOTO --}}
                <div id="preview-container"
                     class="mt-4 hidden border border-[#E1E8E3] bg-[#FAFCFA] p-4">

                    <p class="mb-3 text-xs font-semibold text-[#385948]">
                        Preview Foto
                    </p>

                    <img
                        id="preview"
                        alt="Preview foto wahana yang dipilih"
                        class="max-h-[280px] max-w-full object-contain"
                    >
                </div>
            </div>

            {{-- JAM OPERASIONAL DAN STATUS --}}
            <div class="grid gap-5 md:grid-cols-3">

                <div>
                    <label for="jam_buka"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Jam Buka <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="time"
                        id="jam_buka"
                        name="jam_buka"
                        value="{{ old('jam_buka') }}"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >

                    @error('jam_buka')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_tutup"
                           class="mb-2 block text-xs font-semibold text-[#415C4D]">
                        Jam Tutup <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="time"
                        id="jam_tutup"
                        name="jam_tutup"
                        value="{{ old('jam_tutup') }}"
                        required
                        class="w-full border border-[#D3DED5] bg-white px-3 py-3 text-sm text-[#244737] outline-none transition focus:border-[#22704D] focus:ring-1 focus:ring-[#22704D]"
                    >

                    @error('jam_tutup')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
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
                        <option value="1" {{ old('status_aktif', '1') == '1' ? 'selected' : '' }}>
                            Aktif
                        </option>

                        <option value="0" {{ old('status_aktif') == '0' ? 'selected' : '' }}>
                            Tidak Aktif
                        </option>
                    </select>

                    @error('status_aktif')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            {{-- TOMBOL AKSI --}}
            <div class="flex flex-wrap items-center gap-3 border-t border-[#E0E8E2] pt-5">

                <button
                    type="submit"
                    class="inline-flex min-h-11 items-center justify-center bg-[#064631] px-6 py-3 text-xs font-bold text-white transition hover:bg-[#237052]"
                >
                    Simpan Wahana
                </button>

                <a
                    href="{{ route('admin.wahana.index') }}"
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
    const inputFoto = document.getElementById('foto');
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

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (
            !allowedTypes.includes(file.type) ||
            file.size > 2 * 1024 * 1024
        ) {
            alert(
                'Pilih gambar JPG, PNG, atau WebP dengan ukuran maksimal 2 MB.'
            );

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
</script>
@endpush