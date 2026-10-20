<?php

namespace Tests\Unit;

use App\Services\TicketPurchaseService;
use App\Support\LocalTicketDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\FakeTicketCatalogProvider;
use Tests\Support\LocalTicketTestCase;

class TicketPurchaseServiceTest extends LocalTicketTestCase
{
    private function ticket(): array
    {
        return [
            'reference' => 'dummy:masuk', 'name' => '[DUMMY] Masuk', 'category' => 'masuk',
            'is_active' => true, 'adult_price' => 25000, 'child_price' => 15000,
        ];
    }

    public function test_calculation_uses_trusted_provider_without_database(): void
    {
        DB::shouldReceive('connection')->never();
        $fake = new FakeTicketCatalogProvider(['dummy:masuk' => $this->ticket()]);
        $service = new TicketPurchaseService($fake, new LocalTicketDatabase);
        $payload = $this->purchasePayload();
        $payload['total'] = 1;
        $payload['items'][0]['price'] = 1;
        $payload['items'][] = $payload['items'][0];
        $result = $service->calculate($payload);

        $this->assertSame(80000, $result['total']);
        $this->assertSame(25000, $result['items'][0]['adult_subtotal']);
        $this->assertSame(15000, $result['items'][0]['child_subtotal']);
        $this->assertSame(40000, $result['items'][0]['subtotal']);
        $this->assertSame(['dummy:masuk', 'domestik', '2026-10-21'], $fake->calls[0]);
    }

    #[DataProvider('invalidPrices')]
    public function test_invalid_tariffs_never_become_zero_price_orders(mixed $price): void
    {
        $ticket = $this->ticket();
        $ticket['child_price'] = $price;
        $service = new TicketPurchaseService(
            new FakeTicketCatalogProvider(['dummy:masuk' => $ticket]), new LocalTicketDatabase
        );
        $this->expectException(ValidationException::class);
        $service->calculate($this->purchasePayload());
    }

    public static function invalidPrices(): array
    {
        return [
            'missing' => [null], 'zero' => [0], 'negative' => [-1],
            'fraction' => [15000.5], 'text' => ['invalid'],
            'numeric string is not trusted integer' => ['15000'],
            'overflow' => [PHP_INT_MAX],
        ];
    }
}
