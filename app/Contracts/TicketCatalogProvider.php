<?php

namespace App\Contracts;

interface TicketCatalogProvider
{
    /** @return array<int, array{reference: string, name: string, category: string, rates: array}> */
    public function activeTickets(): array;

    /**
     * Source adapters must return trusted integer rupiah, never browser prices.
     *
     * @return array{reference: string, name: string, category: string, is_active: bool, adult_price: mixed, child_price: mixed}|null
     */
    public function find(string $reference, string $visitorType, string $visitDate): ?array;
}
