<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Wahana</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #243047;
            margin: 32px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 24px;
            border-radius: 12px;
        }

        label {
            display: block;
            margin-top: 16px;
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
            min-height: 100px;
            resize: vertical;
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

        .btn-back {
            background: #e5e7eb;
            color: #243047;
        }

        .error {
            color: #b42318;
            font-size: 13px;
            margin-top: 4px;
        }

        #preview-container {
            display: none;
            margin-top: 12px;
        }

        #preview {
            display: block;
            max-width: 100%;
            max-height: 280px;
            border-radius: 8px;
            object-fit: contain;
        }

        .hint {
            font-size: 13px;
            color: #667085;
            margin-top: 6px;
        }
    </style>
</head>

<body>
    <main class="container">
        <h1>Tambah Wahana</h1>
        <p>Isi formulir berikut untuk menambahkan data wahana.</p>

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
            action="{{ route('admin.wahana.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf

            <label for="nama_wahana">Nama Wahana</label>
            <input
                type="text"
                id="nama_wahana"
                name="nama_wahana"
                value="{{ old('nama_wahana') }}"
                maxlength="100"
                required
            >

            <label for="durasi">Durasi (detik)</label>
            <input
                type="number"
                id="durasi"
                name="durasi"
                value="{{ old('durasi') }}"
                min="1"
                placeholder="Contoh: 900 = 15 menit"
                required
            >

            <label for="jam_buka">Jam Buka</label>
            <input
                type="time"
                id="jam_buka"
                name="jam_buka"
                value="{{ old('jam_buka') }}"
                required
            >

            <label for="jam_tutup">Jam Tutup</label>
            <input
                type="time"
                id="jam_tutup"
                name="jam_tutup"
                value="{{ old('jam_tutup') }}"
                required
            >

            <label for="kapasitas">Kapasitas</label>
            <input
                type="number"
                id="kapasitas"
                name="kapasitas"
                value="{{ old('kapasitas') }}"
                min="1"
                required
            >

            <label for="status_aktif">Status</label>
            <select id="status_aktif" name="status_aktif" required>
                <option value="1" {{ old('status_aktif', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ old('status_aktif') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
            </select>

            <label for="deskripsi">Deskripsi (opsional)</label>
            <textarea
                id="deskripsi"
                name="deskripsi"
                maxlength="500"
            >{{ old('deskripsi') }}</textarea>

            <label for="foto">Foto Wahana (opsional)</label>
            <input
                type="file"
                id="foto"
                name="foto"
                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            >

            <p class="hint">Format JPG, JPEG, PNG, atau WebP. Maksimal 2 MB.</p>

            <div id="preview-container">
                <p>Preview foto:</p>
                <img id="preview" alt="Preview foto wahana">
            </div>

            <button type="submit" class="btn btn-save">Simpan Wahana</button>
            <a href="{{ route('admin.wahana.index') }}" class="btn btn-back">Batal</a>
        </form>
    </main>

    <script>
        const inputFoto = document.getElementById('foto');
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

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type) || file.size > 2 * 1024 * 1024) {
                alert('Pilih gambar JPG, PNG, atau WebP dengan ukuran maksimal 2 MB.');
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
