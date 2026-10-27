<?php

namespace Tests\Unit;

use App\Services\TicketPurchaseService;
use App\Support\TicketPurchaseDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\FakeTicketCatalogProvider;
use Tests\TestCase;

class TicketPurchaseServiceTest extends TestCase
{
    private function ticket(): array
    {
        return [
            'reference' => 'master:5', 'name' => 'Tiket uji kalkulasi', 'category' => 'Tiket Masuk',
            'is_active' => true, 'adult_price' => 25000, 'child_price' => 15000,
        ];
    }

    public function test_calculation_uses_trusted_provider_without_database(): void
    {
        DB::shouldReceive('connection')->never();
        $fake = new FakeTicketCatalogProvider(['master:5' => $this->ticket()]);
        $service = new TicketPurchaseService($fake, new TicketPurchaseDatabase);
        $payload = $this->payload();
        $payload['total'] = 1;
        $payload['items'][0]['price'] = 1;
        $payload['items'][] = $payload['items'][0];
        $result = $service->calculate($payload);

        $this->assertSame('80000.00', $result['total']);
        $this->assertSame('25000.00', $result['items'][0]['adult_subtotal']);
        $this->assertSame('15000.00', $result['items'][0]['child_subtotal']);
        $this->assertSame('40000.00', $result['items'][0]['subtotal']);
        $this->assertSame(['master:5', 'domestik', '2026-12-01'], $fake->calls[0]);
    }

    #[DataProvider('invalidPrices')]
    public function test_invalid_tariffs_never_become_zero_price_orders(mixed $price): void
    {
        $ticket = $this->ticket();
        $ticket['child_price'] = $price;
        $service = new TicketPurchaseService(
            new FakeTicketCatalogProvider(['master:5' => $ticket]), new TicketPurchaseDatabase
        );
        $this->expectException(ValidationException::class);
        $service->calculate($this->payload());
    }

    public static function invalidPrices(): array
    {
        return [
            'missing' => [null], 'negative' => [-1],
            'fraction' => [15000.5], 'text' => ['invalid'],
            'too many decimals' => ['15000.001'],
            'overflow' => [PHP_INT_MAX],
        ];
    }

    public function test_decimal_prices_are_exact_and_free_children_are_allowed(): void
    {
        $ticket = $this->ticket();
        $ticket['adult_price'] = '12345.67';
        $ticket['child_price'] = '0.00';
        $service = new TicketPurchaseService(
            new FakeTicketCatalogProvider(['master:5' => $ticket]), new TicketPurchaseDatabase
        );
        $payload = $this->payload();
        $payload['items'][0]['adult_quantity'] = 3;
        $this->assertSame('37037.01', $service->calculate($payload)['total']);
    }

    public function test_entirely_free_order_is_rejected(): void
    {
        $ticket = $this->ticket();
        $ticket['adult_price'] = $ticket['child_price'] = '0.00';
        $service = new TicketPurchaseService(
            new FakeTicketCatalogProvider(['master:5' => $ticket]), new TicketPurchaseDatabase
        );
        $this->expectException(ValidationException::class);
        $service->calculate($this->payload());
    }

    private function payload(): array
    {
        return [
            'visit_date' => '2026-12-01',
            'visitor_type' => 'domestik',
            'items' => [[
                'ticket_reference' => 'master:5', 'adult_quantity' => 1, 'child_quantity' => 1,
            ]],
        ];
    }
}
