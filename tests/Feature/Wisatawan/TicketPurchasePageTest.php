<?php

namespace Tests\Feature\Wisatawan;

use Illuminate\Support\Facades\DB;
use Tests\Support\PostgresTicketTestCase;

class TicketPurchasePageTest extends PostgresTicketTestCase
{
    private const ENDPOINT = '/wisatawan/pembelian-tiket';

    public function test_html_page_renders_master_catalog_and_active_navbar(): void
    {
        $this->actingAs($this->account())->get(self::ENDPOINT)->assertOk()
            ->assertViewIs('wisatawan.ticket-purchase.index')
            ->assertSee('Pilih Jenis Tiket')->assertSee('Data Pengunjung')
            ->assertSee('Tiket Masuk Saja')
            ->assertSee('name="_token"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('href="'.route('ticket-purchase.index').'"', false);
    }

    public function test_browser_checkout_redirects_to_payment_after_persisting_order(): void
    {
        $payload = $this->payload();
        $payload['total'] = '1.00';

        $response = $this->actingAs($this->account())->post(self::ENDPOINT, $payload);
        $order = DB::table('ticket_orders')->first();

        $this->assertNotNull($order);

        $response->assertRedirect(route('ticket-payment.show', $order->code));

        $this->assertSame('30000.00', $order->total);
        $this->assertSame('pending_payment', $order->status);
        $this->assertDatabaseCount('ticket_order_visitors', 2);

        $this->get(route('ticket-purchase.show', $order->code))->assertOk()
            ->assertViewIs('wisatawan.ticket-purchase.show')
            ->assertSee('Pesanan berhasil disimpan.')
            ->assertSee('Menunggu pembayaran')
            ->assertSee('30.000,00')
            ->assertSee('Pemesan Tes')
            ->assertSee('Dewasa Tes')
            ->assertSee('Anak Tes');
    }

    public function test_invalid_browser_checkout_returns_errors_and_preserves_input(): void
    {
        $payload = $this->payload();
        $payload['booker']['email'] = 'bukan-email';
        $this->actingAs($this->account())->from(self::ENDPOINT)->post(self::ENDPOINT, $payload)
            ->assertRedirect(self::ENDPOINT)->assertSessionHasErrors('booker.email')
            ->assertSessionHasInput('booker.name', 'Pemesan Tes');
        $this->withCookie(config('session.cookie'), session()->getId())->get(self::ENDPOINT)
            ->assertOk()->assertSee('Email pemesan tidak valid.')
            ->assertSee('value="Pemesan Tes"', false);
        $this->assertDatabaseCount('ticket_orders', 0);
    }

    public function test_browser_purchase_requires_login_and_wisatawan_role(): void
    {
        $this->get(self::ENDPOINT)->assertRedirect(route('login'));
        $this->actingAs($this->account('admin'))->get(self::ENDPOINT)->assertForbidden();
    }

    public function test_html_order_summary_is_private_and_escapes_customer_input(): void
    {
        $payload = $this->payload();
        $payload['booker']['name'] = '<script>alert("pemesan")</script>';
        $code = $this->actingAs($this->account())->postJson(self::ENDPOINT, $payload)
            ->assertCreated()->json('order.code');
        $this->get(self::ENDPOINT.'/'.$code)->assertOk()
            ->assertSee($payload['booker']['name'])
            ->assertDontSee($payload['booker']['name'], false);
        $this->actingAs($this->account())->get(self::ENDPOINT.'/'.$code)->assertNotFound();
    }

    public function test_invalid_input_shapes_can_be_rendered_after_validation(): void
    {
        $payload = $this->payload();
        $payload['visit_date'] = ['bad'];
        $payload['booker']['name'] = ['bad'];

        $this->actingAs($this->account())->from(self::ENDPOINT)->post(self::ENDPOINT, $payload)
            ->assertRedirect(self::ENDPOINT)->assertSessionHasErrors(['visit_date', 'booker.name']);
        $this->withCookie(config('session.cookie'), session()->getId())->get(self::ENDPOINT)
            ->assertOk()->assertSee('Pesanan belum disimpan.');
        $this->assertDatabaseCount('ticket_orders', 0);
    }

    public function test_empty_catalog_page_remains_usable(): void
    {
        DB::table('daftar_tiket')->update(['status_aktif' => 0]);
        $this->actingAs($this->account())->get(self::ENDPOINT)->assertOk()
            ->assertViewHas('tickets', [])
            ->assertSee('Belum ada tarif tiket yang tersedia');
    }
}
