<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Menampilkan statistik dan daftar aktivitas sistem.
     */
    public function index(): View
    {
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

        $logs = AuditLog::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.audit-log.index', [
            'stats' => $stats,
            'logs' => $logs,
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
}
