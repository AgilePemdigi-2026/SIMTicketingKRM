<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityReservation extends Model
{
    protected $connection = 'pgsql';

    protected $fillable = [
        'code', 'invoice_number', 'user_id', 'id_fasilitas', 'id_sesi',
        'facility_name', 'session_name', 'visit_date', 'start_time', 'end_time',
        'duration_minutes', 'participant_count', 'customer_name', 'identity_number',
        'phone', 'email', 'purpose', 'additional_information', 'unit_price',
        'total_amount', 'administrative_status', 'capacity_hold_until',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'capacity_hold_until' => 'datetime',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
