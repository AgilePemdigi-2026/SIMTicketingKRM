<?php

namespace App\Listeners;

use App\Events\TicketPaymentConfirmed;
use App\Models\IssuedTicket;
use App\Models\TicketOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueTicketsAfterPayment
{
    public function handle(TicketPaymentConfirmed $event): void
    {
        DB::transaction(function () use ($event) {
            $order = TicketOrder::query()
                ->where('code', $event->orderCode)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status !== 'paid') {
                return;
            }

            $order->load('items.visitors');

            foreach ($order->items as $item) {
                foreach ($item->visitors as $visitor) {
                    IssuedTicket::firstOrCreate(
                        [
                            'ticket_order_visitor_id' => $visitor->id,
                        ],
                        [
                            'ticket_order_id' => $order->id,
                            'ticket_order_item_id' => $item->id,
                            'ticket_code' => (string) Str::uuid(),
                            'qr_token' => Str::random(64),
                            'status' => 'active',
                        ]
                    );
                }
            }
        }, 3);
    }
}
