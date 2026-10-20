<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Fasilitas</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #243047;
            margin: 32px;
        }

        .container {
            max-width: 850px;
            margin: auto;
            background: white;
            padding: 24px;
            border-radius: 12px;
        }

        label {
            display: block;
            margin-top: 14px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #ccd2dc;
            border-radius: 6px;
            font-size: 14px;
        }

        textarea {
            min-height: 80px;
            resize: vertical;
        }

        .sesi {
            border: 1px solid #d5dbe5;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }

        .sesi h3 {
            margin-top: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .btn {
            display: inline-block;
            padding: 10px 14px;
            margin-top: 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-save {
            background: #176b45;
            color: white;
        }

        .btn-back, .btn-remove {
            background: #e5e7eb;
            color: #243047;
        }

        .btn-add {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .error {
            color: #b42318;
        }

        .hint {
            font-size: 13px;
            color: #667085;
        }

        .foto-preview {
            max-width: 100%;
            max-height: 280px;
            object-fit: contain;
            border-radius: 8px;
            margin-top: 10px;
        }

        @media (max-width: 600px) {
            body {
                margin: 12px;
            }

            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<main class="container">
    <h1>Edit Fasilitas</h1>
    <p>Ubah data fasilitas dan atur pilihan sesi sewanya.</p>

    @if ($errors->any())
        <div class="error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.fasilitas.update', $fasilitas->id_fasilitas) }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <h2>Data Fasilitas</h2>

        <label for="nama_fasilitas">Nama Fasilitas</label>
        <input
            type="text"
            id="nama_fasilitas"
            name="nama_fasilitas"
            value="{{ old('nama_fasilitas', $fasilitas->nama_fasilitas) }}"
            maxlength="100"
            required
        >

        <label for="kapasitas_fasilitas">Kapasitas Fasilitas</label>
        <input
            type="number"
            id="kapasitas_fasilitas"
            name="kapasitas_fasilitas"
            value="{{ old('kapasitas_fasilitas', $fasilitas->kapasitas_fasilitas) }}"
            min="1"
            required
        >

        <label for="status_fasilitas">Status Fasilitas</label>
        <select id="status_fasilitas" name="status_fasilitas" required>
            <option value="1"
                {{ (string) old('status_fasilitas', $fasilitas->status_fasilitas) === '1' ? 'selected' : '' }}>
                Tersedia
            </option>
            <option value="0"
                {{ (string) old('status_fasilitas', $fasilitas->status_fasilitas) === '0' ? 'selected' : '' }}>
                Tidak Tersedia
            </option>
        </select>

        <label for="ketentuan_fasilitas">Ketentuan Fasilitas (opsional)</label>
        <textarea
            id="ketentuan_fasilitas"
            name="ketentuan_fasilitas"
        >{{ old('ketentuan_fasilitas', $fasilitas->ketentuan_fasilitas) }}</textarea>

        <label for="deskripsi_fasilitas">Deskripsi Fasilitas (opsional)</label>
        <textarea
            id="deskripsi_fasilitas"
            name="deskripsi_fasilitas"
        >{{ old('deskripsi_fasilitas', $fasilitas->deskripsi_fasilitas) }}</textarea>

        <label for="foto_fasilitas">Foto Fasilitas (opsional)</label>

        @if ($fasilitas->foto_fasilitas)
            <div>
                <p>Foto saat ini:</p>
                <img
                    src="{{ str_starts_with($fasilitas->foto_fasilitas, 'http')
                        ? $fasilitas->foto_fasilitas
                        : asset('storage/' . $fasilitas->foto_fasilitas) }}"
                    alt="Foto {{ $fasilitas->nama_fasilitas }}"
                    class="foto-preview"
                    id="foto-lama"
                >
            </div>
        @endif

        <input
            type="file"
            id="foto_fasilitas"
            name="foto_fasilitas"
            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
        >

        <p class="hint">Pilih foto baru jika ingin mengganti foto lama. Maksimal 2 MB.</p>

        <div id="preview-container" style="display:none;">
            <p>Preview foto baru:</p>
            <img id="preview" class="foto-preview" alt="Preview foto fasilitas">
        </div>

        <hr style="margin-top:28px;">

        <h2>Pilihan Sesi</h2>
        <p class="hint">
            Jam selesai harus setelah jam mulai pada hari yang sama.
            Durasi harus sesuai dengan selisih waktu tersebut.
        </p>

        <div id="daftar-sesi">
            @php
                $sesiLama = old('sesi', $fasilitas->sesiFasilitas
                    ->map(fn ($item) => [
                        'id_sesi' => $item->id_sesi,
                        'nama_sesi' => $item->nama_sesi,
                        'jam_mulai' => substr((string) $item->jam_mulai, 0, 5),
                        'jam_selesai' => substr((string) $item->jam_selesai, 0, 5),
                        'durasi_menit' => $item->durasi_menit,
                        'harga_sesi' => $item->harga_sesi,
                    ])->values()->all());

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

            @foreach ($sesiLama as $i => $sesi)
                <section class="sesi">
                    <h3>Sesi <span class="nomor-sesi">{{ $i + 1 }}</span></h3>

                    <input
                        type="hidden"
                        name="sesi[{{ $i }}][id_sesi]"
                        value="{{ $sesi['id_sesi'] ?? '' }}"
                    >

                    <label>Nama Sesi</label>
                    <input
                        type="text"
                        name="sesi[{{ $i }}][nama_sesi]"
                        value="{{ $sesi['nama_sesi'] ?? '' }}"
                        maxlength="100"
                        required
                    >

                    <div class="grid">
                        <div>
                            <label>Jam Mulai</label>
                            <input
                                type="time"
                                name="sesi[{{ $i }}][jam_mulai]"
                                value="{{ $sesi['jam_mulai'] ?? '' }}"
                                required
                            >
                        </div>

                        <div>
                            <label>Jam Selesai</label>
                            <input
                                type="time"
                                name="sesi[{{ $i }}][jam_selesai]"
                                value="{{ $sesi['jam_selesai'] ?? '' }}"
                                required
                            >
                        </div>

                        <div>
                            <label>Durasi (menit)</label>
                            <input
                                type="number"
                                name="sesi[{{ $i }}][durasi_menit]"
                                value="{{ $sesi['durasi_menit'] ?? '' }}"
                                min="1"
                                required
                            >
                        </div>

                        <div>
                            <label>Harga Sesi (Rp)</label>
                            <input
                                type="number"
                                name="sesi[{{ $i }}][harga_sesi]"
                                value="{{ $sesi['harga_sesi'] ?? '' }}"
                                min="0"
                                step="0.01"
                                required
                            >
                        </div>
                    </div>

                    <button type="button" class="btn btn-remove hapus-sesi">
                        Hapus Sesi
                    </button>
                </section>
            @endforeach
        </div>

        <button type="button" id="tambah-sesi" class="btn btn-add">
            + Tambah Sesi
        </button>

        <div>
            <button type="submit" class="btn btn-save">Simpan Perubahan</button>
            <a href="{{ route('admin.fasilitas.index') }}" class="btn btn-back">Batal</a>
        </div>
    </form>
</main>

<script>
    const daftarSesi = document.getElementById('daftar-sesi');
    const tombolTambah = document.getElementById('tambah-sesi');

    let indeksSesi = {{ count($sesiLama) }};

    function perbaruiNomorSesi() {
        daftarSesi.querySelectorAll('.sesi').forEach((item, index) => {
            item.querySelector('.nomor-sesi').textContent = index + 1;
        });
    }

    tombolTambah.addEventListener('click', function () {
        const index = indeksSesi++;

        const sesi = document.createElement('section');
        sesi.className = 'sesi';

        sesi.innerHTML = `
            <h3>Sesi <span class="nomor-sesi"></span></h3>
            <input type="hidden" name="sesi[${index}][id_sesi]" value="">

            <label>Nama Sesi</label>
            <input type="text" name="sesi[${index}][nama_sesi]"
                   maxlength="100" required>

            <div class="grid">
                <div>
                    <label>Jam Mulai</label>
                    <input type="time" name="sesi[${index}][jam_mulai]" required>
                </div>
                <div>
                    <label>Jam Selesai</label>
                    <input type="time" name="sesi[${index}][jam_selesai]" required>
                </div>
                <div>
                    <label>Durasi (menit)</label>
                    <input type="number" name="sesi[${index}][durasi_menit]"
                           min="1" required>
                </div>
                <div>
                    <label>Harga Sesi (Rp)</label>
                    <input type="number" name="sesi[${index}][harga_sesi]"
                           min="0" step="0.01" required>
                </div>
            </div>

            <button type="button" class="btn btn-remove hapus-sesi">
                Hapus Sesi
            </button>
        `;

        daftarSesi.appendChild(sesi);
        perbaruiNomorSesi();
    });

    daftarSesi.addEventListener('click', function (event) {
        if (!event.target.classList.contains('hapus-sesi')) {
            return;
        }

        if (daftarSesi.querySelectorAll('.sesi').length <= 1) {
            alert('Minimal harus ada satu sesi fasilitas.');
            return;
        }

        event.target.closest('.sesi').remove();
        perbaruiNomorSesi();
    });

    const inputFoto = document.getElementById('foto_fasilitas');
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('preview-container');
    let previewUrl = null;

    inputFoto.addEventListener('change', function () {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }

        const file = this.files[0];

        if (!file) {
            preview.removeAttribute('src');
            previewContainer.style.display = 'none';
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!allowedTypes.includes(file.type) || file.size > 2 * 1024 * 1024) {
            alert('Pilih gambar JPG, PNG, atau WebP maksimal 2 MB.');
            this.value = '';
            preview.removeAttribute('src');
            previewContainer.style.display = 'none';
            return;
        }

        previewUrl = URL.createObjectURL(file);
        preview.src = previewUrl;
        previewContainer.style.display = 'block';
    });
</script>
</body>
</html>
