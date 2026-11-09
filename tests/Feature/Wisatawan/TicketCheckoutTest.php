<?php

namespace Tests\Feature\Wisatawan;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\PostgresTicketTestCase;

class TicketCheckoutTest extends PostgresTicketTestCase
{
    private const ENDPOINT = '/wisatawan/pembelian-tiket';

    #[DataProvider('quantities')]
    public function test_checkout_accepts_one_to_four_tickets(int $quantity): void
    {
        $payload = $this->payload();
        $payload['items'][0]['adult_quantity'] = $quantity;
        $payload['items'][0]['child_quantity'] = 0;
        $payload['items'][0]['visitors'] = array_fill(0, $quantity, $payload['items'][0]['visitors'][0]);
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $payload)
            ->assertCreated()->assertJsonPath('order.total', ($quantity * 20000).'.00');
        $this->assertDatabaseCount('ticket_orders', 1);
        $this->assertDatabaseCount('ticket_order_items', 1);
        $this->assertDatabaseCount('ticket_order_visitors', $quantity);
    }

    public static function quantities(): array
    {
        return [[1], [2], [3], [4]];
    }

    #[DataProvider('invalidPayloads')]
    public function test_incomplete_or_inconsistent_checkout_is_rejected(string $case, string $error): void
    {
        $payload = $this->payload();
        switch ($case) {
            case 'missing booker': unset($payload['booker']);
                break;
            case 'bad email': $payload['booker']['email'] = 'wrong';
                break;
            case 'bad phone': $payload['booker']['phone'] = 'abcdefghi';
                break;
            case 'missing identity': unset($payload['items'][0]['visitors'][1]['identity_number']);
                break;
            case 'wrong age': $payload['items'][0]['visitors'][1]['age_category'] = 'adult';
                break;
            case 'missing visitor': array_pop($payload['items'][0]['visitors']);
                break;
            case 'no reference': unset($payload['items'][0]['ticket_reference']);
                break;
            case 'bad type': $payload['visitor_type'] = 'other';
                break;
            case 'past date': $payload['visit_date'] = now('Asia/Jakarta')->subDay()->toDateString();
                break;
            case 'zero': $payload['items'][0]['adult_quantity'] = $payload['items'][0]['child_quantity'] = 0;
                break;
            case 'over four': $payload['items'] = array_fill(0, 3, $payload['items'][0]);
                break;
        }
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $payload)
            ->assertUnprocessable()->assertJsonValidationErrors($error);
        foreach (['ticket_orders', 'ticket_order_items', 'ticket_order_visitors'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public static function invalidPayloads(): array
    {
        return [
            ['missing booker', 'booker.name'], ['bad email', 'booker.email'], ['bad phone', 'booker.phone'],
            ['missing identity', 'items.0.visitors.1.identity_number'], ['wrong age', 'items.0.visitors'],
            ['missing visitor', 'items.0.visitors'], ['no reference', 'items.0.ticket_reference'],
            ['bad type', 'visitor_type'], ['past date', 'visit_date'], ['zero', 'items'], ['over four', 'items'],
        ];
    }
}
