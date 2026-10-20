<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Tiket</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #243047;
            margin: 32px;
        }

        .container {
            max-width: 750px;
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
            min-height: 90px;
            resize: vertical;
        }

        .wahana-box {
            margin-top: 8px;
            padding: 14px;
            border: 1px solid #ccd2dc;
            border-radius: 8px;
            background: #fafbfc;
        }

        .wahana-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
        }

        .wahana-item:last-child {
            border-bottom: none;
        }

        .wahana-item input {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
        }

        .wahana-item span {
            font-weight: normal;
        }

        .wahana-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 8px;
        }

        .wahana-count {
            font-size: 13px;
            color: #176b45;
            font-weight: bold;
        }

        .btn-clear {
            border: none;
            background: #fee2e2;
            color: #991b1b;
            padding: 7px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .help {
            display: block;
            margin-top: 8px;
            color: #667085;
            font-size: 13px;
            line-height: 1.5;
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
        }

        @media (max-width: 600px) {
            body {
                margin: 12px;
            }

            .container {
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    <main class="container">
        <h1>Edit Tiket</h1>
        <p>Ubah informasi tiket sesuai kebutuhan.</p>

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.daftar-tiket.update', $daftarTiket->id_tiket) }}" method="POST">
            @csrf
            @method('PUT')

            <label>Wahana yang Termasuk dalam Tiket</label>

            <div class="wahana-info">
                <span class="wahana-count" id="wahana-count">0 wahana dipilih</span>

                <button type="button" class="btn-clear" id="clear-wahana">
                    Batalkan Semua Pilihan
                </button>
            </div>

            <div class="wahana-box">
                @forelse ($wahana as $item)
                    <label class="wahana-item">
                        <input
                            type="checkbox"
                            name="wahana_ids[]"
                            value="{{ $item->id_wahana }}"
                            {{ in_array(
                                $item->id_wahana,
                                old('wahana_ids', $daftarTiket->daftarWahana->pluck('id_wahana')->all())
                            ) ? 'checked' : '' }}
                        >
                        <span>{{ $item->nama_wahana }}</span>
                    </label>
                @empty
                    <p>Belum ada data wahana.</p>
                @endforelse
            </div>

            <small class="help">
                Centang wahana yang ingin dimasukkan. Klik kembali centangnya
                untuk membatalkan pilihan. Tiket masuk saja boleh tanpa wahana.
            </small>

            <label for="nama_tiket">Nama Tiket</label>
            <input
                type="text"
                id="nama_tiket"
                name="nama_tiket"
                value="{{ old('nama_tiket', $daftarTiket->nama_tiket) }}"
                maxlength="100"
                required
            >

            <label for="kategori_tiket">Kategori Tiket</label>
            <select id="kategori_tiket" name="kategori_tiket" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="Tiket Masuk"
                    {{ old('kategori_tiket', $daftarTiket->kategori_tiket) == 'Tiket Masuk' ? 'selected' : '' }}>
                    Tiket Masuk
                </option>
                <option value="Tiket Wahana"
                    {{ old('kategori_tiket', $daftarTiket->kategori_tiket) == 'Tiket Wahana' ? 'selected' : '' }}>
                    Tiket Wahana
                </option>
            </select>

            <label for="kuota">Kuota</label>
            <input
                type="number"
                id="kuota"
                name="kuota"
                value="{{ old('kuota', $daftarTiket->kuota) }}"
                min="0"
                required
            >

            <label for="kategori_wisatawan">Kategori Wisatawan</label>
            <select id="kategori_wisatawan" name="kategori_wisatawan" required>
                <option value="">-- Pilih Kategori Wisatawan --</option>
                <option value="Domestik (WNI)"
                    {{ old('kategori_wisatawan', $daftarTiket->kategori_wisatawan) == 'Domestik (WNI)' ? 'selected' : '' }}>
                    Domestik (WNI)
                </option>
                <option value="Mancanegara (WNA)"
                    {{ old('kategori_wisatawan', $daftarTiket->kategori_wisatawan) == 'Mancanegara (WNA)' ? 'selected' : '' }}>
                    Mancanegara (WNA)
                </option>
            </select>

            <label for="harga_dewasa">Harga Dewasa (Rp)</label>
            <input
                type="number"
                id="harga_dewasa"
                name="harga_dewasa"
                value="{{ old('harga_dewasa', $daftarTiket->harga_dewasa) }}"
                min="0"
                step="0.01"
                required
            >

            <label for="harga_anak">Harga Anak (Rp)</label>
            <input
                type="number"
                id="harga_anak"
                name="harga_anak"
                value="{{ old('harga_anak', $daftarTiket->harga_anak) }}"
                min="0"
                step="0.01"
                required
            >

            <label for="status_aktif">Status</label>
            <select id="status_aktif" name="status_aktif" required>
                <option value="1" {{ old('status_aktif', $daftarTiket->status_aktif) == '1' ? 'selected' : '' }}>
                    Aktif
                </option>
                <option value="0" {{ old('status_aktif', $daftarTiket->status_aktif) == '0' ? 'selected' : '' }}>
                    Tidak Aktif
                </option>
            </select>

            <label for="ketentuan">Ketentuan (opsional)</label>
            <textarea id="ketentuan" name="ketentuan">{{ old('ketentuan', $daftarTiket->ketentuan) }}</textarea>

            <button type="submit" class="btn btn-save">Simpan Perubahan</button>
            <a href="{{ route('admin.daftar-tiket.index') }}" class="btn btn-back">Batal</a>
        </form>
    </main>

    <script>
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
    </script>
</body>
</html>
