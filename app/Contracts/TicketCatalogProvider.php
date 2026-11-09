<?php

namespace App\Contracts;

interface TicketCatalogProvider
{
    /** @return array<int, array{reference: string, name: string, category: string, group: string, available_quota: int, terms: ?string, rates: array, wahana: array}> */
    public function activeTickets(): array;

    /**
     * Source adapters must return trusted integer rupiah, never browser prices.
     *
     * @return array{reference: string, name: string, category: string, is_active: bool, available_quota: int, adult_price: mixed, child_price: mixed}|null
     */
    public function find(string $reference, string $visitorType, string $visitDate): ?array;
}
