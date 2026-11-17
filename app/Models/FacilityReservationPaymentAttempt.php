<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacilityReservationPaymentAttempt extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'facility_reservation_payment_attempts';

    protected $fillable = [
        'facility_reservation_payment_id',
        'reference_id',
        'provider_payment_id',
        'currency',
        'amount',
        'status',
        'qr_image_url',
        'qr_string',
        'expires_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(
            FacilityReservationPayment::class,
            'facility_reservation_payment_id'
        );
    }
}
