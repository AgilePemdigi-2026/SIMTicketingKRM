<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Master Fasilitas | SIMTicketingKRM</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 36px;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
        }

        .container {
            max-width: 1450px;
            margin: auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .eyebrow {
            color: #16805d;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        h1 {
            font-size: 30px;
            margin: 0;
            letter-spacing: -0.8px;
        }

        .subtitle {
            margin: 9px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .btn {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 7px;
            padding: 10px 15px;
            border: none;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
        }

        .btn-add {
            background: #147d59;
            color: white;
            box-shadow: 0 4px 10px #147d5920;
        }

        .btn-add:hover {
            background: #106648;
            transform: translateY(-1px);
        }

        .btn-edit {
            background: #fff4d8;
            color: #956200;
        }

        .btn-edit:hover {
            background: #ffe8ae;
        }

        .btn-delete {
            background: #fff0f0;
            color: #c33333;
        }

        .btn-delete:hover {
            background: #ffdddd;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e8edf4;
            border-radius: 14px;
            padding: 21px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 3px 12px #1e293b05;
        }

        .stat-icon {
            width: 47px;
            height: 47px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 12px;
            background: #e4f7ee;
            color: #147d59;
            font-size: 23px;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 25px;
            font-weight: 800;
            color: #172b45;
        }

        .panel {
            background: white;
            border: 1px solid #e8edf4;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px #1e293b06;
        }

        .panel-header {
            padding: 22px 24px;
            border-bottom: 1px solid #edf0f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .panel-header h2 {
            font-size: 17px;
            margin: 0;
        }

        .panel-header p {
            font-size: 13px;
            color: #64748b;
            margin: 5px 0 0;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
        }

        thead {
            background: #f8fafc;
        }

        th {
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        th, td {
            text-align: left;
            padding: 17px 16px;
            border-bottom: 1px solid #edf0f5;
            vertical-align: top;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fbfdff;
        }

        .number {
            color: #94a3b8;
            font-weight: 700;
        }

        .facility {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 200px;
        }

        .photo {
            width: 64px;
            height: 64px;
            flex-shrink: 0;
            object-fit: cover;
            border-radius: 11px;
            background: #f1f5f9;
            border: 1px solid #e5eaf1;
        }

        .photo-placeholder {
            width: 64px;
            height: 64px;
            flex-shrink: 0;
            border-radius: 11px;
            background: #eaf6f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #147d59;
            font-size: 24px;
        }

        .facility-name {
            font-weight: 800;
            font-size: 14px;
            color: #172b45;
            margin-bottom: 5px;
        }

        .facility-note {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            max-width: 220px;
        }

        .capacity {
            display: inline-block;
            background: #f1f5f9;
            padding: 7px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .session-list {
            display: flex;
            flex-direction: column;
            gap: 9px;
            min-width: 205px;
        }

        .session-card {
            border: 1px solid #e8edf4;
            background: #fcfdff;
            border-radius: 10px;
            padding: 11px 12px;
        }

        .session-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 7px;
        }

        .session-name {
            font-size: 13px;
            font-weight: 800;
            color: #243b53;
        }

        .session-duration {
            color: #64748b;
            background: #f1f5f9;
            border-radius: 5px;
            padding: 3px 6px;
            font-size: 10px;
            white-space: nowrap;
        }

        .session-time {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 7px;
        }

        .session-price {
            color: #147d59;
            font-size: 14px;
            font-weight: 800;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-active {
            background: #e3f8ec;
            color: #16804a;
        }

        .status-inactive {
            background: #fff0f0;
            color: #c33333;
        }

        .description {
            font-size: 12px;
            line-height: 1.7;
            color: #64748b;
            max-width: 200px;
            overflow-wrap: anywhere;
        }

        .actions {
            display: flex;
            flex-direction: column;
            gap: 7px;
            align-items: stretch;
            min-width: 80px;
        }

        .actions form {
            margin: 0;
        }

        .empty {
            text-align: center;
            padding: 55px 20px;
        }

        .empty-icon {
            font-size: 38px;
            margin-bottom: 12px;
        }

        .empty h3 {
            margin: 0 0 8px;
            font-size: 17px;
        }

        .empty p {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert {
            border-radius: 10px;
            padding: 14px 17px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert-success {
            background: #e3f8ec;
            color: #166534;
            border: 1px solid #c4efd4;
        }

        .alert-error {
            background: #fff0f0;
            color: #991b1b;
            border: 1px solid #ffd6d6;
        }

        .footer {
            margin-top: 22px;
        }

        .back-link {
            color: #64748b;
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            color: #147d59;
        }

        @media (max-width: 800px) {
            body {
                padding: 20px 12px;
            }

            h1 {
                font-size: 24px;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .stat-card {
                padding: 16px;
            }

            .panel-header {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
<main class="container">

    <header class="topbar">
        <div>
            <div class="eyebrow">SIMTicketingKRM / Admin</div>
            <h1>Master Data Fasilitas</h1>
            <p class="subtitle">
                Kelola fasilitas, jadwal sesi, dan harga sewa dalam satu halaman.
            </p>
        </div>

        <a href="{{ route('admin.fasilitas.create') }}" class="btn btn-add">
            <span>＋</span> Tambah Fasilitas
        </a>
    </header>

    @if (session('success'))
        <div class="alert alert-success">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Data belum dapat diproses:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="stats">
        <div class="stat-card">
            <div class="stat-icon">▦</div>
            <div>
                <div class="stat-label">Total Fasilitas</div>
                <div class="stat-value">{{ $fasilitas->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">✓</div>
            <div>
                <div class="stat-label">Fasilitas Aktif</div>
                <div class="stat-value">
                    {{ $fasilitas->where('status_fasilitas', 1)->count() }}
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">◷</div>
            <div>
                <div class="stat-label">Total Sesi</div>
                <div class="stat-value">
                    {{ $fasilitas->sum(fn ($item) => $item->sesiFasilitas->count()) }}
                </div>
            </div>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <div>
                <h2>Daftar Fasilitas</h2>
                <p>Informasi fasilitas beserta rincian sesi dan harga.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Fasilitas</th>
                        <th>Kapasitas</th>
                        <th>Sesi & Harga</th>
                        <th>Status</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($fasilitas as $item)
                    <tr>
                        <td class="number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>

                        <td>
                            <div class="facility">
                                @if ($item->foto_fasilitas)
                                    <img
                                        class="photo"
                                        src="{{ str_starts_with($item->foto_fasilitas, 'http')
                                            ? $item->foto_fasilitas
                                            : asset('storage/' . $item->foto_fasilitas) }}"
                                        alt="Foto {{ $item->nama_fasilitas }}"
                                    >
                                @else
                                    <div class="photo-placeholder">⌂</div>
                                @endif

                                <div>
                                    <div class="facility-name">
                                        {{ $item->nama_fasilitas }}
                                    </div>

                                    @if ($item->ketentuan_fasilitas)
                                        <div class="facility-note">
                                            {{ $item->ketentuan_fasilitas }}
                                        </div>
                                    @else
                                        <div class="facility-note">Fasilitas wisata</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="capacity">
                                {{ $item->kapasitas_fasilitas }} orang
                            </span>
                        </td>

                        <td>
                            <div class="session-list">
                                @forelse ($item->sesiFasilitas as $sesi)
                                    <div class="session-card">
                                        <div class="session-top">
                                            <span class="session-name">
                                                {{ $sesi->nama_sesi }}
                                            </span>

                                            <span class="session-duration">
                                                {{ $sesi->durasi_menit }} mnt
                                            </span>
                                        </div>

                                        <div class="session-time">
                                            {{ substr((string) $sesi->jam_mulai, 0, 5) }}
                                            – {{ substr((string) $sesi->jam_selesai, 0, 5) }}
                                        </div>

                                        <div class="session-price">
                                            Rp {{ number_format($sesi->harga_sesi, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @empty
                                    <span class="facility-note">Belum ada sesi.</span>
                                @endforelse
                            </div>
                        </td>

                        <td>
                            @if ((int) $item->status_fasilitas === 1)
                                <span class="status status-active">Aktif</span>
                            @else
                                <span class="status status-inactive">Nonaktif</span>
                            @endif
                        </td>

                        <td>
                            <div class="description">
                                {{ $item->deskripsi_fasilitas ?: 'Belum ada deskripsi.' }}
                            </div>
                        </td>

                        <td>
                            <div class="actions">
                                <a
                                    href="{{ route('admin.fasilitas.edit', $item->id_fasilitas) }}"
                                    class="btn btn-edit"
                                >
                                    Edit
                                </a>
                 
                                <form
                                    action="{{ route('admin.fasilitas.toggle-status', $item->id_fasilitas) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin mengubah status fasilitas ini?')"
                                >
                                    @csrf
                                    @method('PATCH')

                                    @if ((int) $item->status_fasilitas === 1)
                                        <button type="submit" class="btn btn-delete">
                                            Nonaktifkan
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-add">
                                            Aktifkan
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty">
                                <div class="empty-icon">⌂</div>
                                <h3>Belum ada fasilitas</h3>
                                <p>Tambahkan fasilitas pertama untuk mulai mengelola sesi dan harga.</p>
                                <a href="{{ route('admin.fasilitas.create') }}" class="btn btn-add">
                                    ＋ Tambah Fasilitas
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <footer class="footer">
        <a href="{{ route('admin.dashboard') }}" class="back-link">
            ← Kembali ke Dashboard Admin
        </a>
    </footer>

</main>
</body>
</html>
