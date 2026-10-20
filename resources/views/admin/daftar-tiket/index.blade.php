<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master Data Tiket</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 32px;
            background: #f5f7fb;
            color: #243047;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 24px;
            border-radius: 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 9px 13px;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-add {
            background: #176b45;
            color: white;
        }

        .btn-edit {
            background: #e7a42b;
            color: #222;
        }

        .btn-delete {
            background: #c93636;
            color: white;
        }

        .alert {
            background: #e1f5e8;
            padding: 12px;
            border-radius: 6px;
            margin: 16px 0;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            background: #edf1f7;
        }

        .actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .empty {
            text-align: center;
            padding: 24px;
            color: #667085;
        }
    </style>
</head>

<body>
    <main class="container">
        <div class="header">
            <div>
                <h1>Master Data Tiket</h1>
                <p>Kelola data tiket dan kategori wisatawan SIMTicketingKRM.</p>
            </div>

            <a href="{{ route('admin.daftar-tiket.create') }}" class="btn btn-add">
                + Tambah Tiket
            </a>
        </div>

        @if (session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Tiket</th>
                        <th>Wahana</th>
                        <th>Kategori Tiket</th>
                        <th>Kategori Wisatawan</th>
                        <th>Kuota</th>
                        <th>Harga Dewasa</th>
                        <th>Harga Anak</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($daftarTiket as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->nama_tiket }}</td>
                            <td>
                                @forelse ($item->daftarWahana as $wahanaItem)
                                    {{ $wahanaItem->nama_wahana }}@if (!$loop->last), @endif
                                @empty
                                    -
                                @endforelse
                            </td>
                            <td>{{ $item->kategori_tiket }}</td>
                            <td>{{ $item->kategori_wisatawan }}</td>
                            <td>{{ $item->kuota }}</td>
                            <td>Rp {{ number_format($item->harga_dewasa, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->harga_anak, 0, ',', '.') }}</td>
                            <td>{{ $item->status_aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}</td>

                            <td>
                                <div class="actions">
                                    <a
                                        href="{{ route('admin.daftar-tiket.edit', $item->id_tiket) }}"
                                        class="btn btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.daftar-tiket.destroy', $item->id_tiket) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus tiket ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-delete">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="empty">
                                Belum ada data tiket.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p>
            <a href="{{ route('admin.dashboard') }}">
                Kembali ke Dashboard Admin
            </a>
        </p>
    </main>
</body>
</html>