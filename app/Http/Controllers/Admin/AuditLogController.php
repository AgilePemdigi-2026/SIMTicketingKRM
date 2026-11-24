<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    private const ACTION_LABELS = [
        'login' => 'Login',
        'logout' => 'Logout',
        'profile_updated' => 'Perubahan Profil',
        'password_updated' => 'Perubahan Password',
        'payment_confirmed' => 'Konfirmasi Cash',
        'qris_payment_confirmed' => 'Konfirmasi QRIS',
        'ticket_checked_in' => 'Check-in E-Tiket',
        'reservation_created' => 'Pembuatan Reservasi',
        'reservation_payment_confirmed' => 'Pembayaran Reservasi',
        'reschedule_requested' => 'Pengajuan Reschedule',
    ];

    /**
     * Menampilkan statistik dan daftar aktivitas sistem.
     */
    public function index(Request $request): View
    {
        $modules = $this->availableModules();
        $filters = $this->validatedFilters($request, $modules);

        $today = CarbonImmutable::now('Asia/Jakarta');

        $start = $today->startOfDay()->utc();
        $end = $today->addDay()->startOfDay()->utc();

        $stats = [
            'total_aktivitas' => AuditLog::query()
                ->count(),

            'aktivitas_hari_ini' => AuditLog::query()
                ->where('created_at', '>=', $start)
                ->where('created_at', '<', $end)
                ->count(),

            'aktivitas_pengguna' => AuditLog::query()
                ->where('actor_role', '!=', 'system')
                ->count(),

            'aktivitas_sistem' => AuditLog::query()
                ->where('actor_role', 'system')
                ->count(),
        ];

        $logs = $this->filteredQuery($filters)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($filters);

        return view('admin.audit-log.index', [
            'stats' => $stats,
            'logs' => $logs,
            'filters' => $filters,
            'modules' => $modules,
        ]);
    }

    /**
     * Menampilkan detail aktivitas dan histori perubahan.
     */
    public function show(AuditLog $auditLog): View
    {
        return view('admin.audit-log.show', [
            'log' => $auditLog,
        ]);
    }

    /**
     * Daftar modul yang tersedia untuk filter.
     */
    private function availableModules(): array
    {
        $defaultModules = [
            'Autentikasi',
            'Pembayaran',
            'Validasi Tiket',
            'Reservasi',
        ];

        $recordedModules = AuditLog::query()
            ->distinct()
            ->pluck('module')
            ->all();

        $modules = array_values(array_unique(
            array_merge($defaultModules, $recordedModules)
        ));

        sort($modules, SORT_NATURAL | SORT_FLAG_CASE);

        return $modules;
    }

    /**
     * Validasi filter untuk halaman dan export.
     */
    private function validatedFilters(
        Request $request,
        array $modules
    ): array {
        $untilRules = [
            'nullable',
            'date_format:Y-m-d',
        ];

        if ($request->filled('from')) {
            $untilRules[] = 'after_or_equal:from';
        }

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'module' => ['nullable', 'string', Rule::in($modules)],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'until' => $untilRules,
        ], [
            'search.string' => 'Kata pencarian harus berupa teks.',
            'search.max' => 'Kata pencarian maksimal 150 karakter.',
            'module.in' => 'Modul aktivitas tidak valid.',
            'from.date_format' => 'Format tanggal awal tidak valid.',
            'until.date_format' => 'Format tanggal akhir tidak valid.',
            'until.after_or_equal' =>
                'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
        ]);

        if (isset($filters['search'])) {
            $filters['search'] = trim($filters['search']);
        }

        return array_filter(
            $filters,
            static fn ($value) => $value !== null && $value !== ''
        );
    }

    /**
     * Query bersama untuk daftar aktivitas dan export.
     */
    private function filteredQuery(array $filters): Builder
    {
        $query = AuditLog::query();

        if (isset($filters['search']) && $filters['search'] !== '') {
            $keyword = $filters['search'];

            // Escape wildcard agar pencarian menggunakan teks literal.
            $escapedKeyword = str_replace(
                ['!', '%', '_'],
                ['!!', '!%', '!_'],
                mb_strtolower($keyword)
            );

            $pattern = '%'.$escapedKeyword.'%';

            $matchingActions = [];

            foreach (self::ACTION_LABELS as $action => $label) {
                if (mb_stripos($label, $keyword) !== false) {
                    $matchingActions[] = $action;
                }
            }

            $query->where(function (Builder $search) use (
                $pattern,
                $matchingActions
            ) {
                $search
                    ->whereRaw(
                        "LOWER(actor_name) LIKE ? ESCAPE '!'",
                        [$pattern]
                    )
                    ->orWhereRaw(
                        "LOWER(actor_role) LIKE ? ESCAPE '!'",
                        [$pattern]
                    )
                    ->orWhereRaw(
                        "LOWER(action) LIKE ? ESCAPE '!'",
                        [$pattern]
                    )
                    ->orWhereRaw(
                        "LOWER(description) LIKE ? ESCAPE '!'",
                        [$pattern]
                    )
                    ->orWhereRaw(
                        "LOWER(module) LIKE ? ESCAPE '!'",
                        [$pattern]
                    );

                if ($matchingActions !== []) {
                    $search->orWhereIn('action', $matchingActions);
                }
            });
        }

        if (! empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        // Batas awal tanggal berdasarkan WIB.
        if (! empty($filters['from'])) {
            $start = CarbonImmutable::parse(
                $filters['from'],
                'Asia/Jakarta'
            )->startOfDay()->utc();

            $query->where('created_at', '>=', $start);
        }

        // Batas akhir eksklusif: awal hari berikutnya dalam WIB.
        if (! empty($filters['until'])) {
            $end = CarbonImmutable::parse(
                $filters['until'],
                'Asia/Jakarta'
            )->addDay()->startOfDay()->utc();

            $query->where('created_at', '<', $end);
        }

        return $query;
    }

    /**
     * Export seluruh aktivitas sesuai filter dalam format CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters(
            $request,
            $this->availableModules()
        );

        $query = $this->filteredQuery($filters);

        $filename = 'audit-log-'
            .CarbonImmutable::now('Asia/Jakarta')->format('Ymd-His')
            .'.csv';

        return response()->streamDownload(
            function () use ($query) {
                $handle = fopen('php://output', 'w');

                try {
                    // UTF-8 BOM dan separator untuk kompatibilitas Excel.
                    fwrite($handle, "\xEF\xBB\xBF");
                    fwrite($handle, "sep=;\r\n");

                    fputcsv($handle, [
                        'ID Audit Log',
                        'Waktu (WIB)',
                        'Nama Pengguna',
                        'Role',
                        'Modul',
                        'Aktivitas',
                        'Kode Aktivitas',
                        'Keterangan',
                        'Jenis Objek',
                        'ID Objek',
                    ], ';', '"', '');

                    foreach (
                        $query->orderByDesc('created_at')
                            ->orderByDesc('id')
                            ->cursor() as $log
                    ) {
                        $actionLabel = self::ACTION_LABELS[$log->action]
                            ?? ucfirst(str_replace('_', ' ', $log->action));

                        $roleLabel = match ($log->actor_role) {
                            'admin' => 'Admin',
                            'petugas' => 'Petugas',
                            'wisatawan' => 'Wisatawan',
                            'system' => 'Sistem',
                            default => ucfirst($log->actor_role),
                        };

                        $row = [
                            $log->id,
                            $log->created_at
                                ?->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i:s'),
                            $log->actor_name,
                            $roleLabel,
                            $log->module,
                            $actionLabel,
                            $log->action,
                            $log->description,
                            $log->subject_type,
                            $log->subject_id,
                        ];

                        fputcsv(
                            $handle,
                            array_map(
                                fn ($value) => $this->safeCsvValue($value),
                                $row
                            ),
                            ';',
                            '"',
                            ''
                        );
                    }
                } finally {
                    fclose($handle);
                }
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /**
     * Mengurangi risiko CSV Formula Injection.
     */
    private function safeCsvValue(mixed $value): string
    {
        $text = (string) ($value ?? '');

        // Cegah karakter kontrol dan awalan formula spreadsheet.
        if (
            preg_match('/^[\t\r\n]/u', $text)
            || preg_match('/^[\s\p{Z}]*[=+\-@]/u', $text)
        ) {
            return "'".$text;
        }

        return $text;
    }
}
