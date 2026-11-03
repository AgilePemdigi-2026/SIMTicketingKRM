<?php

namespace Tests\Feature;

use App\Events\TicketPaymentConfirmed;
use App\Models\TicketOrder;
use App\Models\TicketPayment;
use App\Models\TicketPaymentEvent;
use App\Services\TicketPaymentService;
use App\Services\TicketPurchaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\Support\PostgresTicketTestCase;

class TicketPaymentTest extends PostgresTicketTestCase
{
    private function order(): TicketOrder
    {
        $user = $this->account();
        $this->actingAs($user);

        return app(TicketPurchaseService::class)->create($user, $this->payload());
    }

    private function endpoint(TicketOrder $order): string
    {
        return '/wisatawan/pembelian-tiket/'.$order->code.'/pembayaran';
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('receipt.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWZkAAAAASUVORK5CYII='));
    }

    private function qris(TicketOrder $order): TicketPayment
    {
        $this->postJson($this->endpoint($order), ['method' => 'qris'])->assertOk();
        return $order->payment()->firstOrFail();
    }

    public function test_select_uses_stored_total_and_identical_retry_does_not_duplicate(): void
    {
        $order = $this->order();
        $url = $this->endpoint($order);
        $this->get($url)->assertOk()->assertSee('Pilih metode pembayaran');
        $first = $this->postJson($url, ['method' => 'qris', 'amount' => '1', 'status' => 'paid'])
            ->assertOk()->assertJsonPath('payment.amount', '30000.00')->assertJsonPath('payment.status', 'pending');
        $this->postJson($url, ['method' => 'qris'])->assertOk()->assertJsonPath('payment.code', $first->json('payment.code'));
        $this->postJson($url, ['method' => 'cash'])->assertConflict();
        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseCount('ticket_payments', 1);
        $this->assertDatabaseCount('ticket_payment_events', 1);
        $this->get($url)->assertOk()
            ->assertSee('MIDTRANS SANDBOX')
            ->assertSee('Buat QRIS Midtrans Sandbox')
            ->assertDontSee('qris-code.png')
            ->assertDontSee('MUHAMMAD NAUFAL ZAKI, SOFTWARE')
            ->assertSee('30.000');
        $this->get('/wisatawan/pembelian-tiket/'.$order->code)->assertSee('Lanjut ke Pembayaran');
    }

    public function test_qris_manual_upload_ditolak_dan_bukti_lama_tetap_privat(): void
    {
        Storage::fake('local');
        $order = $this->order();
        $payment = $this->qris($order);

        // QRIS Midtrans tidak lagi menerima unggahan bukti manual.
        $this->postJson($this->endpoint($order).'/bukti', ['proof' => $this->image()])
            ->assertConflict();
        $this->assertDatabaseCount('ticket_payment_submissions', 0);
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $order->fresh()->status);

        // Bukti historis dari alur lama tetap harus terlindungi.
        $content = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWZkAAAAASUVORK5CYII=');
        $path = 'ticket-payment-proofs/legacy.png';
        Storage::disk('local')->put($path, $content);
        $proof = $payment->submissions()->create([
            'file_path' => $path,
            'file_hash' => hash('sha256', $content),
        ]);

        $this->getJson($this->endpoint($order))
            ->assertJsonMissingPath('payment.submissions.0.file_path')
            ->assertJsonMissingPath('payment.submissions.0.file_hash');
        $this->get('/pembayaran/bukti/'.$proof->id)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs($this->account())
            ->get('/pembayaran/bukti/'.$proof->id)->assertNotFound();
        $this->actingAs($this->account('admin'))
            ->get('/pembayaran/bukti/'.$proof->id)->assertNotFound();
        $this->actingAs($this->account('petugas'))
            ->get('/pembayaran/bukti/'.$proof->id)->assertOk();
    }

    public function test_semua_percobaan_upload_bukti_qris_manual_ditolak(): void
    {
        Storage::fake('local');
        $order = $this->order();
        $payment = $this->qris($order);

        $this->postJson($this->endpoint($order).'/bukti', ['proof' => $this->image()])
            ->assertConflict();
        $this->postJson($this->endpoint($order).'/bukti', ['proof' => $this->image()])
            ->assertConflict();
        $different = UploadedFile::fake()->createWithContent(
            'different.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWZkAAAAASUVORK5CYII=').'extra'
        );
        $this->postJson($this->endpoint($order).'/bukti', ['proof' => $different])
            ->assertConflict();

        $this->assertDatabaseCount('ticket_payment_submissions', 0);
        $this->assertCount(0, Storage::disk('local')->allFiles());
        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_petugas_tidak_dapat_menolak_qris_midtrans_secara_manual(): void
    {
        $order = $this->order();
        $payment = $this->qris($order);

        $this->actingAs($this->account('petugas'))
            ->postJson('/petugas/pembayaran/'.$payment->id.'/tolak', [
                'receipt_checked' => 1,
                'reason' => 'Keputusan manual tidak diperbolehkan',
            ])->assertConflict();

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertDatabaseCount('ticket_payment_submissions', 0);
    }

    public function test_petugas_tidak_dapat_mengonfirmasi_qris_midtrans_secara_manual(): void
    {
        $order = $this->order();
        $payment = $this->qris($order);
        $url = '/petugas/pembayaran/'.$payment->id.'/konfirmasi';

        $this->actingAs($this->account('petugas'))
            ->postJson($url, [
                'receipt_checked' => 1,
                'merchant_reference' => 'GOPAY-REF-001',
            ])->assertConflict();

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertNull($payment->fresh()->merchant_reference);
        $this->assertSame(0, TicketPaymentEvent::where('action', 'payment_confirmed')->count());
    }

    public function test_referensi_manual_tidak_bisa_membayar_pesanan_qris_manapun(): void
    {
        $one = $this->order();
        $first = $this->qris($one);
        $two = $this->order();
        $second = $this->qris($two);

        $this->actingAs($this->account('petugas'));
        foreach ([$first, $second] as $payment) {
            $this->postJson('/petugas/pembayaran/'.$payment->id.'/konfirmasi', [
                'receipt_checked' => 1,
                'merchant_reference' => 'DUPLICATE-REF',
            ])->assertConflict();
        }

        $this->assertSame('pending_payment', $one->fresh()->status);
        $this->assertSame('pending_payment', $two->fresh()->status);
        $this->assertNull($first->fresh()->merchant_reference);
        $this->assertNull($second->fresh()->merchant_reference);
    }

    public function test_cash_only_staff_can_confirm_actual_receipt(): void
    {
        $order = $this->order();
        $this->postJson($this->endpoint($order), ['method' => 'cash'])->assertOk();
        $payment = $order->payment()->first();
        $this->get($this->endpoint($order))
            ->assertOk()
            ->assertSee('KODE PEMBAYARAN CASH')
            ->assertSee('Menunggu pembayaran');
        $this->postJson($this->endpoint($order).'/bukti', ['proof' => $this->image()])->assertConflict();
        $url = '/petugas/pembayaran/'.$payment->id.'/konfirmasi';
        $this->postJson($url, ['receipt_checked' => 1])->assertForbidden();
        $staff = $this->account('petugas');
        $this->actingAs($staff)->postJson($url, [])->assertUnprocessable()->assertJsonValidationErrors('receipt_checked');
        $this->postJson($url, ['receipt_checked' => 1])->assertOk()->assertJsonPath('order_status', 'paid');
        $this->postJson($url, ['receipt_checked' => 1])->assertOk();
        $this->postJson('/petugas/pembayaran/'.$payment->id.'/tolak', ['receipt_checked' => 1, 'reason' => 'ubah'])->assertConflict();
        $this->assertDatabaseHas('ticket_payments', ['id' => $payment->id, 'confirmed_by' => $staff->id]);
        $this->assertSame(1, TicketPaymentEvent::where('action', 'payment_confirmed')->count());
    }

    public function test_payment_endpoints_require_authentication_and_enforce_owner_and_role(): void
    {
        $order = $this->order();
        $this->postJson($this->endpoint($order), ['method' => 'cash'])->assertOk();
        $payment = $order->payment()->first();
        $this->actingAs($this->account())->getJson($this->endpoint($order))->assertNotFound();
        $this->postJson($this->endpoint($order), ['method' => 'cash'])->assertNotFound();
        $this->getJson('/petugas/pembayaran')->assertForbidden();
        $this->actingAs($this->account('admin'))->postJson('/petugas/pembayaran/'.$payment->id.'/konfirmasi', ['receipt_checked' => 1])->assertForbidden();
        $this->getJson('/petugas/pembayaran/'.$payment->id)->assertForbidden();
        $this->actingAs($this->account('petugas'))->getJson($this->endpoint($order))->assertForbidden();
        auth()->forgetGuards();
        $this->getJson($this->endpoint($order))->assertUnauthorized();
        $this->postJson($this->endpoint($order), ['method' => 'cash'])->assertUnauthorized();
        $this->getJson('/petugas/pembayaran')->assertUnauthorized();
        $this->getJson('/pembayaran/bukti/1')->assertUnauthorized();
        $this->get($this->endpoint($order))->assertRedirect('/login');
    }

    public function test_invalid_method_and_non_pending_order_cannot_create_payment(): void
    {
        $order = $this->order();
        $this->postJson($this->endpoint($order), ['method' => 'transfer'])->assertUnprocessable()->assertJsonValidationErrors('method');
        $order->update(['status' => 'cancelled']);
        $this->postJson($this->endpoint($order), ['method' => 'cash'])->assertConflict();
        $this->assertDatabaseCount('ticket_payments', 0);
    }

    #[DataProvider('invalidProofs')]
    public function test_invalid_proofs_are_not_saved(string $type): void
    {
        Storage::fake('local');
        $order = $this->order();
        $this->postJson($this->endpoint($order), ['method' => 'qris'])->assertOk();
        $file = match ($type) {
            'missing' => null,
            'oversized' => UploadedFile::fake()->createWithContent('large.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWZkAAAAASUVORK5CYII=').str_repeat('x', 5121 * 1024)),
            'spoofed' => UploadedFile::fake()->createWithContent('bad.png', '<?php echo 1;'),
            'extension' => UploadedFile::fake()->createWithContent('bad.php', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWZkAAAAASUVORK5CYII=')),
        };
        $this->postJson($this->endpoint($order).'/bukti', ['proof' => $file])->assertUnprocessable()->assertJsonValidationErrors('proof');
        $this->assertCount(0, Storage::disk('local')->allFiles());
        $this->assertDatabaseCount('ticket_payment_submissions', 0);
    }

    public static function invalidProofs(): array
    {
        return ['missing' => ['missing'], 'over 5MB' => ['oversized'], 'fake image' => ['spoofed'], 'PHP extension' => ['extension']];
    }

    public function test_qris_manual_upload_ditolak_sebelum_file_dan_event_tercipta(): void
    {
        Storage::fake('local');
        $order = $this->order();
        $this->qris($order);

        // Listener ini tidak boleh terpanggil karena upload ditolak sebelum menyimpan.
        TicketPaymentEvent::creating(function ($event) {
            if ($event->action === 'proof_submitted') {
                throw new \RuntimeException('Upload QRIS manual seharusnya diblokir');
            }
        });
        try {
            $this->postJson($this->endpoint($order).'/bukti', [
                'proof' => $this->image(),
            ])->assertConflict();
            $this->assertDatabaseCount('ticket_payment_submissions', 0);
            $this->assertSame('pending', $order->payment()->first()->status);
            $this->assertCount(0, Storage::disk('local')->allFiles());
        } finally {
            TicketPaymentEvent::flushEventListeners();
        }
    }

    public function test_qris_manual_confirmation_ditolak_tanpa_memicu_event_tiket(): void
    {
        $order = $this->order();
        $payment = $this->qris($order);
        Event::fake([TicketPaymentConfirmed::class]);

        $this->actingAs($this->account('petugas'))
            ->postJson('/petugas/pembayaran/'.$payment->id.'/konfirmasi', [
                'receipt_checked' => 1,
                'merchant_reference' => 'ROLLBACK',
            ])->assertConflict();

        $this->assertSame('pending_payment', $order->fresh()->status);
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertDatabaseCount('ticket_payment_submissions', 0);
        Event::assertNotDispatched(TicketPaymentConfirmed::class);
    }

    public function test_staff_list_is_newest_first_paginated_and_has_dashboard_entrypoint(): void
    {
        for ($i = 0; $i < 21; $i++) {
            $order = $this->order();
            $this->postJson($this->endpoint($order), ['method' => 'cash'])->assertOk();
        }
        $this->actingAs($this->account('petugas'));
        $this->getJson('/petugas/pembayaran')->assertOk()->assertJsonPath('payments.total', 21)
            ->assertJsonPath('payments.per_page', 20)->assertJsonCount(20, 'payments.data')
            ->assertJsonPath('payments.data.0.order.code', $order->code);
        $this->getJson('/petugas/pembayaran?page=2')->assertJsonCount(1, 'payments.data');
        $this->get('/petugas/pembayaran')->assertOk()->assertSee('Verifikasi Pembayaran');
        $this->get('/petugas/dashboard')->assertOk()->assertSee(route('ticket-payment.staff.index'), false);
    }

    public function test_confirmation_event_is_dispatched_only_after_outer_commit_and_not_after_rollback(): void
    {
        $order = $this->order();
        $ownerId = $order->user_id;
        $staff = $this->account('petugas');
        $payment = app(TicketPaymentService::class)->select($order->user, $order->code, 'cash');
        $received = [];
        Event::listen(TicketPaymentConfirmed::class, function ($event) use (&$received): void {
            $received[] = $event;
        });
        try {
            $this->testConnection->beginTransaction();
            app(TicketPaymentService::class)->review($staff, $payment->id, [], true);
            $this->assertCount(0, $received);
            $this->testConnection->rollBack();
            $this->assertSame('pending_payment', $order->fresh()->status);
            $this->assertCount(0, $received);
            app(TicketPaymentService::class)->review($staff, $payment->id, [], true);
            $this->assertCount(0, $received);
            $this->testConnection->commit();
            $this->assertCount(1, $received);
            $this->assertSame($order->code, $received[0]->orderCode);
            $this->assertSame('30000.00', $received[0]->amount);
            $this->assertSame('cash', $received[0]->method);
            $this->assertNotEmpty($received[0]->paidAt);
        } finally {
            while ($this->testConnection->transactionLevel() > 0) {
                $this->testConnection->rollBack();
            }
            Event::forget(TicketPaymentConfirmed::class);
            $this->removeCommittedFixtures($order->id, [$ownerId, $staff->id]);
        }
    }

    public function test_two_simultaneous_confirmations_produce_one_confirmation_and_one_paid_order(): void
    {
        $order = $this->order();
        $ownerId = $order->user_id;
        $staff = $this->account('petugas');
        $payment = app(TicketPaymentService::class)->select($order->user, $order->code, 'cash');
        $this->testConnection->commit();
        $workers = [];
        try {
            $this->testConnection->beginTransaction();
            DB::table('ticket_orders')->where('id', $order->id)->lockForUpdate()->first();
            $script = <<<'WORKER'
require 'vendor/autoload.php';
$test = new Tests\Feature\TicketPaymentTest('test_cash_only_staff_can_confirm_actual_receipt');
$test->createApplication();
Illuminate\Support\Facades\DB::statement("SET application_name = 'krm-payment-race-test'");
$payment = app(App\Services\TicketPaymentService::class)->review(App\Models\User::findOrFail((int) $argv[1]), (int) $argv[2], [], true);
echo $payment->status;
WORKER;
            for ($i = 0; $i < 2; $i++) {
                $worker = new Process([PHP_BINARY, '-r', $script, (string) $staff->id, (string) $payment->id], base_path());
                $worker->setTimeout(15);
                $worker->start();
                $workers[] = $worker;
            }
            $waiting = 0;
            $deadline = microtime(true) + 8;
            do {
                DB::select('SELECT pg_stat_clear_snapshot()');
                $waiting = (int) DB::selectOne("SELECT count(*) AS count FROM pg_stat_activity WHERE datname = current_database() AND application_name = 'krm-payment-race-test' AND wait_event_type = 'Lock'")->count;
                if ($waiting < 2) {
                    usleep(20000);
                }
            } while ($waiting < 2 && microtime(true) < $deadline);
            $this->testConnection->commit();
            foreach ($workers as $worker) {
                $worker->wait();
                $this->assertSame(0, $worker->getExitCode(), $worker->getErrorOutput());
                $this->assertSame('paid', $worker->getOutput());
            }
            $this->assertSame(2, $waiting, 'Both real PostgreSQL sessions must contend for the order lock.');
            $this->assertSame('paid', $order->fresh()->status);
            $this->assertSame(1, $payment->events()->where('action', 'payment_confirmed')->count());
        } finally {
            while ($this->testConnection->transactionLevel() > 0) {
                $this->testConnection->rollBack();
            }
            foreach ($workers as $worker) {
                $worker->stop();
            }
            $this->removeCommittedFixtures($order->id, [$ownerId, $staff->id]);
        }
    }

    private function removeCommittedFixtures(int $orderId, array $userIds): void
    {
        $this->assertSame('simticketingkrm_purchase_test', $this->testConnection->getDatabaseName());
        DB::transaction(function () use ($orderId, $userIds): void {
            $payments = DB::table('ticket_payments')->where('ticket_order_id', $orderId)->pluck('id');
            DB::table('ticket_payment_events')->whereIn('ticket_payment_id', $payments)->delete();
            DB::table('ticket_payment_submissions')->whereIn('ticket_payment_id', $payments)->delete();
            DB::table('ticket_payments')->whereIn('id', $payments)->delete();
            $items = DB::table('ticket_order_items')->where('ticket_order_id', $orderId)->pluck('id');
            DB::table('ticket_order_visitors')->whereIn('ticket_order_item_id', $items)->delete();
            DB::table('ticket_order_items')->whereIn('id', $items)->delete();
            DB::table('ticket_orders')->where('id', $orderId)->delete();
            DB::table('users')->whereIn('id', $userIds)->delete();
        });
    }
}