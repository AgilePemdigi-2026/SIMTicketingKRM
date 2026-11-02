<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'ticket_order_id', 'method', 'amount', 'status', 'merchant_reference', 'confirmed_by', 'paid_at'])]
class TicketPayment extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'paid_at' => 'immutable_datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class, 'ticket_order_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TicketPaymentSubmission::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TicketPaymentEvent::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'under_review' => 'Menunggu verifikasi Petugas',
            'rejected' => 'Bukti ditolak — unggah ulang',
            'paid' => 'Lunas',
            default => 'Menunggu pembayaran',
        };
    }
}
