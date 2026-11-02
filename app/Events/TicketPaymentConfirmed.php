<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class TicketPaymentConfirmed implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public readonly string $orderCode,
        public readonly string $paymentCode,
        public readonly string $method,
        public readonly string $amount,
        public readonly string $paidAt,
    ) {}
}
