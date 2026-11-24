<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogService
{
    /**
     * Hanya kolom operasional yang diizinkan masuk histori.
     *
     * Jangan memasukkan password, token, QR token,
     * nomor identitas, atau kredensial payment gateway.
     */
    private const ALLOWED_FIELDS = [
        'status',
        'administrative_status',
        'payment_status',
        'method',
        'amount',
        'total',
        'total_amount',
        'visit_date',
        'paid_at',
        'used_at',
        'checked_in_by',
        'confirmed_by',
        'role',
        'is_active',
        'sale_channel',
        'participant_count',
        'id_sesi',
        'id_fasilitas',
        'requested_visit_date',
        'requested_id_sesi',
    ];

    /**
     * Mencatat aktivitas pengguna atau sistem.
     *
     * Actor null digunakan untuk aktivitas otomatis.
     */
    public function record(
        ?User $actor,
        string $module,
        string $action,
        string $description,
        ?string $subjectType = null,
        string|int|null $subjectId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $actor?->getKey(),
            'actor_name' => $actor?->name ?? 'Sistem',
            'actor_role' => $actor?->role ?? 'system',
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId !== null
                ? (string) $subjectId
                : null,
            'old_values' => $this->sanitizeValues($oldValues),
            'new_values' => $this->sanitizeValues($newValues),
            'created_at' => now('UTC'),
        ]);
    }

    /**
     * Menyaring histori agar hanya data operasional
     * yang diizinkan dapat disimpan.
     */
    private function sanitizeValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $filtered = [];

        foreach ($values as $key => $value) {
            if (! in_array($key, self::ALLOWED_FIELDS, true)) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $filtered[$key] = $value;
            }
        }

        return $filtered === []
            ? null
            : $filtered;
    }
}
