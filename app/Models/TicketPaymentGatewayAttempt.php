<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketPaymentGatewayAttempt extends Model
{
    protected $table = 'ticket_payment_gateway_attempts';

    protected $fillable = [
        'ticket_payment_id',
        'reference_id',
        'provider_payment_request_id',
        'provider_payment_id',
        'amount',
        'currency',
        'status',
        'qr_string',
        'qr_image_url',
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
            TicketPayment::class,
            'ticket_payment_id'
        );
    }
}
