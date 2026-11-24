<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityReservationRescheduleRequest extends Model
{
    protected $connection = 'pgsql';

    protected $fillable = [
        'facility_reservation_id',
        'user_id',
        'original_visit_date',
        'original_id_sesi',
        'requested_visit_date',
        'requested_id_sesi',
        'reason',
        'status',
        'admin_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'original_visit_date' => 'date',
            'requested_visit_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(
            FacilityReservation::class,
            'facility_reservation_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function originalSession(): BelongsTo
    {
        return $this->belongsTo(
            SesiFasilitas::class,
            'original_id_sesi',
            'id_sesi'
        );
    }

    public function requestedSession(): BelongsTo
    {
        return $this->belongsTo(
            SesiFasilitas::class,
            'requested_id_sesi',
            'id_sesi'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }
}
