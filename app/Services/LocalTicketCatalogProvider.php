<?php

namespace App\Services;

use App\Contracts\TicketCatalogProvider;
use App\Models\LocalDummyTicket;
use App\Support\LocalTicketDatabase;

class LocalTicketCatalogProvider implements TicketCatalogProvider
{
    public function __construct(private LocalTicketDatabase $database) {}

    public function activeTickets(): array
    {
        $this->database->connection();

        return LocalDummyTicket::with('rates')->where('is_active', true)->orderBy('code')->get()
            ->map(fn (LocalDummyTicket $ticket) => [
                'reference' => $ticket->code,
                'name' => $ticket->name,
                'category' => $ticket->category,
                'rates' => $ticket->rates->map(fn ($rate) => [
                    'visitor_type' => $rate->visitor_type,
                    'adult_price' => $rate->adult_price,
                    'child_price' => $rate->child_price,
                ])->all(),
            ])->all();
    }

    public function find(string $reference, string $visitorType, string $visitDate): ?array
    {
        $this->database->connection();
        // Demo tariffs do not vary by date. The real adapter may use $visitDate.
        $ticket = LocalDummyTicket::where('code', $reference)->first();
        if ($ticket === null) {
            return null;
        }

        $rate = $ticket->rates()->where('visitor_type', $visitorType)->first();

        return [
            'reference' => $ticket->code,
            'name' => $ticket->name,
            'category' => $ticket->category,
            'is_active' => $ticket->is_active,
            'adult_price' => $rate?->adult_price,
            'child_price' => $rate?->child_price,
        ];
    }
}
