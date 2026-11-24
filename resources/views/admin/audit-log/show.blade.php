@extends('layouts.admin')

@section('title', 'Detail Audit Log - Kebun Raya Mangrove')

@section('content')

@php
    $roleLabel = match ($log->actor_role) {
        'admin' => 'Admin',
        'petugas' => 'Petugas',
        'wisatawan' => 'Wisatawan',
        'system' => 'Sistem',
        default => ucfirst($log->actor_role),
    };

    $actionLabel = match ($log->action) {
        'login' => 'Login',
        'logout' => 'Logout',
        'payment_confirmed' => 'Konfirmasi Cash',
        'qris_payment_confirmed' => 'Konfirmasi QRIS',
        'ticket_checked_in' => 'Check-in E-Tiket',
        'reservation_created' => 'Pembuatan Reservasi',
        'reservation_payment_confirmed' => 'Pembayaran Reservasi',
        'reschedule_requested' => 'Pengajuan Reschedule',
        default => ucfirst(str_replace('_', ' ', $log->action)),
    };

    $fieldLabels = [
        'status' => 'Status',
        'administrative_status' => 'Status Administratif',
        'payment_status' => 'Status Pembayaran',
        'method' => 'Metode Pembayaran',
        'amount' => 'Nominal',
        'total' => 'Total',
        'total_amount' => 'Total Tagihan',
        'visit_date' => 'Tanggal Kunjungan',
        'paid_at' => 'Waktu Pembayaran',
        'used_at' => 'Waktu Check-in',
        'checked_in_by' => 'ID Petugas Validasi',
        'confirmed_by' => 'ID Petugas Konfirmasi',
        'role' => 'Role',
        'sale_channel' => 'Kanal Pembelian',
        'participant_count' => 'Jumlah Peserta',
        'id_sesi' => 'ID Sesi',
        'id_fasilitas' => 'ID Fasilitas',
        'requested_visit_date' => 'Tanggal Pengajuan Baru',
        'requested_id_sesi' => 'ID Sesi yang Diajukan',
    ];

    $formatValue = static function ($key, $value) {
        if ($value === null) {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (in_array($key, ['amount', 'total', 'total_amount'], true) && is_numeric($value)) {
            return 'Rp '.number_format((float) $value, 0, ',', '.');
        }

        if (in_array($key, ['paid_at', 'used_at'], true)) {
            try {
                return \Carbon\CarbonImmutable::parse($value, 'UTC')
                    ->timezone('Asia/Jakarta')
                    ->format('d/m/Y H:i:s').' WIB';
            } catch (\Throwable) {
                return (string) $value;
            }
        }

        if ($key === 'sale_channel') {
            return match ($value) {
                'online' => 'Online',
                'counter' => 'Loket',
                default => (string) $value,
            };
        }

        if ($key === 'method') {
            return strtoupper((string) $value);
        }

        if (! is_scalar($value)) {
            return '[Data tidak ditampilkan]';
        }

        return (string) $value;
    };
@endphp

<div class="mx-auto max-w-[1200px]">

    {{-- HEADER --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-[#D4DED7] pb-6">

        <div>
            <p class="mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-[#668977]">
                Administrasi Kebun Raya Mangrove
            </p>

            <h1 class="text-2xl font-semibold text-[#173D30]">
                Detail Audit Log
            </h1>

            <p class="mt-2 text-sm text-[#74877B]">
                Histori aktivitas #{{ $log->id }}
            </p>
        </div>

        <a
            href="{{ route('admin.audit-log.index', request()->only(['search', 'module', 'from', 'until', 'page'])) }}"
            class="inline-flex items-center justify-center border border-[#527A65] px-5 py-3 text-sm font-semibold text-[#315B46] transition hover:bg-[#E9F0EB]"
        >
            Kembali
        </a>

    </div>

    {{-- INFORMASI AKTIVITAS --}}
    <section class="mb-6 rounded-lg border border-[#DCE5DE] bg-white p-6">

        <h2 class="mb-5 border-b border-[#E5EBE6] pb-3 text-lg font-semibold text-[#173D30]">
            Informasi Aktivitas
        </h2>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

            @foreach ([
                'ID Audit Log' => $log->id,
                'Nama Pengguna' => $log->actor_name,
                'Role' => $roleLabel,
                'Modul' => $log->module,
                'Aktivitas' => $actionLabel,
                'Kode Aktivitas' => $log->action,
                'Waktu Aktivitas' => $log->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s').' WIB',
                'Jenis Objek' => $log->subject_type ?? '-',
                'ID Objek' => $log->subject_id ?? '-',
            ] as $label => $value)

                <div class="min-w-0">
                    <p class="text-xs text-[#819187]">
                        {{ $label }}
                    </p>

                    <p class="mt-2 break-words text-sm font-semibold text-[#173D30]">
                        {{ $value }}
                    </p>
                </div>

            @endforeach

        </div>

        <div class="mt-6 border-t border-[#E5EBE6] pt-5">
            <p class="text-xs text-[#819187]">
                Keterangan Aktivitas
            </p>

            <p class="mt-2 text-sm leading-7 text-[#405A4D]">
                {{ $log->description }}
            </p>
        </div>

    </section>

    {{-- HISTORI PERUBAHAN DATA --}}
    <div class="mb-6">

        <h2 class="mb-4 text-lg font-semibold text-[#173D30]">
            Histori Perubahan Data
        </h2>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">

            @foreach ([
                [
                    'title' => 'Data Sebelum',
                    'subtitle' => 'Kondisi sebelum aktivitas dilakukan',
                    'values' => $log->old_values,
                ],
                [
                    'title' => 'Data Sesudah',
                    'subtitle' => 'Kondisi setelah aktivitas dilakukan',
                    'values' => $log->new_values,
                ],
            ] as $section)

                <section class="overflow-hidden rounded-lg border border-[#DCE5DE] bg-white">

                    <div class="border-b border-[#E2E9E4] bg-[#F5F8F5] px-5 py-4">

                        <h3 class="text-sm font-bold text-[#173D30]">
                            {{ $section['title'] }}
                        </h3>

                        <p class="mt-1 text-xs text-[#819187]">
                            {{ $section['subtitle'] }}
                        </p>

                    </div>

                    <div class="px-5 py-2">

                        @php
                            $visibleValues = array_intersect_key(
                                $section['values'] ?? [],
                                $fieldLabels
                            );
                        @endphp

                        @forelse ($visibleValues as $key => $value)

                            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-[#E5EBE6] py-4 last:border-b-0">

                                <p class="text-xs text-[#74877B]">
                                    {{ $fieldLabels[$key] }}
                                </p>

                                <p class="max-w-full break-words text-sm font-semibold text-[#173D30]">
                                    {{ $formatValue($key, $value) }}
                                </p>

                            </div>

                        @empty

                            <div class="py-8 text-center text-xs text-[#819187]">
                                Tidak ada histori perubahan data.
                            </div>

                        @endforelse

                    </div>

                </section>

            @endforeach

        </div>

    </div>

    {{-- INFORMASI READ ONLY --}}
    <div class="border border-[#D5E5DA] bg-[#EFF6F0] px-5 py-4 text-xs leading-6 text-[#355D47]">
        Catatan Audit Log bersifat read-only.
        Data pada halaman ini merupakan histori aktivitas yang disimpan oleh sistem.
    </div>

</div>

@endsection
