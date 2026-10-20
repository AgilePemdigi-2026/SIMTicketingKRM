<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master Data Wahana</title>

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
            border: 0;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-add { background: #176b45; color: white; }
        .btn-edit { background: #e7a42b; color: #222; }
        .btn-delete { background: #c93636; color: white; }

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
            vertical-align: middle;
        }

        th { background: #edf1f7; }

        .foto-wahana {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            display: block;
        }

        .no-foto {
            width: 80px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #edf1f7;
            color: #667085;
            border-radius: 6px;
            font-size: 12px;
            text-align: center;
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
                <h1>Master Data Wahana</h1>
                <p>Kelola data wahana SIMTicketingKRM.</p>
            </div>

            <a href="{{ route('admin.wahana.create') }}" class="btn btn-add">
                + Tambah Wahana
            </a>
        </div>

        @if (session('success'))
            <div class="alert">
                {{ session('success') }}
            </div>
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
                        <th>Foto</th>
                        <th>Nama Wahana</th>
                        <th>Durasi (detik)</th>
                        <th>Jam Operasional</th>
                        <th>Kapasitas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($wahana as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                @if ($item->foto)
                                    <img
                                        src="{{ str_starts_with($item->foto, 'http') ? $item->foto : asset('storage/' . $item->foto) }}"
                                        alt="Foto {{ $item->nama_wahana }}"
                                        class="foto-wahana"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >
                                    <div class="no-foto" style="display: none;">
                                        Foto tidak tersedia
                                    </div>
                                @else
                                    <div class="no-foto">
                                        Belum ada foto
                                    </div>
                                @endif
                            </td>

                            <td>{{ $item->nama_wahana }}</td>

                            <td>{{ $item->durasi }}</td>

                            <td>
                                {{ \Carbon\Carbon::parse($item->jam_buka)->format('H:i') }}
                                -
                                {{ \Carbon\Carbon::parse($item->jam_tutup)->format('H:i') }}
                            </td>

                            <td>{{ $item->kapasitas }}</td>

                            <td>
                                {{ $item->status_aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}
                            </td>

                            <td>
                                <div class="actions">
                                    <a
                                        href="{{ route('admin.wahana.edit', $item->id_wahana) }}"
                                        class="btn btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.wahana.destroy', $item->id_wahana) }}"
                                        method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus wahana ini?')"
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
                            <td colspan="8" class="empty">
                                Belum ada data wahana.
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
