<?php

namespace App\Services;

use App\Contracts\TicketCatalogProvider;
use App\Models\TicketOrder;
use App\Models\User;
use App\Support\LocalTicketDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketPurchaseService
{
    public function __construct(
        private TicketCatalogProvider $catalog,
        private LocalTicketDatabase $database,
    ) {}

    /**
     * Receives data validated by CheckoutTicketPurchaseRequest.
     * No database or source-specific model is needed for price calculation.
     */
    public function calculate(array $data): array
    {
        $items = [];
        $total = 0;
        foreach ($data['items'] as $index => $item) {
            $ticket = $this->catalog->find(
                $item['ticket_reference'], $data['visitor_type'], $data['visit_date']
            );
            if ($ticket === null || $ticket['is_active'] !== true) {
                throw ValidationException::withMessages([
                    "items.{$index}.ticket_reference" => 'Tiket tidak ditemukan atau tidak aktif.',
                ]);
            }

            // Limit each price to keep all four-ticket sums within PHP integers.
            foreach (['adult_price', 'child_price'] as $price) {
                if (! is_int($ticket[$price]) || $ticket[$price] <= 0 || $ticket[$price] > intdiv(PHP_INT_MAX, 4)) {
                    throw ValidationException::withMessages([
                        "items.{$index}.ticket_reference" => 'Tarif tiket belum tersedia atau tidak valid. Pembelian belum dapat diselesaikan.',
                    ]);
                }
            }

            $adultQuantity = (int) $item['adult_quantity'];
            $childQuantity = (int) $item['child_quantity'];
            $adultSubtotal = $adultQuantity * $ticket['adult_price'];
            $childSubtotal = $childQuantity * $ticket['child_price'];
            $subtotal = $adultSubtotal + $childSubtotal;
            $items[] = [
                'ticket_reference' => $ticket['reference'],
                'ticket_name' => $ticket['name'],
                'ticket_category' => $ticket['category'],
                'adult_quantity' => $adultQuantity,
                'child_quantity' => $childQuantity,
                'adult_unit_price' => $ticket['adult_price'],
                'child_unit_price' => $ticket['child_price'],
                'adult_subtotal' => $adultSubtotal,
                'child_subtotal' => $childSubtotal,
                'subtotal' => $subtotal,
            ];
            $total += $subtotal;
        }

        if ($total <= 0) {
            throw ValidationException::withMessages(['items' => 'Total pembelian harus lebih dari nol.']);
        }

        return ['items' => $items, 'total' => $total];
    }

    public function create(User $user, array $data): TicketOrder
    {
        abort_unless($user->role === 'wisatawan', 403);
        $this->database->connection();

        // Guard verified sqlite is the default; all local models use this connection.
        return DB::transaction(function () use ($user, $data) {
            $quote = $this->calculate($data);
            $booker = $data['booker'];
            $order = TicketOrder::create([
                'code' => (string) Str::uuid(),
                'user_id' => $user->getKey(),
                'visit_date' => $data['visit_date'],
                'visitor_type' => $data['visitor_type'],
                'booker_name' => $booker['name'],
                'booker_identity_number' => $booker['identity_number'],
                'booker_phone' => $booker['phone'],
                'booker_email' => $booker['email'],
                'total' => $quote['total'],
                'status' => 'pending_payment',
            ]);
            foreach ($quote['items'] as $index => $snapshot) {
                unset($snapshot['adult_subtotal'], $snapshot['child_subtotal']);
                $orderItem = $order->items()->create($snapshot);
                foreach ($data['items'][$index]['visitors'] as $visitor) {
                    $orderItem->visitors()->create([
                        'name' => $visitor['name'],
                        'identity_number' => $visitor['identity_number'],
                        'age_category' => $visitor['age_category'],
                    ]);
                }
            }

            return $order->load('items.visitors');
        });
    }
}
