<?php

namespace Tests\Feature\Wisatawan;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\PostgresTicketTestCase;

class TicketPurchaseCompletionTest extends PostgresTicketTestCase
{
    private const ENDPOINT = '/wisatawan/pembelian-tiket';

    public function test_catalog_exposes_explicit_groups_terms_and_master_quota(): void
    {
        DB::table('daftar_tiket')->where('id_tiket', 1)->update(['kategori_tiket' => 'Wahana']);
        DB::table('daftar_tiket')->where('id_tiket', 4)->update(['kategori_tiket' => 'Promo Wahana Khusus']);
        $this->actingAs($this->account())->getJson(self::ENDPOINT)->assertOk()
            ->assertJsonPath('tickets.0.group', 'wahana')
            ->assertJsonPath('tickets.1.group', 'lainnya')
            ->assertJsonPath('tickets.2.group', 'masuk')
            ->assertJsonPath('tickets.2.available_quota', 10)
            ->assertJsonPath('tickets.2.terms', 'Testing');
    }

    public function test_preview_returns_exact_quote_without_creating_orders_or_reserving_quota(): void
    {
        $this->actingAs($this->account())->postJson(self::ENDPOINT.'/preview', $this->payload())
            ->assertOk()->assertJsonPath('quote.total', '30000.00')
            ->assertJsonPath('quote.items.0.available_quota', 10)
            ->assertJsonPath('quota_reserved', false);
        $this->assertDatabaseCount('ticket_orders', 0);
        $this->assertSame(10, DB::table('daftar_tiket')->where('id_tiket', 5)->value('kuota'));
    }

    #[DataProvider('invalidPreviewCases')]
    public function test_invalid_preview_has_no_writes(string $case, string $error): void
    {
        $data = $this->payload();
        switch ($case) {
            case 'past': $data['visit_date'] = now('Asia/Jakarta')->subDay()->toDateString();
                break;
            case 'inactive': DB::table('daftar_tiket')->where('id_tiket', 5)->update(['status_aktif' => 0]);
                break;
            case 'zero quota': DB::table('daftar_tiket')->where('id_tiket', 5)->update(['kuota' => 0]);
                break;
            case 'aggregate quota':
                DB::table('daftar_tiket')->where('id_tiket', 5)->update(['kuota' => 3]);
                $data['items'][] = $data['items'][0];
                break;
            case 'visitor type': $data['visitor_type'] = 'mancanegara';
                break;
            case 'over four': $data['items'][0]['adult_quantity'] = 4;
                break;
        }
        $this->actingAs($this->account())->postJson(self::ENDPOINT.'/preview', $data)
            ->assertUnprocessable()->assertJsonValidationErrors($error);
        $this->assertDatabaseCount('ticket_orders', 0);
    }

    public static function invalidPreviewCases(): array
    {
        return [
            ['past', 'visit_date'], ['inactive', 'items.0.ticket_reference'],
            ['zero quota', 'items.0.ticket_reference'], ['aggregate quota', 'items.1.ticket_reference'],
            ['visitor type', 'items.0.ticket_reference'], ['over four', 'items'],
        ];
    }

    public function test_checkout_rechecks_price_and_quota_after_preview(): void
    {
        $data = $this->payload();
        $this->actingAs($this->account())->postJson(self::ENDPOINT.'/preview', $data)
            ->assertOk()->assertJsonPath('quote.total', '30000.00');
        DB::table('daftar_tiket')->where('id_tiket', 5)->update(['harga_dewasa' => '22222.25']);
        $this->postJson(self::ENDPOINT, $data)->assertCreated()->assertJsonPath('order.total', '32222.25');
        DB::table('daftar_tiket')->where('id_tiket', 5)->update(['kuota' => 1]);
        $data['checkout_token'] = (string) Str::uuid();
        $this->postJson(self::ENDPOINT, $data)->assertUnprocessable()->assertJsonValidationErrors('items.0.ticket_reference');
        $this->assertDatabaseCount('ticket_orders', 1);
    }

    public function test_identical_checkout_token_replays_the_existing_snapshot_even_after_master_changes(): void
    {
        $data = $this->payload();
        $this->actingAs($this->account());
        $first = $this->postJson(self::ENDPOINT, $data)->assertCreated();
        DB::table('daftar_tiket')->where('id_tiket', 5)->update(['status_aktif' => 0, 'kuota' => 0]);
        $data['items'][0]['adult_quantity'] = '1';
        $this->postJson(self::ENDPOINT, $data)->assertCreated()
            ->assertJsonPath('order.code', $first->json('order.code'))
            ->assertJsonPath('order.total', '30000.00')
            ->assertJsonMissingPath('order.checkout_token')->assertJsonMissingPath('order.checkout_fingerprint');
        $this->assertDatabaseCount('ticket_orders', 1);
        $this->assertDatabaseCount('ticket_order_visitors', 2);
    }

    public function test_identical_replay_still_returns_the_order_after_its_visit_date(): void
    {
        $this->freezeTime();
        $data = $this->payload();
        $first = $this->actingAs($this->account())->postJson(self::ENDPOINT, $data)->assertCreated();
        $this->travelTo(now('Asia/Jakarta')->addDays(2));

        $this->postJson(self::ENDPOINT, $data)->assertCreated()
            ->assertJsonPath('order.code', $first->json('order.code'));
        $this->assertDatabaseCount('ticket_orders', 1);
        $this->travelBack();
    }

    public function test_checkout_token_cannot_be_reused_with_different_payload(): void
    {
        $data = $this->payload();
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $data)->assertCreated();
        $data['booker']['name'] = 'Pemesan Berbeda';
        $this->postJson(self::ENDPOINT, $data)->assertStatus(409);
        $this->assertDatabaseCount('ticket_orders', 1);
    }

    public function test_checkout_tokens_are_scoped_to_the_owner_and_old_api_payload_remains_supported(): void
    {
        $data = $this->payload();
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $data)->assertCreated();
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $data)->assertCreated();
        unset($data['checkout_token']);
        $this->postJson(self::ENDPOINT, $data)->assertCreated();
        $this->assertDatabaseCount('ticket_orders', 3);
    }

    public function test_browser_checkout_requires_valid_token(): void
    {
        $data = $this->payload();
        unset($data['checkout_token']);
        $this->actingAs($this->account())->from(self::ENDPOINT)->post(self::ENDPOINT, $data)
            ->assertRedirect(self::ENDPOINT)->assertSessionHasErrors('checkout_token');
        $data['checkout_token'] = 'bad-token';
        $this->postJson(self::ENDPOINT, $data)->assertUnprocessable()->assertJsonValidationErrors('checkout_token');
        $this->assertDatabaseCount('ticket_orders', 0);
    }

    public function test_booker_can_visit_without_duplicating_the_visitor_count(): void
    {
        $data = $this->payload();
        $data['booker_is_visitor'] = true;
        $data['items'][0]['visitors'][0]['name'] = $data['booker']['name'];
        $data['items'][0]['visitors'][0]['identity_number'] = $data['booker']['identity_number'];
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $data)->assertCreated()
            ->assertJsonCount(2, 'order.items.0.visitors');
        $this->assertDatabaseCount('ticket_order_visitors', 2);
    }

    public function test_booker_visiting_must_match_the_first_adult(): void
    {
        $data = $this->payload();
        $data['booker_is_visitor'] = true;
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $data)->assertUnprocessable()
            ->assertJsonValidationErrors('booker_is_visitor');
        $this->assertDatabaseCount('ticket_orders', 0);
    }

    public function test_child_only_order_has_separate_booker_and_rejects_visiting_booker(): void
    {
        $data = $this->payload();
        $data['items'][0]['adult_quantity'] = 0;
        $data['items'][0]['visitors'] = [$data['items'][0]['visitors'][1]];
        $data['booker_is_visitor'] = true;
        $this->actingAs($this->account())->postJson(self::ENDPOINT, $data)->assertUnprocessable()
            ->assertJsonValidationErrors('booker_is_visitor');
        $data['booker_is_visitor'] = false;
        $this->postJson(self::ENDPOINT, $data)->assertCreated()
            ->assertJsonPath('order.total', '10000.00')->assertJsonCount(1, 'order.items.0.visitors');
    }

    public function test_order_list_is_paginated_newest_first_and_private(): void
    {
        $this->actingAs($this->account());
        $codes = [];
        for ($i = 0; $i < 21; $i++) {
            $codes[] = $this->postJson(self::ENDPOINT, $this->payload())->assertCreated()->json('order.code');
        }
        $this->getJson(self::ENDPOINT.'/pesanan')->assertOk()
            ->assertJsonPath('orders.total', 21)->assertJsonPath('orders.per_page', 20)
            ->assertJsonCount(20, 'orders.data')->assertJsonPath('orders.data.0.code', $codes[20]);
        $this->getJson(self::ENDPOINT.'/pesanan?page=2')->assertOk()
            ->assertJsonCount(1, 'orders.data')->assertJsonPath('orders.data.0.code', $codes[0]);
        $this->get(self::ENDPOINT.'/pesanan')->assertOk()->assertSee('Pesanan Saya')->assertSee($codes[20]);
        $this->actingAs($this->account())->getJson(self::ENDPOINT.'/pesanan')->assertOk()->assertJsonCount(0, 'orders.data');
        $this->get(self::ENDPOINT.'/'.$codes[0])->assertNotFound();
    }

    public function test_new_endpoints_require_wisatawan_authentication(): void
    {
        $this->postJson(self::ENDPOINT.'/preview', $this->payload())->assertUnauthorized();
        $this->getJson(self::ENDPOINT.'/pesanan')->assertUnauthorized();
        $this->actingAs($this->account('petugas'))->postJson(self::ENDPOINT.'/preview', $this->payload())->assertForbidden();
        $this->get(self::ENDPOINT.'/pesanan')->assertForbidden();
    }

    public function test_empty_order_list_has_a_purchase_link_and_site_entrypoints_are_connected(): void
    {
        $this->actingAs($this->account())->get(self::ENDPOINT.'/pesanan')->assertOk()
            ->assertSee('Belum ada pesanan')->assertSee(route('ticket-purchase.index'), false);
        $this->get('/beranda')->assertOk()->assertSee('href="'.route('ticket-purchase.index').'"', false);
        $this->get('/destinasi')->assertOk()->assertSee('href="'.route('ticket-purchase.index').'"', false);
    }
}
