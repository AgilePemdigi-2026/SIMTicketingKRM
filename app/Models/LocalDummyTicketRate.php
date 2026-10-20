<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['local_dummy_ticket_id', 'visitor_type', 'adult_price', 'child_price'])]
class LocalDummyTicketRate extends Model
{
    protected $connection = 'sqlite';

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(LocalDummyTicket::class, 'local_dummy_ticket_id');
    }
}
