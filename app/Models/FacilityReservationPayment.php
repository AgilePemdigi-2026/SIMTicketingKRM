<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilityReservationPayment extends Model
{
    protected $connection = 'pgsql';

    protected $table = 'facility_reservation_payments';

    protected $fillable = [
        'facility_reservation_id',
        'method',
        'status',
        'amount',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(
            FacilityReservation::class,
            'facility_reservation_id'
        );
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(
            FacilityReservationPaymentAttempt::class,
            'facility_reservation_payment_id'
        );
    }
}
