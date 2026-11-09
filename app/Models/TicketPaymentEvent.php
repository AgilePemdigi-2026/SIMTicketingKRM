<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['ticket_payment_id', 'actor_id', 'action', 'reason'])]
class TicketPaymentEvent extends Model {}
