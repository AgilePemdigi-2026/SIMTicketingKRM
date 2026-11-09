<?php

namespace App\Services;

use App\Contracts\TicketCatalogProvider;
use App\Models\TicketOrder;
use App\Models\User;
use App\Support\TicketMoney;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketPurchaseService
{
    public function __construct(
        private TicketCatalogProvider $catalog,
        private TicketPurchaseDatabase $database,
    ) {}

    /**
     * Receives data validated by CheckoutTicketPurchaseRequest.
     * No database or source-specific model is needed for price calculation.
     */
    public function calculate(array $data): array
    {
        $items = [];
        $total = 0;
        $quantities = [];
        foreach ($data['items'] as $index => $item) {
            $ticket = $this->catalog->find(
                $item['ticket_reference'], $data['visitor_type'], $data['visit_date']
            );
            if ($ticket === null || $ticket['is_active'] !== true) {
                throw ValidationException::withMessages([
                    "items.{$index}.ticket_reference" => 'Tiket tidak ditemukan atau tidak aktif.',
                ]);
            }

            try {
                $adultPrice = TicketMoney::minor($ticket['adult_price'] ?? null);
                $childPrice = TicketMoney::minor($ticket['child_price'] ?? null);
            } catch (\InvalidArgumentException) {
                throw ValidationException::withMessages([
                    "items.{$index}.ticket_reference" => 'Tarif tiket belum tersedia atau tidak valid. Pembelian belum dapat diselesaikan.',
                ]);
            }

            $adultQuantity = (int) $item['adult_quantity'];
            $childQuantity = (int) $item['child_quantity'];
            $reference = $ticket['reference'];
            $quantities[$reference] = ($quantities[$reference] ?? 0) + $adultQuantity + $childQuantity;
            $quota = $ticket['available_quota'] ?? null;
            if (! is_int($quota) || $quota < $quantities[$reference]) {
                throw ValidationException::withMessages([
                    "items.{$index}.ticket_reference" => 'Kuota tiket tidak mencukupi untuk seluruh pilihan tiket yang sama. Silakan sesuaikan jumlah tiket.',
                ]);
            }
            $adultSubtotal = $adultQuantity * $adultPrice;
            $childSubtotal = $childQuantity * $childPrice;
            $subtotal = $adultSubtotal + $childSubtotal;
            $items[] = [
                'ticket_reference' => $ticket['reference'],
                'ticket_name' => $ticket['name'],
                'ticket_category' => $ticket['category'],
                'adult_quantity' => $adultQuantity,
                'child_quantity' => $childQuantity,
                'adult_unit_price' => TicketMoney::decimal($adultPrice),
                'child_unit_price' => TicketMoney::decimal($childPrice),
                'adult_subtotal' => TicketMoney::decimal($adultSubtotal),
                'child_subtotal' => TicketMoney::decimal($childSubtotal),
                'subtotal' => TicketMoney::decimal($subtotal),
                'available_quota' => $quota,
            ];
            $total += $subtotal;
        }

        if ($total <= 0) {
            throw ValidationException::withMessages(['items' => 'Total pembelian harus lebih dari nol.']);
        }

        return ['items' => $items, 'total' => TicketMoney::decimal($total)];
    }

    public function create(User $user, array $data): TicketOrder
    {
        abort_unless($user->role === 'wisatawan', 403);
        $connection = $this->database->connection();

        $fingerprint = $this->fingerprint($data);
        $token = $data['checkout_token'] ?? null;

        return $connection->transaction(function () use ($user, $data, $token, $fingerprint) {
            User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            if ($token !== null) {
                $existing = TicketOrder::where('user_id', $user->getKey())->where('checkout_token', $token)->first();
                if ($existing !== null) {
                    abort_unless(hash_equals($existing->checkout_fingerprint, $fingerprint), 409,
                        'Token checkout sudah digunakan untuk data lain. Mulai pembelian baru untuk mengubah pesanan.');

                    return $existing->load('items.visitors');
                }
            }
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
                'checkout_token' => $token,
                'checkout_fingerprint' => $token !== null ? $fingerprint : null,
            ]);
            foreach ($quote['items'] as $index => $snapshot) {
                unset($snapshot['adult_subtotal'], $snapshot['child_subtotal'], $snapshot['available_quota']);
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

    private function fingerprint(array $data): string
    {
        $canonical = [
            'visit_date' => $data['visit_date'], 'visitor_type' => $data['visitor_type'],
            'booker' => array_map(fn ($key) => $data['booker'][$key], ['name', 'identity_number', 'phone', 'email']),
            'items' => array_map(fn ($item) => [
                'reference' => $item['ticket_reference'],
                'adult_quantity' => (int) $item['adult_quantity'], 'child_quantity' => (int) $item['child_quantity'],
                'visitors' => array_map(fn ($visitor) => [$visitor['name'], $visitor['identity_number'], $visitor['age_category']], $item['visitors']),
            ], $data['items']),
        ];

        return hash('sha256', json_encode($canonical, JSON_THROW_ON_ERROR));
    }
}
