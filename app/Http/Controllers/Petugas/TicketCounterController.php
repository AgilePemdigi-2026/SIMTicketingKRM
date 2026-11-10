<?php

namespace App\Http\Controllers\Petugas;

use App\Support\TicketPurchaseDatabase;
use Illuminate\Support\Str;
use App\Contracts\TicketCatalogProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Petugas\PreviewCounterTicketRequest;
use App\Services\TicketPurchaseService;
use App\Http\Requests\Petugas\CheckoutCounterTicketRequest;
use App\Models\User;
use App\Services\TicketPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TicketCounterController extends Controller
{
    public function index(TicketCatalogProvider $catalog): View
    {
        return view('petugas.loket.index', [
            'tickets' => $catalog->activeTickets(),
            'today' => now('Asia/Jakarta')->toDateString(),
            'quote' => null,
            'checkoutToken' => (string) Str::uuid(),
        ]);
    }

    public function preview(
        PreviewCounterTicketRequest $request,
        TicketPurchaseService $service,
        TicketCatalogProvider $catalog
    ): View {
        $quote = $service->calculate($request->validated());

        return view('petugas.loket.index', [
            'tickets' => $catalog->activeTickets(),
            'today' => now('Asia/Jakarta')->toDateString(),
            'quote' => $quote,
            'checkoutToken' => $request->input('checkout_token') ?: (string) Str::uuid(),
        ]);
    }

    public function checkout(
        CheckoutCounterTicketRequest $request,
        TicketPurchaseService $purchaseService,
        TicketPaymentService $paymentService,
        TicketPurchaseDatabase $database
    ): RedirectResponse {
        $data = $request->validated();
        $booker = $data['booker'];

        // Gunakan akun Wisatawan terdaftar yang identitasnya sesuai.
        $wisatawan = User::query()
            ->where('role', 'wisatawan')
            ->where('email', $booker['email'])
            ->where('identity_number', $booker['identity_number'])
            ->where('phone', $booker['phone'])
            ->first();

        if ($wisatawan === null) {
            return redirect()
                ->route('petugas.loket.index')
                ->withErrors([
                    'booker.email' => 'Akun Wisatawan tidak ditemukan. Pastikan email, nomor identitas, dan telepon sesuai dengan akun terdaftar.',
                ])
                ->withInput();
        }

        // Simpan pesanan dan pembayaran dalam satu transaksi PostgreSQL.
        $payment = $database->connection()->transaction(
            function () use (
                $wisatawan,
                $data,
                $purchaseService,
                $paymentService
            ) {
                $order = $purchaseService->create($wisatawan, $data);

                return $paymentService->select(
                    $wisatawan,
                    $order->code,
                    $data['payment_method']
                );
            }
        );

        return redirect()
            ->route('ticket-payment.staff.show', $payment->id)
            ->with(
                'payment_updated',
                'Pesanan loket berhasil dibuat. Pembayaran belum dikonfirmasi.'
            );
    }
}
