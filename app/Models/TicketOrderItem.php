<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ticket_order_id', 'ticket_reference', 'ticket_name', 'ticket_category', 'adult_quantity', 'child_quantity', 'adult_unit_price', 'child_unit_price', 'subtotal'])]
class TicketOrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'adult_quantity' => 'integer', 'child_quantity' => 'integer',
            'adult_unit_price' => 'decimal:2', 'child_unit_price' => 'decimal:2', 'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(TicketOrder::class, 'ticket_order_id');
    }

    public function visitors(): HasMany
    {
        return $this->hasMany(TicketOrderVisitor::class);
    }
}
