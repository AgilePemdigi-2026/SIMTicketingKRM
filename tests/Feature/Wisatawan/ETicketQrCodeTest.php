<?php

namespace Tests\Feature\Wisatawan;

use App\Models\TicketOrder;
use App\Services\TicketPurchaseService;
use Illuminate\Support\Facades\DB;
use Tests\Support\PostgresTicketTestCase;

class ETicketQrCodeTest extends PostgresTicketTestCase
{
    private function order(): TicketOrder
    {
        $user = $this->account();
        $this->actingAs($user);

        return app(TicketPurchaseService::class)->create(
            $user,
            $this->payload()
        );
    }

    private function endpoint(TicketOrder $order): string
    {
        return '/wisatawan/pembelian-tiket/'
            .$order->code.'/pembayaran';
    }

    private function removeCommittedFixtures(
        int $orderId,
        array $userIds
    ): void {
        $this->assertSame(
            'simticketingkrm_purchase_test',
            $this->testConnection->getDatabaseName()
        );

        DB::transaction(function () use ($orderId, $userIds): void {
            $payments = DB::table('ticket_payments')
                ->where('ticket_order_id', $orderId)
                ->pluck('id');

            DB::table('ticket_payment_events')
                ->whereIn('ticket_payment_id', $payments)
                ->delete();

            DB::table('ticket_payment_submissions')
                ->whereIn('ticket_payment_id', $payments)
                ->delete();

            DB::table('ticket_payments')
                ->whereIn('id', $payments)
                ->delete();

            $items = DB::table('ticket_order_items')
                ->where('ticket_order_id', $orderId)
                ->pluck('id');

            DB::table('issued_tickets')
                ->where('ticket_order_id', $orderId)
                ->delete();

            DB::table('ticket_order_visitors')
                ->whereIn('ticket_order_item_id', $items)
                ->delete();

            DB::table('ticket_order_items')
                ->whereIn('id', $items)
                ->delete();

            DB::table('ticket_orders')
                ->where('id', $orderId)
                ->delete();

            DB::table('users')
                ->whereIn('id', $userIds)
                ->delete();
        });
    }

    public function test_paid_order_displays_unique_qr_code_for_each_visitor(): void
    {
        $order = $this->order();
        $ownerId = $order->user_id;
        $staff = $this->account('petugas');

        try {
            // Pilih pembayaran Cash.
            $this->postJson($this->endpoint($order), [
                'method' => 'cash',
            ])->assertOk();

            $payment = $order->payment()->firstOrFail();

            // Commit transaksi luar agar event after-commit dijalankan.
            $this->testConnection->commit();

            // Konfirmasi pembayaran oleh petugas.
            $this->actingAs($staff)
                ->postJson('/petugas/pembayaran/'.$payment->id.'/konfirmasi', [
                    'receipt_checked' => 1,
                ])
                ->assertOk()
                ->assertJsonPath('order_status', 'paid');

            // Pastikan tiket diterbitkan untuk kedua pengunjung.
            $tickets = DB::table('issued_tickets')
                ->where('ticket_order_id', $order->id)
                ->get();

            $this->assertCount(2, $tickets);
            $this->assertCount(
                2,
                $tickets->pluck('qr_token')->unique()
            );

            // Pemilik pesanan membuka halaman E-Tiket.
            $response = $this->actingAs($order->user)
                ->get($this->endpoint($order).'/e-tiket');

            $response->assertOk()
                ->assertSee('QR Code Tiket')
                ->assertSee('data:image/svg+xml;base64,', false);

            $this->assertSame(
                2,
                substr_count(
                    $response->getContent(),
                    'data:image/svg+xml;base64,'
                )
            );
        } finally {
            while ($this->testConnection->transactionLevel() > 0) {
                $this->testConnection->rollBack();
            }

            $this->removeCommittedFixtures(
                $order->id,
                [$ownerId, $staff->id]
            );
        }
    }

    public function test_unpaid_order_cannot_access_e_ticket(): void
    {
        $order = $this->order();

        $this->get($this->endpoint($order).'/e-tiket')
            ->assertRedirect(
                route('ticket-payment.show', $order->code)
            );
    }

    public function test_other_user_cannot_access_someone_elses_e_ticket(): void
    {
        $order = $this->order();

        $this->actingAs($this->account())
            ->get($this->endpoint($order).'/e-tiket')
            ->assertNotFound();
    }
}
