<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AdminNotificationService
{
    public const ACTIONS = [
        'payment_confirmed' => 'Pembayaran Cash berhasil',
        'qris_payment_confirmed' => 'Pembayaran QRIS berhasil',
        'ticket_checked_in' => 'Check-in pengunjung berhasil',
        'reservation_created' => 'Reservasi fasilitas baru',
        'reservation_payment_confirmed' => 'Pembayaran reservasi berhasil',
        'reschedule_requested' => 'Pengajuan reschedule baru',
    ];

    public function query(User $user): Builder
    {
        abort_unless($user->role === 'admin', 403);

        return AuditLog::query()
            ->whereIn('audit_logs.action', array_keys(self::ACTIONS))
            ->leftJoin('admin_notification_reads as reads', function ($join) use ($user) {
                $join->on('reads.audit_log_id', '=', 'audit_logs.id')
                    ->where('reads.user_id', '=', $user->getKey());
            })
            ->select([
                'audit_logs.*',
                'reads.read_at as notification_read_at',
            ]);
    }

    public function unreadCount(User $user): int
    {
        return $this->query($user)
            ->whereNull('reads.read_at')
            ->count();
    }

    public function latest(User $user, int $limit = 5)
    {
        return $this->query($user)
            ->orderByDesc('audit_logs.created_at')
            ->orderByDesc('audit_logs.id')
            ->limit(max(1, min(20, $limit)))
            ->get();
    }

    public function paginate(User $user)
    {
        return $this->query($user)
            ->orderByDesc('audit_logs.created_at')
            ->orderByDesc('audit_logs.id')
            ->paginate(15);
    }

    public function markRead(User $user, int $auditLogId): void
    {
        $notification = $this->query($user)
            ->where('audit_logs.id', $auditLogId)
            ->firstOrFail();

        DB::table('admin_notification_reads')->insertOrIgnore([
            'user_id' => $user->getKey(),
            'audit_log_id' => $notification->getKey(),
            'read_at' => now('UTC'),
        ]);
    }

    public function markAllRead(User $user): void
    {
        $unreadIds = $this->query($user)
            ->whereNull('reads.read_at')
            ->reorder()
            ->select('audit_logs.id')
            ->pluck('audit_logs.id');

        $readAt = now('UTC');

        foreach ($unreadIds->chunk(500) as $ids) {
            $rows = $ids->map(fn ($id) => [
                'user_id' => $user->getKey(),
                'audit_log_id' => $id,
                'read_at' => $readAt,
            ])->all();

            DB::table('admin_notification_reads')
                ->insertOrIgnore($rows);
        }
    }

    public function title(AuditLog $log): string
    {
        return self::ACTIONS[$log->action]
            ?? 'Aktivitas operasional';
    }
}
