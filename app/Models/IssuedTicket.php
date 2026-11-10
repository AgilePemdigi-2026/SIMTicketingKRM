<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedTicket extends Model
{
    protected $fillable = [
        'ticket_order_id',
        'ticket_order_item_id',
        'ticket_order_visitor_id',
        'ticket_code',
        'qr_token',
        'status',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'immutable_datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class, 'ticket_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(TicketOrderItem::class, 'ticket_order_item_id');
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(TicketOrderVisitor::class, 'ticket_order_visitor_id');
    }
}
