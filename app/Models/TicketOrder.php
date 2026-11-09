<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['code', 'user_id', 'visit_date', 'visitor_type', 'booker_name', 'booker_identity_number', 'booker_phone', 'booker_email', 'total', 'status', 'checkout_token', 'checkout_fingerprint'])]
class TicketOrder extends Model
{
    protected $hidden = ['checkout_token', 'checkout_fingerprint'];

    protected function casts(): array
    {
        return ['visit_date' => 'date:Y-m-d', 'total' => 'decimal:2'];
    }

    public function payment(): HasOne
    {
        return $this->hasOne(TicketPayment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TicketOrderItem::class);
    }
}
