<?php

namespace Tests\Support;

use App\Contracts\TicketCatalogProvider;

class FakeTicketCatalogProvider implements TicketCatalogProvider
{
    public array $calls = [];

    public function __construct(public array $tickets) {}

    public function activeTickets(): array
    {
        return array_values(array_filter($this->tickets, fn ($ticket) => $ticket['is_active']));
    }

    public function find(string $reference, string $visitorType, string $visitDate): ?array
    {
        $this->calls[] = [$reference, $visitorType, $visitDate];

        return $this->tickets[$reference] ?? null;
    }
}
