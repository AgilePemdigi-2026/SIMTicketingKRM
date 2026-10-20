<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ticket_order_id', 'ticket_reference', 'ticket_name', 'ticket_category', 'adult_quantity', 'child_quantity', 'adult_unit_price', 'child_unit_price', 'subtotal'])]
class TicketOrderItem extends Model
{
    protected $connection = 'sqlite';

    protected function casts(): array
    {
        return [
            'adult_quantity' => 'integer', 'child_quantity' => 'integer',
            'adult_unit_price' => 'integer', 'child_unit_price' => 'integer', 'subtotal' => 'integer',
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
