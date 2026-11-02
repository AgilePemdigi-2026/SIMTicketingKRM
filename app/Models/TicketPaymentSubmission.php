<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_payment_id', 'file_path', 'file_hash', 'status', 'rejection_reason', 'reviewed_by', 'reviewed_at'])]
class TicketPaymentSubmission extends Model
{
    protected $hidden = ['file_path', 'file_hash'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'immutable_datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(TicketPayment::class, 'ticket_payment_id');
    }
}
